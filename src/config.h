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

#define NETMRG_ROOT 		"/var/www/netmrg/"
#define RRDTOOL 		"/usr/bin/rrdtool - "

#define NTHREADS 		8 			// number of simultaneous threads
#define THREAD_SLEEP 		150000			// number of microseconds between thread checks

// MySQL Credentials
#define MYSQL_HOST		"localhost"
#define MYSQL_USER		"netmrgwrite"
#define MYSQL_PASS		"netmrgwrite"
#define MYSQL_DB		"netmrg"

// Define the command that distributes reports
#define DISTRIB_CMD "/var/www/netmrg/bin/distrib.sh"

#endif              
