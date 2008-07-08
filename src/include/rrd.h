/********************************************
* NetMRG Integrator
*
* rrd.h
* NetMRG Gatherer RRDTOOL integration
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

#ifndef NETMRG_RRD
#define NETMRG_RRD

#include "types.h"
#include <string>

// create a connection to RRD
void rrd_init();

// clean up connection to RRD
void rrd_cleanup();

// send a string to RRDTOOL
void rrd_cmd(DeviceInfo info, string cmd);

// returns the file name for the .rrd file 
string get_rrd_file(string mon_id);

// creates a new rrd file
void create_rrd(DeviceInfo info, RRDInfo rrd);

// alters the parameters of an rrd file
void tune_rrd(DeviceInfo info, RRDInfo rrd);

// updates the data in an rrd file
void update_rrd(DeviceInfo info, RRDInfo rrd);

// create, update, and/or tune an rrd file as needed
void update_monitor_rrd(DeviceInfo info, RRDInfo rrd);

#endif

