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
enum Cond { cActiveThreads };

void	netmrg_mutex_lock(Lock);
void	netmrg_mutex_unlock(Lock);
int		netmrg_mutex_trylock(Lock);

void	netmrg_cond_signal(Cond);
void	netmrg_cond_wait(Cond, Lock);

#endif

