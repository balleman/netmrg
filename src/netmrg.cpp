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
   Copyright 2001-2003 Brady Alleman.  All Rights Reserved.

   MySQL examples from http://mysql.turbolift.com/mysql/chapter4.php3
   pthreads examples from http://www.math.arizona.edu/swig/pthreads/threads.html
   net-snmp examples from http://net-snmp.sf.net/
   Thanks to Patrick Haller (http://haller.ws) for helping to debug threading.

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

using namespace std;

int active_threads = 0;

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



// child - the thread spawned to process each device
void *child(void * arg)
{
	int device_id = *(int *) arg;

#ifndef OLD_MYSQL
	mysql_thread_init();
#endif

	process_device(device_id);

	mutex_lock(lkActiveThreads);
	active_threads--;
	mutex_unlock(lkActiveThreads);
	debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "Thread Ended.");

#ifndef OLD_MYSQL
	mysql_thread_end();
#endif

	pthread_exit(0);

} // end child

// remove lock file
void remove_lockfile()
{
	unlink(get_setting(setPathLockFile).c_str());
}

// set things up, and spawn the threads for data gathering
void run_netmrg()
{

	MYSQL			mysql;
	MYSQL_RES		*mysql_res;
	MYSQL_ROW		mysql_row;
	time_t			start_time;
	FILE			*lockfile;
	FILE			*runtime;
	long int		num_rows	= 0;
	pthread_t*		threads		= NULL;
	int*			ids			= NULL;
	string			temp_string;

	start_time = time( NULL );
	
	setlinebuf(stdout);

	debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, "NetMRG starting.");
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Start time is " + inttostr(start_time));

	if (file_exists(get_setting(setPathLockFile)))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical:  Lockfile exists.  Is another NetMRG running?");
		exit(254);
	}

	// create lockfile
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Creating Lockfile.");
	if ((lockfile = fopen(get_setting(setPathLockFile).c_str(),"w+")) != NULL)
	{
		fprintf(lockfile, "%ld", (long int) start_time);
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

	// open mysql connection for initial queries
	if (!db_connect(&mysql))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical: Master database connection failed.");
		exit(3);
	}

	// request list of devices to process
	mysql_res = db_query(&mysql, NULL, "SELECT id FROM devices WHERE disabled=0 ORDER BY id");

	num_rows	= mysql_num_rows(mysql_res);
	threads		= new pthread_t[num_rows];
	ids			= new int[num_rows];

	// reading settings isn't necessarily efficient.  storing them locally.
	int			THREAD_COUNT = get_setting_int(setThreadCount);
	long int	THREAD_SLEEP = get_setting_int(setThreadSleep);
	
	int dev_counter = 0;

	// deploy more threads as needed
	int last_active_threads = 0;
	while (dev_counter < num_rows)
	{
		if (mutex_trylock(lkActiveThreads) != EBUSY)
		{
			if (last_active_threads != active_threads)
			{
				debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[ACTIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
				last_active_threads = active_threads;
			}
			while ((active_threads < THREAD_COUNT) && (dev_counter < num_rows))
			{
				mysql_row = mysql_fetch_row(mysql_res);
				int dev_id = strtoint(string(mysql_row[0]));
				ids[dev_counter] = dev_id;
				pthread_create(&threads[dev_counter], NULL, child, &ids[dev_counter]);
				pthread_detach(threads[dev_counter]);
				dev_counter++;
				active_threads++;
			}
			
			mutex_unlock(lkActiveThreads);
		}
		else
		{
			debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "[ACTIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}

	// wait until all threads exit
	int canexit = 0;
	while (canexit == 0)
	{
		if (mutex_trylock(lkActiveThreads) != EBUSY)
		{
			if (last_active_threads != active_threads)
			{
				debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[PASSIVE] Last: " +
				inttostr(last_active_threads) + ", Now: " +
				inttostr(active_threads));
				last_active_threads = active_threads;
			}
			if (active_threads == 0) canexit = 1;
			mutex_unlock(lkActiveThreads);
		}
		else
		{
			debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "[PASSIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}

	// free active devices results
	mysql_free_result(mysql_res);

	delete [] threads;
	delete [] ids;

	// clean up mysql
	mysql_close(&mysql);
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Closed MySQL connection.");

	// determine runtime and store it
	long int run_time = time( NULL ) - start_time;
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Runtime: " + inttostr(run_time));
	if (runtime = fopen(get_setting(setPathRuntimeFile).c_str(),"w+"))
	{
		fprintf(runtime, "%ld", run_time);
		fclose(runtime);
	}
	else
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Failed to open runtime file for writing.");
	}


}

void show_version()
{
	printf("\nNetMRG Data Gatherer\n");
	printf("Version %s\n\n", NETMRG_VERSION);
}

void show_usage()
{
	show_version();
	
	printf("General:\n");
	printf("-v          Display Version\n");
	printf("-h          Show usage (you are here)\n");
	printf("-C <file>   Use alternate configuration file <file>\n");
	printf("-t <num>    Limits number of simultaneous threads to <num>\n");
	
	printf("\nMode of Operation:\n");
	printf("-i <devid>  Recache the interfaces of device <devid>\n");
	printf("-d <devid>  Recache the disks of device <devid>\n");
	printf("-K <file>   Parse config file <file> (for syntax checking)\n");
	printf("If no mode is specified, the default is to gather data for all enabled devices.\n");
	
	printf("\nLogging:\n");
	printf("-q          Quiet; display no debug messages.\n");
	printf("-c <cm>     Use debug component mask <cm>\n");
	printf("-l <lm>     Use debug level mask <lm>\n");

	printf("\nDatabase Settings:\n");
	printf("-H <host>   Use database server on <host>\n");
	printf("-D <db>     Use database named <db>\n");
	printf("-u <user>   Use database user name <user>\n");
	printf("-p <pass>   Use database password <pass>, will prompt for password if <pass> is omitted\n");
		
	printf("\n");
}

void external_snmp_recache(int device_id, int type)
{
	MYSQL 		mysql;
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	DeviceInfo	info;

	if (!db_connect(&mysql))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical: Master database connection failed.");
		exit(3);
	}
	
	info.device_id = device_id;

	mysql_res = db_query(&mysql, &info, string("SELECT ip, snmp_read_community, snmp_version, snmp_port, ") +
		"snmp_timeout, snmp_retries FROM devices WHERE id=" + inttostr(device_id));
	mysql_row = mysql_fetch_row(mysql_res);

	if (mysql_row == NULL)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, &info, "Device does not exist.");
		exit(1);
	}
	
	info.snmp_version = strtoint(mysql_row[2]);
	
	if (info.snmp_version == 0)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, &info, "Can't recache a device without SNMP.");
		exit(1);
	}

	info.ip 					= mysql_row[0];
	info.snmp_read_community	= mysql_row[1];
	info.snmp_port				= strtoint(mysql_row[3]);
	info.snmp_timeout			= strtoint(mysql_row[4]);
	info.snmp_retries			= strtoint(mysql_row[5]);

	mysql_free_result(mysql_res);

	snmp_init();
	snmp_session_init(info);
	switch (type)
	{
		case 1: do_snmp_interface_recache(&info, &mysql);	break;
		case 2: do_snmp_disk_recache(&info, &mysql); 		break;
	}
	snmp_session_cleanup(info);
	snmp_cleanup();
	mysql_close(&mysql);
}

