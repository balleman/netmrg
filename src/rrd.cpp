/********************************************
* NetMRG Integrator
*
* rrd.cpp
* NetMRG Gatherer RRDTOOL integration
*
* see doc/LICENSE for copyright information
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

	mutex_lock(lkRRD);
	fprintf(rrdtool_pipe, cmd.c_str());
	mutex_unlock(lkRRD);
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

	command = "create " + get_rrd_file(inttostr(info.monitor_id)) +
			" DS:mon_" + inttostr(info.monitor_id) + ":" + rrd.data_type +
			":600:" + rrd.min_val + ":" + rrd.max_val + " " +
			"RRA:AVERAGE:0.5:1:600 " +
			"RRA:AVERAGE:0.5:6:700 " 	+
			"RRA:AVERAGE:0.5:24:775 " 	+
			"RRA:AVERAGE:0.5:288:797 " 	+
			"RRA:LAST:0.5:1:600 " 		+
			"RRA:LAST:0.5:6:700 " 		+
			"RRA:LAST:0.5:24:775 " 		+
			"RRA:LAST:0.5:288:797 " 	+
			"RRA:MAX:0.5:1:600 " 		+
			"RRA:MAX:0.5:6:700 "		+
			"RRA:MAX:0.5:24:775 "		+
			"RRA:MAX:0.5:288:797";
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
	string command = "update " + get_rrd_file(inttostr(info.monitor_id)) + " N:" + stripnl(info.curr_val);
	rrd_cmd(info, command);
}

// update_monitor_rrd
//
// for a monitor:
//	1. Create a RRD file if one doesn't exist.
//	2. Tune the RRD file if necessary.
//	3. Update the RRD file with current data.

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

	update_rrd(info, rrd);
}

