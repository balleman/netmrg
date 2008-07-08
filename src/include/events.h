/********************************************
* NetMRG Integrator
*
* events.h
* NetMRG Gatherer Events Header File
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

#ifndef NETMRG_EVENTS
#define NETMRG_EVENTS

#include "types.h"
#include "db.h"

uint process_events(DeviceInfo info, MYSQL *mysql);
uint process_event(DeviceInfo info, MYSQL *mysql, int trigger_type, int last_status, int situation, long int last_triggered, string name);
uint process_condition(DeviceInfo info, long long int compare_value, int value_type, int condition);
void process_responses(DeviceInfo info, MYSQL *mysql);


#endif
