/********************************************
* NetMRG Integrator
*
* monitors.h
* NetMRG Gatherer Monitors Library Header
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

#ifndef NETMRG_MONITORS
#define NETMRG_MONITORS

#include "types.h"
#include "db.h"

// global monitor processing
uint	process_monitor(DeviceInfo info, MYSQL *mysql, RRDInfo rrd);

// processing for specific monitor types
string	process_internal_monitor(DeviceInfo info, MYSQL *mysql);
string	process_sql_monitor(DeviceInfo info, MYSQL *mysql);
string	process_script_monitor(DeviceInfo info, MYSQL *mysql);
string	process_snmp_monitor(DeviceInfo info, MYSQL *mysql);

// support functions
void	update_monitor_db(DeviceInfo info, MYSQL *mysql, RRDInfo rrd);
string	expand_parameters(DeviceInfo &info, string input);
void	params_to_env(DeviceInfo &info, char ** &env);
void	free_env(DeviceInfo &info, char ** &env);


#endif
