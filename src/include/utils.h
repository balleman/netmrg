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

#include "common.h"
#include "types.h"
#include <cstdio>
#include <cstdlib>

using std::string;

// NetMRG Components
const int DEBUG_GLOBAL		= 1;
const int DEBUG_THREAD		= 2;
const int DEBUG_DEVICE		= 4;
const int DEBUG_SUBDEVICE	= 8;
const int DEBUG_MONITOR		= 16;
const int DEBUG_EVENT		= 32;
const int DEBUG_RESPONSE	= 64;
const int DEBUG_RRD			= 128;
const int DEBUG_SNMP		= 256;
const int DEBUG_GATHERER	= 512;
const int DEBUG_MYSQL		= 1024;

// Logging Levels (see syslog(3) manpage for definitions)
const int LEVEL_EMERG		= 1;
const int LEVEL_ALERT		= 2;
const int LEVEL_CRITICAL	= 4;	// this is the worst condition currently used
const int LEVEL_ERROR		= 8;
const int LEVEL_WARNING		= 16;
const int LEVEL_NOTICE		= 32;
const int LEVEL_INFO		= 64;
const int LEVEL_DEBUG		= 128;

// general functions
int 			file_exists(string filename);
string  		stripnl(string input);
string			token_replace(string &source, string token, string value);
u_char			*u_string(string source, u_char *out);
string			inttostr(long long int int_to_convert);
long long int 	strtoint(string string_to_convert);
string			inttopadstr(int integer, int padlen);
string			count_file_lines(DeviceInfo info);
void 			U_to_NULL(string *input);
uint			worstof(uint a, uint b);

// debugging functions
int				get_debug_level();
void			set_debug_level(int level);
int				get_debug_components();
void			set_debug_components(int components);
void 			debuglogger(int component, int level, const DeviceInfo *, const string & message);

#endif
