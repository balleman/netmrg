/********************************************
* NetMRG Integrator
*
* mappings.h
* NetMRG Gatherer Mappings Library Header
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_MAPPINGS
#define NETMRG_MAPPINGS

#include "types.h"
#include "db.h"


// caching functions
void do_snmp_interface_recache(DeviceInfo *info, MYSQL *mysql);
void do_snmp_disk_recache(DeviceInfo *info, MYSQL *mysql);

// parameter setup functions
int setup_interface_parameters(DeviceInfo *info, MYSQL *mysql);
int setup_disk_parameters(DeviceInfo *info, MYSQL *mysql);

// misc functions
void parse_fancy_alias(DeviceInfo *info, string alias);

#endif
