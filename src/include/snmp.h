/********************************************
* NetMRG Integrator
*
* snmp.h
* NetMRG Gatherer SNMP Library Header
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_SNMP
#define NETMRG_SNMP

#include <string>
#include <list>
#include "types.h"

void snmp_init();
void snmp_cleanup();
string snmp_get(DeviceInfo info, string oidstring);
list<SNMPPair> snmp_trim_rootoid(list<SNMPPair> input, string rootoid);
list<SNMPPair> snmp_swap_index_value(list<SNMPPair> input);
list<SNMPPair> snmp_walk(DeviceInfo info, string oidstring);

#endif

