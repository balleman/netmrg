/********************************************
* NetMRG Integrator
*
* devices.h
* NetMRG Gatherer Devices Library Header
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
