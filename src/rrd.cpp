/********************************************
* NetMRG Integrator
*
* rrd.cpp
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

#include <cstdio>

#include "common.h"
#include "rrd.h"
#include "utils.h"
#include "locks.h"
#include "settings.h"

// RRDTOOL Pipe
FILE *rrdtool_pipe;

void rrd_init()
{
	debuglogger(DEBUG_GLOBAL + DEBUG_RRD, LEVEL_INFO, NULL, "Initializing RRDTOOL pipe.");
	string rrdtool = get_setting(setPathRRDTOOL) + " - ";
	if (!(get_debug_components() & DEBUG_RRD) || !(get_debug_level() & LEVEL_DEBUG))
		rrdtool = rrdtool + " >/dev/null";
	rrdtool_pipe = popen(rrdtool.c_str(), "w");
	if (!rrdtool_pipe)
	{
		debuglogger(DEBUG_GLOBAL + DEBUG_RRD, LEVEL_CRITICAL, NULL, "Failed to initialize RRDTOOL pipe.");
		exit(3);
	}
		
	// sets buffering to one line
	setlinebuf(rrdtool_pipe);
}

void rrd_cleanup()
{
	if (rrdtool_pipe)
	{
		pclose(rrdtool_pipe);
		debuglogger(DEBUG_GLOBAL + DEBUG_RRD, LEVEL_INFO, NULL, "Closed RRDTOOL pipe.");
	}
	else
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Tried to close RRDTOOL pipe before opening it.");
	}
}

// rrd_cmd
//
// issues a command to RRDTOOL via the RRDTOOL pipe, and logs it

void rrd_cmd(DeviceInfo info, string cmd)
{
	debuglogger(DEBUG_RRD, LEVEL_DEBUG, &info, "RRD: '" + cmd + "'");
	cmd = " " + cmd + "\n";

	netmrg_mutex_lock(lkRRD);
	fprintf(rrdtool_pipe, cmd.c_str());
	netmrg_mutex_unlock(lkRRD);
}

// get_rrd_file
//
// returns the name of the rrd file in use for a given monitor

string get_rrd_file(string mon_id)
{
	string filename = get_setting(setPathRRDs) + "/mon_" + mon_id + ".rrd";
	return filename;
}

// create_rrd
//
// creates a new RRD file

void create_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command;
	int poll_interval = get_setting_int(setPollInterval);

	command = "create " + get_rrd_file(inttostr(info.monitor_id)) +
			" --step " + inttostr(poll_interval) + " DS:mon_" + inttostr(info.monitor_id) + ":" 
			+ rrd.data_type + ":" + inttostr(poll_interval * 2) + ":" + rrd.min_val + ":" + 
			rrd.max_val + " " +
			/* Step: Interval; Capacity: 50 hours */ 
			"RRA:AVERAGE:0.5:1:" + inttostr(180000 / poll_interval) + " "     +
			/* Step: 30 mins;  Capacity: 350 hours */ 
			"RRA:AVERAGE:0.5:"   + inttostr(1800   / poll_interval) + ":700 " +
			/* Step: 2 hours;  Capacity: 1550 hours */ 
			"RRA:AVERAGE:0.5:"   + inttostr(7200   / poll_interval) + ":775 " +
			/* Step: 1 day;    Capacity: 19128 hours */ 
			"RRA:AVERAGE:0.5:"   + inttostr(86400  / poll_interval) + ":797 " +

			"RRA:LAST:0.5:1:"    + inttostr(180000 / poll_interval) + " "     +
			"RRA:LAST:0.5:"      + inttostr(1800   / poll_interval) + ":700 " +
			"RRA:LAST:0.5:"      + inttostr(7200   / poll_interval) + ":775 " +
			"RRA:LAST:0.5:"      + inttostr(86400  / poll_interval) + ":797 " +

			"RRA:MAX:0.5:1:"     + inttostr(180000 / poll_interval) + " " 	  +
			"RRA:MAX:0.5:"       + inttostr(1800   / poll_interval) + ":700 " +
			"RRA:MAX:0.5:"       + inttostr(7200   / poll_interval) + ":775 " +
			"RRA:MAX:0.5:"       + inttostr(86400  / poll_interval) + ":797"  ;
	rrd_cmd(info, command);
}

// tune_rrd
//
// modifies the maximum and minimum values acceptable for a given RRD

void tune_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "tune " + get_rrd_file(inttostr(info.monitor_id)) + " -a mon_" +
		inttostr(info.monitor_id) + ":" + rrd.max_val +
		" -i mon_" + inttostr(info.monitor_id) + ":" + rrd.min_val;
	rrd_cmd(info, command);
}

// update_rrd
//
// update an RRD with a current value

void update_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "update " + get_rrd_file(inttostr(info.monitor_id)) + " N:" + strstripnl(info.curr_val);
	rrd_cmd(info, command);
}

// update_monitor_rrd
//
// for a monitor:
//	1. Create a RRD file if one doesn't exist.
//	2. Tune the RRD file if necessary.
//	3. Update the RRD file with current data, or an unknown if we need to do that

void update_monitor_rrd(DeviceInfo info, RRDInfo rrd)
{
	if (!(file_exists(get_rrd_file(inttostr(info.monitor_id)))))
	{
		create_rrd(info, rrd);
	}

	if (rrd.tuned == 0)
	{
		tune_rrd(info, rrd);
	}

	if ( (rrd.data_type != "GAUGE") && (info.counter_unknowns == 1) )
	{
		debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Writing unknown to non-gauge RRD due to restart condition.");
		info.curr_val = "U";
	}

	update_rrd(info, rrd);
}

