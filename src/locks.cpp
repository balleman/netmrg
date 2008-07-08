/********************************************
* NetMRG Integrator
*
* locks.cpp
* NetMRG Gatherer Locks
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

#include "locks.h"
#include "utils.h"

// Create mutex locks
static pthread_mutex_t active_threads_lock	= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t mysql_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t snmp_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t rrdtool_lock			= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t settings_lock		= PTHREAD_MUTEX_INITIALIZER;
static pthread_mutex_t pipe_lock			= PTHREAD_MUTEX_INITIALIZER;

// Create conditional variables
static pthread_cond_t active_threads_cv		= PTHREAD_COND_INITIALIZER;

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

pthread_cond_t* get_cond(Cond myCond)
{
	switch (myCond)
	{
		case cActiveThreads:	return &active_threads_cv;
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

void netmrg_cond_signal(Cond myCond)
{
	pthread_cond_signal(get_cond(myCond));
}

void netmrg_cond_wait(Cond myCond, Lock myLock)
{
	pthread_cond_wait(get_cond(myCond), get_lock(myLock));
}

