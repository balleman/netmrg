/********************************************
* NetMRG Integrator
*
* utils.cpp
* NetMRG Gatherer Utilities Library
*
* see doc/LICENSE for copyright information
********************************************/

#include <string>
#include <sys/stat.h>
#include <sys/types.h>
#include <syslog.h>

#include "utils.h"
#include "db.h"

// vt100_compatible
//
// returns true if our terminal seems to be VT100 compatible
bool vt100_compatible()
{
	if (!isatty(STDOUT_FILENO)) return false;
	char *term = getenv("TERM");
	if (!term) return false;
	if (!strncasecmp(term, "linux", 5)) return true;
	if (!strncasecmp(term, "xterm", 5)) return true;
	if (!strncasecmp(term, "vt", 2))	return true;
}

// file_exists
//
// evaluates to true if filename specifies an existing file

int file_exists(string filename)
{
	struct stat file_stat;
	return !(stat(filename.c_str(), &file_stat));
}

// stripnl - given a string, return a string without new line at the end
string stripnl(string input)
{
	string temp_str;

	if (input[input.length() - 1] == '\n')
	{
		temp_str = input.substr(0, input.length() - 1);
	}
	else
	{
		temp_str = input;
	}
	
	return temp_str;
	
} // end stripnl

// token_replace - replace a token with a value throughout a string
string token_replace(string &source, string token, string value)
{
	long int i;

	while ((i = source.find(token)) >= 0)
	{
		source.replace(i, token.length(), value);
	}

	return source;
}

// u_string - cast a string into a u_char array
u_char *u_string(string source, u_char *out)
{
	return (unsigned char *)source.c_str();
}


// formatting functions

// inttostr - converts an integer to a string
string inttostr(long long int int_to_convert)
{
	char temp_str[100];
	snprintf(temp_str, 100, "%lld", int_to_convert);
	return string(temp_str);
} // end inttostr

// strtoint - converts a string to an integer
long long int strtoint(string string_to_convert)
{
	return strtoll(string_to_convert.c_str(), NULL, 10);
} // end strtoint

// inttopadstr - converts a string to an integer, adding 0s to pad to a given length
string inttopadstr(int integer, int padlen)
{
	char tempstr[255];
	string format = string("%0") + inttostr(padlen) + string("d");
	snprintf(tempstr, 255, format.c_str(), integer);
	return string(tempstr);
} // end inttopadstr


// debuglogger - NetMRG's version of syslog

// Debugging Options
static int debug_components = DEBUG_DEFAULT;
static int debug_level 		= LEVEL_DEFAULT;
static int log_method		= LOG_METHOD_STDOUT;
static bool debug_safety	= false;

// Debugging Options Manipulations
void set_debug_level(int level)
{
	debug_level = level;
}

int get_debug_level()
{
	return debug_level;
}

void set_debug_components(int components)
{
	debug_components = components;
}

int get_debug_components()
{
	return debug_components;
}

void set_debug_safety(bool safety)
{
	debug_safety = safety;
}

bool get_debug_safety()
{
	return debug_safety;
}

void set_log_method(int method)
{
	log_method = method;
}

int get_log_method()
{
	return log_method;
}

int level_to_priority(int level)
{
	switch (level)
	{
		case LEVEL_EMERG: 		return LOG_EMERG;
		case LEVEL_ALERT: 		return LOG_ALERT;
		case LEVEL_CRITICAL: 	return LOG_CRIT;
		case LEVEL_ERROR: 		return LOG_ERR;
		case LEVEL_WARNING:		return LOG_WARNING;
		case LEVEL_NOTICE:		return LOG_NOTICE;
		case LEVEL_INFO:		return LOG_INFO;
		case LEVEL_DEBUG:		return LOG_DEBUG;
	}
}

int level_to_color(int level)
{
	switch (level)
	{
		case LEVEL_EMERG:		return COLOR_MAGENTA;
		case LEVEL_ALERT:		return COLOR_RED;
		case LEVEL_CRITICAL:	return COLOR_RED;
		case LEVEL_ERROR:		return COLOR_BROWN;
		case LEVEL_WARNING:		return COLOR_BROWN;
		case LEVEL_NOTICE:		return COLOR_CYAN;
		case LEVEL_INFO:		return COLOR_WHITE;
		case LEVEL_DEBUG:		return COLOR_GREEN;
	}
}

int level_to_attrib(int level)
{
	switch (level)
	{
		case LEVEL_EMERG:		return ATTR_BRIGHT;
		case LEVEL_ALERT:		return ATTR_BRIGHT;
		case LEVEL_ERROR:		return ATTR_BRIGHT;
		case LEVEL_NOTICE:		return ATTR_DIM;
		case LEVEL_INFO:		return ATTR_RESET;
		default:				return ATTR_RESET;
	}
}


// censor_message - replace the contents of braces with a 'Field Omitted' message
string censor_message(const string & message)
{
	string tempmsg = string(message);
	unsigned int pos;
	
	while ((pos = tempmsg.find("{")) != string::npos)
	{
		tempmsg.replace(pos, tempmsg.find("}") - pos + 1, "<Field Omitted>");
	}
	
	return tempmsg;
} // end censor_message

