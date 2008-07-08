/********************************************
* NetMRG Integrator
*
* snmp.h
* NetMRG Gatherer SNMP Library Header
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

#ifndef NETMRG_SNMP
#define NETMRG_SNMP

#include "common.h"
#include "types.h"
#include <string>
#include <list>

void snmp_init();
void snmp_cleanup();
void snmp_session_init(DeviceInfo & info);
void snmp_session_cleanup(DeviceInfo & info);
string snmp_get(DeviceInfo info, string oidstring);
string snmp_diff(DeviceInfo info, string oid1, string oid2);
list<SNMPPair> snmp_trim_rootoid(list<SNMPPair> input, string rootoid);
list<SNMPPair> snmp_swap_index_value(list<SNMPPair> input);
list<SNMPPair> snmp_walk(DeviceInfo info, string oidstring);
long long int get_snmp_uptime(DeviceInfo info);

#endif

