/********************************************
* NetMRG Integrator
*
* netmrg.cpp
* NetMRG Gatherer
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
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
#include <cstring>
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
	int lockfile_res;
	
	// check for existing lockfile
	if (file_exists(get_setting(setPathLockFile)))
	{
		lockfile = fopen(get_setting(setPathLockFile).c_str(), "r");
		pid_t otherpid;
		lockfile_res = fscanf(lockfile, "%d", &otherpid);
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
			debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical: Master database connection failed.");
			exit(3);
		}
	
		// verify the database version matches the gatherer version
		mysql_res = db_query(&mysql, NULL, "SELECT version FROM versioninfo WHERE module = 'Main'");
		mysql_row = mysql_fetch_row(mysql_res);
		if (string(mysql_row[0]) != string(NETMRG_VERSION))
		{
			debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, string("Critical: Database version (") + mysql_row[0] + ") and gatherer version (" + NETMRG_VERSION + ") do not match.");
			debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Log into the web interface to perform the database upgrade.");
			exit(4);
		}
		mysql_free_result(mysql_res);
		
		// request list of devices to process
		mysql_res = db_query(&mysql, NULL, "SELECT id FROM devices WHERE disabled=0 ORDER BY id");
	
		long int num_rows	=	mysql_num_rows(mysql_res);
		pthread_t* threads	=   new pthread_t[num_rows];
		int* ids			=	new int[num_rows];
	
		// reading settings isn't necessarily efficient.  storing them locally.
		int			THREAD_COUNT = get_setting_int(setThreadCount);
	
		int dev_counter = 0;
	
		// deploy more threads as needed
		int last_active_threads = 0;
	
		netmrg_mutex_lock(lkActiveThreads);
	
		while (dev_counter < num_rows)
		{
			debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[ACTIVE] Last: " +
				inttostr(last_active_threads) + ", Now: " +
				inttostr(active_threads));
			last_active_threads = active_threads;
	
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
	
			netmrg_cond_wait(cActiveThreads, lkActiveThreads);
			if (netmrg_terminated)
			{
				saydie();
				break;
			}
	
		}
	
		// wait until all threads exit
		while (active_threads != 0)
		{
			netmrg_cond_wait(cActiveThreads, lkActiveThreads);
	
			debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[PASSIVE] Last: " +
				inttostr(last_active_threads) + ", Now: " +
				inttostr(active_threads));
				last_active_threads = active_threads;
		}
	
		netmrg_mutex_unlock(lkActiveThreads);
	
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
		FILE *runtime;
		if ((runtime = fopen(get_setting(setPathRuntimeFile).c_str(),"w+")))
		{
			fprintf(runtime, "%ld", run_time);
			fclose(runtime);
		}
		else
		{
			debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Failed to open runtime file for writing.");
		}
		
		if ((schedule == schWait) && (!netmrg_terminated))
		{
			if ( time(NULL) > (start_time + get_setting_int(setPollInterval)))
			{
				debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "We're running behind!");
			}
			else
			{
				timespec tosleep, unslept;
				tosleep.tv_sec = start_time + get_setting_int(setPollInterval) - time(NULL);
				tosleep.tv_nsec = 0;
				while (nanosleep(&tosleep, &unslept))
				{
					if ((errno == EINTR) && (netmrg_terminated))
					{
						saydie();
						break;
					}
					tosleep.tv_sec = unslept.tv_sec;
				}
				
			}
		}	
	}
	while ((schedule != schOnce) && (!netmrg_terminated));
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
	printf("-X          Become a daemon.\n");
	printf("-M <mode>   Scheduling mode, <mode> is:\n");
	printf("            once = return after one gather cycle (default).\n");
	printf("            wait = gather once every interval, waiting between intervals.\n");

	printf("\nMode of Operation:\n");
	printf("-i <devid>  Recache the interfaces of device <devid>\n");
	printf("-d <devid>  Recache the disks of device <devid>\n");
	printf("-P <devid>  Recache the properties of device <devid>\n");
	printf("-K <file>   Parse config file <file>, or default if omitted (for syntax checking)\n");
	printf("If no mode is specified, the default is to gather data for all enabled devices.\n");

	printf("\nLogging:\n");
	printf("-S          Syslog; output logs to syslog instead of stdout.\n");
	printf("-b          Bland; disable color output.\n");
	printf("-a          All; display all debug messages.\n");
	printf("-m          Most; display more than the default.\n");
	printf("-q          Quiet; display no debug messages.\n");
	printf("-c <cm>     Use debug component mask <cm>.\n");
	printf("-l <lm>     Use debug level mask <lm>.\n");
	printf("-s          Safe; omit potentially sensitive information.\n");

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
		string("snmp_timeout, snmp_retries, dev_type, snmp3_user, snmp3_seclev, snmp3_aprot, snmp3_apass, snmp3_pprot, ") +
		"snmp3_ppass FROM devices WHERE id=" + inttostr(device_id));
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
	info.snmp3_user				= mysql_row[7];
	info.snmp3_seclev			= strtoint(mysql_row[8]);
	info.snmp3_aprot			= strtoint(mysql_row[9]);
	info.snmp3_apass			= mysql_row[10];
	info.snmp3_pprot			= strtoint(mysql_row[11]);
	info.snmp3_ppass			= mysql_row[12];
	info.snmp_port				= strtoint(mysql_row[3]);
	info.snmp_timeout			= strtoint(mysql_row[4]);
	info.snmp_retries			= strtoint(mysql_row[5]);
	info.device_type			= strtoint(mysql_row[6]);
	info.parameters.push_front(ValuePair("ip", info.ip));
	info.parameters.push_front(ValuePair("snmp_read_community", info.snmp_read_community));

	mysql_free_result(mysql_res);

	snmp_init();
	snmp_session_init(info);
	switch (type)
	{
		case 1: do_snmp_interface_recache(&info, &mysql);	break;
		case 2: do_snmp_disk_recache(&info, &mysql); 		break;
		case 3: do_properties_recache(info, &mysql);		break;
	}
	snmp_session_cleanup(info);
	snmp_cleanup();
	mysql_close(&mysql);
}

// daemonize - make us lurk around the system
void daemonize()
{
	pid_t pid;
	int chdir_res;

	pid = fork();
	if (pid < 0)
	{
		// failed to fork, keep going with this process
		fprintf(stderr, "Failed to fork; unable to daemonize.\n");
		fprintf(stderr, "Continuing in the foreground.\n");
		return;
	}
	else if (pid != 0)
	{
		// fork successful, and we're the parent, time to die.
		exit(0);
	}
	else
	{
		// we're the child, keep going
		setsid();
		chdir_res = chdir("/");
		umask(0);
		return;
	}
}

// main - the body of the program
int main(int argc, char **argv)
{
	int option_char;
	load_settings_default();
	load_settings_file(DEF_CONFIG_FILE);
	string temppass;
	
	if (vt100_compatible())
	{
		set_log_method(LOG_METHOD_VT100);
	}

	while ((option_char = getopt(argc, argv, "hvXSqasmbM:i:d:P:c:l:H:D:u:p::t:C:K::")) != EOF)
		switch (option_char)
		{
			case 'h': 	show_usage();
						exit(0);
						break;
			case 'v': 	show_version();
						exit(0);
						break;
			case 'M':	if (strcmp(optarg, "once") == 0)
							schedule = schOnce;
						else if (strcmp(optarg, "wait") == 0)
							schedule = schWait;
						else
							fprintf(stderr, "I don't know what schedule '%s' is.  Using default.\n", optarg);
						break;
			case 'i': 	external_snmp_recache(strtoint(optarg), 1);
						exit(0);
						break;
			case 'X':	daemonize();
						break;
			case 'S':	set_log_method(LOG_METHOD_SYSLOG);
						break;
			case 'b':	set_log_method(LOG_METHOD_STDOUT);
						break;
			case 'd': 	external_snmp_recache(strtoint(optarg), 2);
						exit(0);
						break;
			case 'P':	external_snmp_recache(strtoint(optarg), 3);
						exit(0);
						break;
			case 'c':	set_debug_components(strtoint(optarg));
						break;
			case 'l':	set_debug_level(strtoint(optarg));
						break;
			case 'q': 	set_debug_level(0);
						break;
			case 'a':	set_debug_level(LEVEL_ALL);
						set_debug_components(DEBUG_ALL);
						break;
			case 'm':	set_debug_level(LEVEL_MOST);
						set_debug_components(DEBUG_MOST);
						break;
			case 's':	set_debug_safety(true);
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
							/* Make sure stdin is a terminal */
							if (!isatty(STDIN_FILENO))
							{
								fprintf(stderr, "Not bound to a terminal. Using empty string for password.\n");
								temppass = "";
							}
							else
							{
								// Save terminal settings
								struct termios saved_tattr;
								tcgetattr (STDIN_FILENO, &saved_tattr);

								// don't echo input
								struct termios tattr;
								tcgetattr (STDIN_FILENO, &tattr);
								tattr.c_lflag &= (ICANON|ECHONL|ISIG);
								tattr.c_lflag &= -ECHO;
								tcsetattr (STDIN_FILENO, TCSANOW, &tattr);

								// if password not specified, prompt for it
								cout << "Password: ";
								cin >> temppass;

								// Restore terminal settings
								tcsetattr (STDIN_FILENO, TCSANOW, &saved_tattr);
							}
						}
						set_setting(setDBPass, temppass);
						break;
			case 't':	set_setting(setThreadCount, optarg);
						break;
			case 'C':	load_settings_file(optarg);
						break;
			case 'K':	set_debug_level(LEVEL_DEBUG);
						set_debug_components(DEBUG_GLOBAL);
						if (optarg != NULL)
						{
							load_settings_file(optarg);
						}
						print_settings();
						exit(0);
						break;

		}
	run_netmrg();
}
