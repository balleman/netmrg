/********************************************
* NetMRG Integrator
*
* netmrg.cpp
* NetMRG Gatherer
*
* see doc/LICENSE for copyright information
********************************************/

/*

   NetMRG Monitoring Procedure
   Copyright 2001-2006 Brady Alleman.  All Rights Reserved.

   MySQL examples from http://mysql.turbolift.com/mysql/chapter4.php3
   pthreads examples from http://www.math.arizona.edu/swig/pthreads/threads.html
   net-snmp examples from http://net-snmp.sf.net/
   Thanks to Patrick Haller (http://haller.ws) for helping to debug threading in the original gatherer.

*/

#include "common.h"
#include <cstdio>
#include <cstdlib>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <string>
#include <errno.h>
#include <list>
#include <iostream>
#include <termios.h>
#include <signal.h>
#include <time.h>

using namespace std;

int active_threads = 0;
bool netmrg_terminated = false;

// Include the NetMRG Headers
#include "types.h"
#include "utils.h"
#include "locks.h"
#include "settings.h"
#include "snmp.h"
#include "db.h"
#include "rrd.h"
#include "mappings.h"
#include "devices.h"

ScheduleType schedule = schOnce;

#ifdef OLD_MYSQL
#define MYSQL_THREAD_INIT
#define MYSQL_THREAD_END
#else
#define MYSQL_THREAD_INIT mysql_thread_init()
#define MYSQL_THREAD_END mysql_thread_end()
#endif


// child - the thread spawned to process each device
void *child(void * arg)
{
	int device_id = *(int *) arg;

	MYSQL_THREAD_INIT;

	process_device(device_id);

	netmrg_mutex_lock(lkActiveThreads);
	active_threads--;
	netmrg_cond_signal(cActiveThreads);
	netmrg_mutex_unlock(lkActiveThreads);

	MYSQL_THREAD_END;

	pthread_exit(0);

} // end child

// remove lock file
void remove_lockfile()
{
	unlink(get_setting(setPathLockFile).c_str());
}

// SIGTERM signal handler
void handle_sigterm(int signum)
{
	netmrg_terminated = true;
}

// Say we're going away due to SIGTERM
void saydie()
{
	debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Caught signal, shutting down.");
}

// set things up, and spawn the threads for data gathering
void run_netmrg()
{
	init_logging();	
	debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, "NetMRG starting.");
	
	pid_t mypid = getpid();
	FILE *lockfile;
	
	// check for existing lockfile
	if (file_exists(get_setting(setPathLockFile)))
	{
		lockfile = fopen(get_setting(setPathLockFile).c_str(), "r");
		pid_t otherpid;
		fscanf(lockfile, "%d", &otherpid);
		fclose(lockfile);
		
		// if we could have sent the signal, or if the problem wasn't finding the PID, die.
		if ( (kill(otherpid, 0) != -1) || (errno != ESRCH) )
		{
			debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, 
				"Critical:  Another instance of NetMRG appears to be running. (PID " + inttostr(otherpid) + ")");
			exit(254);
		}
		else
		{
			debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, "Removing stale lock file.");
			remove_lockfile();
		}
	}

	// create lockfile
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Creating Lockfile.");
	if ((lockfile = fopen(get_setting(setPathLockFile).c_str(),"w+")) != NULL)
	{
		fprintf(lockfile, "%d", mypid);
		fclose(lockfile);
		atexit(remove_lockfile);
	}
	else
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, string("Critical:  Lockfile creation failure.  (") + strerror(errno) + ")");
		exit(2);
	}

	// SNMP library initialization
	snmp_init();
	atexit(snmp_cleanup);

	// RRDTOOL command pipe setup
	rrd_init();
	atexit(rrd_cleanup);
	
	// Setup SIGTERM/SIGINT catching
	struct sigaction term_sigaction;
	term_sigaction.sa_handler = &handle_sigterm;
	if (sigaction(SIGTERM, &term_sigaction, NULL))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Failed to add signal handler.");
		exit(10);
	}
	if (sigaction(SIGINT, &term_sigaction, NULL))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Failed to add signal handler.");
		exit(10);
	}
	

	do
	{
		time_t start_time = time(NULL);
		
		// open mysql connection for initial queries
		MYSQL			mysql;
		MYSQL_RES		*mysql_res;
		MYSQL_ROW		mysql_row;
		if (!db_connect(&mysql))
		{
			debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical: 