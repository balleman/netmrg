/********************************************
* NetMRG Integrator
*
* utils.h
* NetMRG Gatherer Utilities Library Header
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

#ifndef NETMRG_UTILS
#define NETMRG_UTILS

#include "common.h"
#include "types.h"
#include "db.h"
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
const int DEBUG_LOGGING		= 2048;
const int DEBUG_FILELINE	= 4096;
const int DEBUG_FUNCTION	= 8192;
const int DEBUG_PROPERTY	= 16384;

const int DEBUG_ALL			= 32767;
const int DEBUG_MOST		= DEBUG_ALL;
const int DEBUG_DEFAULT		= DEBUG_ALL;

// Logging Levels (see syslog(3) manpage for definitions)
const int LEVEL_EMERG		= 1;
const int LEVEL_ALERT		= 2;
const int LEVEL_CRITICAL	= 4;	// this is the worst condition currently used
const int LEVEL_ERROR		= 8;
const int LEVEL_WARNING		= 16;
const int LEVEL_NOTICE		= 32;
const int LEVEL_INFO		= 64;
const int LEVEL_DEBUG		= 128;

const int LEVEL_ALL			= 255;
const int LEVEL_MOST		= 127;
const int LEVEL_DEFAULT		= 63;

// Logging Output Modes
const int LOG_METHOD_STDOUT = 1;
const int LOG_METHOD_SYSLOG = 2;
const int LOG_METHOD_VT100  = 4;

// Terminal Constants
const char ESC 				= 0x1b;
const int  ATTR_RESET		= 0;
const int  ATTR_BRIGHT		= 1;
const int  ATTR_DIM			= 2;
const int  ATTR_UNDER		= 4;
const int  ATTR_BLINK		= 5;
const int  ATTR_REVER		= 7;
const int  ATTR_HIDDEN		= 8;

const int  COLOR_BLACK		= 30;
const int  COLOR_RED		= 31;
const int  COLOR_GREEN		= 32;
const int  COLOR_BROWN		= 33;
const int  COLOR_BLUE		= 34;
const int  COLOR_MAGENTA	= 35;
const int  COLOR_CYAN		= 36;
const int  COLOR_WHITE		= 37;

// terminal functions
bool			vt100_compatible();

// general functions
int 			file_exists(string filename);
string			remove_surrounding_quotes(string input);
string  		strstripnl(string input);
string			token_replace(string &source, string token, string value);
u_char			*u_string(string source, u_char *out);
string			inttostr(long long int int_to_convert);
string			timetostr(const time_t timestamp);
long long int 	strtoint(string string_to_convert);
string			inttopadstr(int integer, int padlen);
string			count_file_lines(DeviceInfo info);
string			read_value_from_file(DeviceInfo info);
void 			U_to_NULL(string & input);
uint			worstof(uint a, uint b);
string			format_time_elapsed(long long int num_secs);
string			remove_nonnumerics(string input);
double			strtodec(string input);

// debugging functions
void			init_logging();
int				get_debug_level();
void			set_debug_level(int level);
int				get_debug_components();
void			set_debug_components(int components);
bool			get_debug_safety();
void			set_debug_safety(bool safety);
void			set_log_method(int method);
int				get_log_method();

string			censor_message(const string & message);
string			remove_braces(const string & message);
#define debuglogger(a,b,c,d) __debuglogger(a, b, __FILE__, __LINE__, __FUNCTION__, c, d)
void 			__debuglogger(int component, int level, const char * file, int line, const char * function, const DeviceInfo *, const string & message);

#endif