// main - the body of the program
int main(int argc, char **argv)
{
	int option_char;
	load_settings_default();
	load_settings_file(DEF_CONFIG_FILE);
	string temppass;

	while ((option_char = getopt(argc, argv, "hvqi:d:c:l:H:D:u:p::t:C:K:")) != EOF)
		switch (option_char)
		{
			case 'h': 	show_usage();
						exit(0); 
						break;
			case 'v': 	show_version();
						exit(0);
						break;
			case 'i': 	external_snmp_recache(strtoint(optarg), 1);
						exit(0);
						break;
			case 'd': 	external_snmp_recache(strtoint(optarg), 2);
						exit(0);
						break;
			case 'c':	set_debug_components(strtoint(optarg));
						break;
			case 'l':	set_debug_level(strtoint(optarg));
						break;
			case 'q': 	set_debug_level(0);
						break;			
			case 'H':	set_setting(setDBHost, optarg);
						break;
			case 'D':	set_setting(setDBDB, optarg);
						break;
			case 'u':	set_setting(setDBUser, optarg);
						break;
			case 'p':	if (optarg != NULL)
						{
							// if password specified, use it
							temppass = string(optarg);
							// obscure password from process listing
							while (*optarg) *optarg++= 'x';
						}
						else
						{
							// if password not specified, prompt for it
							// this needs replaced with something much better - 
							// seems that there is no good and portable mechanism for 
							// reading hidden passwords...
							cout << "Password: ";
							cin >> temppass;
						}
						set_setting(setDBPass, temppass);
						break;
			case 't':	set_setting(setThreadCount, optarg);
						break;
			case 'C':	load_settings_file(optarg);
						break;
			case 'K':	load_settings_file(optarg);
						print_settings();
						exit(0);
						break;
			
		}
	run_netmrg();
}
