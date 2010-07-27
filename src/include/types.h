/********************************************
* NetMRG Integrator
*
* types.h
* NetMRG Data Structures
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

#ifndef NETMRG_TYPES
#define NETMRG_TYPES

#include "common.h"
#include <list>
#include <string>

using std::string;
using std::list;

// ValuePair
//
// Used to store a name and value for a parameter used in
// to expand parameters beforing being passed to external
// scripts or used as part of a query.

struct ValuePair
{
	string name;
	string value;

	ValuePair(string setname, string setvalue)
	{
		name 	= setname;
		value   = setvalue;
	}
};

// DeviceInfo
//
// Used to provide any subroutine working on a device or
// a component of one with all information necessary.
// This allows the information to be added to in only this
// location and where it is needed, not as a parameter
// in all interim functions.

struct DeviceInfo
{
	int device_id;
	int property_id;
	int subdevice_id;
	int monitor_id;
	int event_id;
	int response_id;

	uint status;

	void * mysql;

	void * snmp_sess_p;
	uint snmp_avoid;
	uint snmp_recache;
	uint snmp_ifnumber;
	uint counter_unknowns;
	long long int snmp_uptime;

	uint device_type;
	uint subdevice_type;

	int test_type;
	int test_id;

	string name;
	string ip;
	string subdevice_name;
	string test_params;

	string curr_val;
	string last_val;
	string delta_val;
	string rate_val;
	long long int delta_time;

	unsigned short snmp_version;
	string snmp_read_community;
	string snmp3_user;
	int snmp3_seclev;
	int snmp3_aprot;
	string snmp3_apass;
	int snmp3_pprot;
	string snmp3_ppass;
	unsigned long snmp_timeout;
	unsigned int snmp_retries;
	unsigned short snmp_port;

	list<ValuePair> parameters;

	DeviceInfo()
	{
		device_id 		= -1;
		property_id		= -1;
		subdevice_id	= -1;
		monitor_id		= -1;
		event_id		= -1;
		response_id		= -1;

		status			=  0;

		mysql			=  NULL;

		snmp_avoid		=  0;
		snmp_recache	=  0;
		snmp_ifnumber	=  0;
		snmp_uptime		=  0;
		counter_unknowns=  0;

		subdevice_type	=  0;

		test_type		= -1;
		test_id			= -1;
		//test_params	= "";

		delta_time		= 0;

		curr_val		= "U";
		last_val		= "U";
		delta_val		= "U";
		rate_val		= "U";

		snmp_version	= 0;
		snmp_timeout	= 1000000;
		snmp_retries	= 4;
		snmp_port		= 161;

		snmp3_seclev		= 0;
		snmp3_aprot		= 0;
		snmp3_pprot		= 0;

		snmp_sess_p		= NULL;
	}
};

// RRDInfo
//
// Provides the information needed for creating
// or updating an RRD file.

struct RRDInfo
{
	string max_val;
	string min_val;

	int tuned;

	string value;

	string data_type;

	RRDInfo()
	{
		max_val 	= "U";
		min_val 	= "U";
		tuned   	=   0;
		data_type 	= "";
	}
};

// SNMPPair
// essentially the same as ValuePair, but for
// oid/value pairs needed for SNMP walks

struct SNMPPair
{
	string  oid;
	string  value;

	SNMPPair(string setoid, string setvalue)
	{
		oid   = setoid;
		value = setvalue;
	}
};

// Schedule 

enum ScheduleType { schOnce, schWait };

#endif
