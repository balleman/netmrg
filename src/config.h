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

// The remaining options are the default values 
// used if not overridden on the command line or config file.

#define NETMRG_ROOT 		"/var/www/netmrg/"
#define RRDTOOL 			"/usr/bin/rrdtool - "

#define DEF_THREAD_COUNT	25						// number of simultaneous threads
#define DEF_THREAD_SLEEP	10000					// number of microseconds between thread checks

// Database Credentials
#define DEF_DB_HOST			"localhost"
#define DEF_DB_USER			"netmrgwrite"
#define DEF_DB_PASS			"netmrgwrite"
#define DEF_DB_DB			"netmrg"

// Define the command that distributes reports
#define DISTRIB_CMD "/var/www/netmrg/bin/distrib.sh"

#endif              
