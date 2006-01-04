/********************************************
* NetMRG Integrator
*
* monitors.h
* NetMRG Gatherer Monitors Library Header
*
* see doc/LICENSE for copyright information
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
