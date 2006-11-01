/********************************************
* NetMRG Integrator
*
* devices.h
* NetMRG Gatherer Devices Library Header
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_DEVICES
#define NETMRG_DEVICES

#include "types.h"
#include "db.h"

// Device processing
void process_device(int dev_id);
void do_properties_recache(DeviceInfo info, MYSQL *mysql);

// Sub-device processing
uint process_sub_devices(DeviceInfo info, MYSQL *mysql);
uint process_sub_device(DeviceInfo info, MYSQL *mysql);

#endif
