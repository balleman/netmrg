/********************************************
* NetMRG Integrator
*
* locks.h
* NetMRG Gatherer Locks Header File
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

