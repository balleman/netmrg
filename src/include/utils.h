/********************************************
* NetMRG Integrator
*
* utils.h
* NetMRG Gatherer Utilities Library Header
*
* see doc/LICENSE for copyright information
********************************************/   

#ifndef NETMRG_UTILS
#define NETMRG_UTILS

#include "types.h"

using std::string;

const int DEBUG_GLOBAL		= 1;
const int DEBUG_THREAD		= 2;
const int DEBUG_DEVICE		= 4;
const int DEBUG_SUBDEVICE	= 8;
const int DEBUG_MONITOR		= 16;
const int DEBUG_EVENT		= 32;
const int DEBUG_RESPONSE	= 64;
const int DEBUG_RRD		= 128;
const int DEBUG_SNMP		= 256;
const int DEBUG_GATHERER	= 512;
const int DEBUG_MYSQL		= 1024;

// Debugging Options
static int debug_levels = DEBUG_GLOBAL + DEBUG_THREAD + DEBUG_DEVICE + DEBUG_SUBDEVICE + DEBUG_MONITOR +
	DEBUG_EVENT + DEBUG_RESPONSE + DEBUG_RRD + DEBUG_SNMP + DEBUG_GATHERER + DEBUG_MYSQL;

int 		file_exists(string filename);
string  	stripnl(string input);
string		token_replace(string &source, string token, string value);
u_char		*u_string(string source, u_char *out);
string		inttostr(long long int int_to_convert);
long long int 	strtoint(string string_to_convert);
string		inttopadstr(int integer, int padlen);
void 		debuglogger(int level, DeviceInfo *info_in, string message);
string		count_file_lines(DeviceInfo info);


#endif
