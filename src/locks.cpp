/********************************************
* NetMRG Integrator
*
* locks.cpp
* NetMRG Gatherer Locks
*
* see doc/LICENSE for copyright information
********************************************/

#include "locks.h"
#include <pthread.h>

// Create mutex locks
static pthread_mutex_t active_threads_lock 	= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t mysql_lock 		= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t snmp_lock 		= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t rrdtool_lock 		= PTHREAD_MUTEX_INITIALIZER;

pthread_mutex_t* get_lock(Lock myLock)
{
	pthread_mutex_t *ret_val;

	switch (myLock)
	{
		case lkActiveThreads:	ret_val = &active_threads_lock;		break;
		case lkMySQL:		ret_val = &mysql_lock;			break;
		case lkSNMP:		ret_val = &snmp_lock;			break;
		case lkRRD:		ret_val = &rrdtool_lock;		break;
	}

	return ret_val;
}

void	mutex_lock(Lock myLock)
{
	pthread_mutex_lock(get_lock(myLock));
}

void	mutex_unlock(Lock myLock)
{
	pthread_mutex_unlock(get_lock(myLock));
}

int	mutex_trylock(Lock myLock)
{
	return pthread_mutex_trylock(get_lock(myLock));
}                                                      
