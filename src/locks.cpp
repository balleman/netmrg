/********************************************
* NetMRG Integrator
*
* locks.cpp
* NetMRG Gatherer Locks
*
* see doc/LICENSE for copyright information
********************************************/

#include "locks.h"
#include "utils.h"

// Create mutex locks
static pthread_mutex_t active_threads_lock	= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t mysql_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t snmp_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t rrdtool_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t settings_lock		= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t pipe_lock			= PTHREAD_MUTEX_INITIALIZER;

pthread_mutex_t* get_lock(Lock myLock)
{
	switch (myLock)
	{
		case lkActiveThreads:	return &active_threads_lock;
		case lkMySQL:			return &mysql_lock;
		case lkSNMP:			return &snmp_lock;
		case lkRRD:				return &rrdtool_lock;
		case lkSettings:		return &settings_lock;
		case lkPipe:			return &pipe_lock;
	}
	return NULL;
}

string get_lock_name(Lock myLock)
{
	switch (myLock)
	{
		case lkActiveThreads:	return "ActiveThreads";
		case lkMySQL:			return "MySQL";
		case lkSNMP:			return "SNMP";
		case lkRRD:				return "RRD";
		case lkSettings:		return "Settings";
		case lkPipe:			return "Pipe";
	}
	return "";
}

void netmrg_mutex_lock(Lock myLock)
{
	debuglogger(DEBUG_THREAD, LEVEL_DEBUG, NULL, "Locking " + get_lock_name(myLock));
	pthread_mutex_lock(get_lock(myLock));
}

void netmrg_mutex_unlock(Lock myLock)
{
	debuglogger(DEBUG_THREAD, LEVEL_DEBUG, NULL, "Unlocking " + get_lock_name(myLock));
	pthread_mutex_unlock(get_lock(myLock));
}

int	netmrg_mutex_trylock(Lock myLock)
{
	debuglogger(DEBUG_THREAD, LEVEL_DEBUG, NULL, "Trying to lock " + get_lock_name(myLock));
	return pthread_mutex_trylock(get_lock(myLock));
}