// remove_braces - erase braces from a string
string remove_braces(const string & message)
{
	string tempmsg = string(message);
	unsigned int pos;	

	while ((pos = tempmsg.find("{")) != string::npos)
	{
		tempmsg.erase(pos, 1);
	}
	
	while ((pos = tempmsg.find("}")) != string::npos)
	{
		tempmsg.erase(pos, 1);
	}
	
	return tempmsg;
} // end remove_braces

// debuglogger
//
// component	- the sum of the components this message pertains to
// level		- the sum of the levels this message pertains to
// info			- the DeviceInfo struct, used to display the context of the message
// message		- the message, sensitive information enclosed in braces will be censored when desired
//
void debuglogger(int component, int level, const DeviceInfo * info, const string & message)
{
	// only proceed if this message is qualified for display
	if ((debug_level & level) && (debug_components & component))
	{
		string tempmsg = "";

		// debug the debugging information
		if ((debug_level & LEVEL_DEBUG) && (debug_components & DEBUG_LOGGING))
		{
			tempmsg = tempmsg + string("[L: ") + inttopadstr(level, 4) + ", C: " + inttopadstr(component, 4) + "] ";
		}

		// display context information
		if (info != NULL)
		{
			if (info->device_id != -1)
			{
				tempmsg = tempmsg + string("[Dev: ") + inttopadstr(info->device_id, 4) + string("] ");
			}

			if (info->subdevice_id != -1)
			{
				tempmsg = tempmsg + string("[Sub: ") + inttopadstr(info->subdevice_id, 4) + string("] ");
			}

			if (info->monitor_id != -1)
			{
				tempmsg = tempmsg + string("[Mon: ") + inttopadstr(info->monitor_id, 4) + string("] ");
			}

			if (info->event_id != -1)
			{
				tempmsg = tempmsg + string("[Ev: ") + inttopadstr(info->event_id, 4) + string("] ");
			}

			if (info->response_id != -1)
			{
				tempmsg = tempmsg + string("[Resp: ") + inttopadstr(info->response_id, 4) + string("] ");
			}
		} // end display context information

		string context = tempmsg;
		string fullmessage;
		string content;
		
		// censor or remove censoring data as appropriate
		if (debug_safety)
		{
			content = censor_message(message);
		}
		else
		{
			content = remove_braces(message);
		}

		fullmessage = context + content;

		if (log_method & LOG_METHOD_STDOUT)
			printf("%s\n", fullmessage.c_str());
				
		// print the formatted message in color
		if (log_method & LOG_METHOD_VT100)
			printf("%c[%d;%d;%dm%s%c[%d;%d;%dm%s\n%c[0;%d;%dm", ESC, ATTR_BRIGHT, COLOR_BLACK, COLOR_BLACK + 10, context.c_str(), ESC, level_to_attrib(level), level_to_color(level), COLOR_BLACK+10, content.c_str(), ESC, COLOR_WHITE, COLOR_BLACK + 10);
		
		// syslog the message
		if (log_method & LOG_METHOD_SYSLOG)
			syslog(level_to_priority(level), "%s", fullmessage.c_str());
	}

	// log message to database, if possible, and if important enough
	if (info && info->mysql && (level < LEVEL_INFO))
	{
		string device, subdevice, monitor;

		if (info->device_id == -1)
			device = "NULL";
		else
			device = inttostr(info->device_id);

		if (info->subdevice_id == -1)
			subdevice = "NULL";
		else
			subdevice = inttostr(info->subdevice_id);

		if (info->monitor_id == -1)
			monitor = "NULL";
		else
			monitor = inttostr(info->monitor_id);

		db_update((MYSQL *) info->mysql, NULL, string("INSERT INTO log SET date=NOW(), dev_id=") + device + ", subdev_id=" +
			subdevice + ", mon_id=" + monitor + ", level=" + inttostr(level) + ", component=" + inttostr(component) +
			", message = '" + db_escape(remove_braces(message)) + "'");
	}


} // end debuglogger

// count_file_lines
string count_file_lines(DeviceInfo info)
{
	FILE *fhandle;
	char ach;
	int linecount = 0;

	if (file_exists(info.test_params))
	{
		fhandle = fopen((info.test_params).c_str(), "r");
		if (fhandle != NULL)
		{
			while ((ach = fgetc(fhandle)) != EOF)
			{
				if (ach == '\n')
				{
					linecount++;
				}
			}
			fclose(fhandle);
		}
		else
		{
			debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Internal Test: Line Count: Unable to read file (" + info.test_params + ")");
			return "U";
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Internal Test: Line Count: File does not exist (" + info.test_params + ")");
		return "U";
	}

	return inttostr(linecount);
}

void U_to_NULL(string & input)
{
	if (input == "U")
	{
		input = "NULL";
	}
	else
	{
		input = string("'") + db_escape(input) + string("'");
	}
}

uint worstof(uint a, uint b)
{
	return (a > b) ? a : b;
}
