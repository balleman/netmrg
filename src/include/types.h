/********************************************
* NetMRG Integrator
*
* types.h
* NetMRG Data Structures
*
* see doc/LICENSE for copyright information
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
	int subdevice_id;
	int monitor_id;
	int event_id;
	int response_id;

	uint status;

	void * snmp_sess_p;
	uint snmp_avoid;
	uint snmp_recache;
	uint snmp_ifnumber;
	long long int snmp_uptime;
	
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
	unsigned long snmp_timeout;
	unsigned int snmp_retries;
	unsigned short snmp_port;

	list<ValuePair> parameters;

	DeviceInfo()
	{
		device_id 		= -1;
		subdevice_id	= -1;
		monitor_id		= -1;
		event_id		= -1;
		response_id		= -1;

		status			=  0;

		snmp_avoid		=  0;
		snmp_recache	=  0;
		snmp_ifnumber	=  0;
		snmp_uptime		=  0;

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

#endif
