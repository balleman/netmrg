/********************************************
* NetMRG Integrator
*
* config.h
* Gatherer Configuration Header File
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_CONFIG
#define NETMRG_CONFIG

#define _THREAD_SAFE
#define _PTHREADS
#define _POSIX_THREADS
#define _POSIX_THREAD_SAFE_FUNCTIONS
#define _P __P

#define NETMRG_VERSION	"0.80"
#define NETMRG_ROOT 	"/var/www/netmrg/"
#define RRDTOOL 		"/usr/bin/rrdtool - "

#define NTHREADS 	15 			// number of simultaneous threads
#define THREAD_SLEEP 	10000		// number of microseconds between thread checks

// MySQL Credentials
#define MYSQL_HOST		"localhost"
#define MYSQL_USER		"netmrgwrite"
#define MYSQL_PASS		"netmrgwrite"
#define MYSQL_DB		"netmrg"

// Define the command that distributes reports
#define DISTRIB_CMD "/var/www/netmrg/bin/distrib.sh"

#endif              
