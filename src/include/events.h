/********************************************
* NetMRG Integrator
*
* events.h
* NetMRG Gatherer Events Header File
*
* see doc/LICENSE for copyright information
********************************************/   

#ifndef NETMRG_EVENTS
#define NETMRG_EVENTS

#include "types.h"
#include "db.h"

uint process_events(DeviceInfo info, MYSQL *mysql);
uint process_event(DeviceInfo info, MYSQL *mysql, int trigger_type, int last_status, int situation, long int last_triggered);
uint process_condition(DeviceInfo info, long long int compare_value, int value_type, int condition);
void process_responses(DeviceInfo info, MYSQL *mysql);


#endif
