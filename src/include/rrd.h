/********************************************
* NetMRG Integrator
*
* rrd.h
* NetMRG Gatherer RRDTOOL integration
*
* see doc/LICENSE for copyright information
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

