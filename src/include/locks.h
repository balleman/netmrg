/********************************************
* NetMRG Integrator
*
* locks.h
* NetMRG Gatherer Locks Header File
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_LOCKS
#define NETMRG_LOCKS

#include "common.h"

enum Lock { lkActiveThreads, lkMySQL, lkSNMP, lkRRD, lkSettings, lkPipe };

void	mutex_lock(Lock);
void	mutex_unlock(Lock);
int		mutex_trylock(Lock);

#endif

