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

#include "utils.h"

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


string inttostr(long long int int_to_convert)
{
	char temp_str[100];

	sprintf(temp_str, "%qd", int_to_convert);

	return string(temp_str);

} // end inttostr

long long int strtoint(string string_to_convert)
{
	return strtoq(string_to_convert.c_str(), NULL, 10);
} // end strtoint

string inttopadstr(int integer, int padlen)
{

	char tempstr[255];
	string format = string("%0") + inttostr(padlen) + string("d");
	sprintf(tempstr, format.c_str(), integer);
	return string(tempstr);

} // end inttopadstr

// debuglogger - NetMRG's version of syslog

// Debugging Options
static int debug_components = 
	DEBUG_GLOBAL + DEBUG_THREAD + DEBUG_DEVICE + DEBUG_SUBDEVICE + DEBUG_MONITOR +
	DEBUG_EVENT + DEBUG_RESPONSE + DEBUG_RRD + DEBUG_SNMP + DEBUG_GATHERER + DEBUG_MYSQL;
	
static int debug_level = 
	LEVEL_EMERG + LEVEL_ALERT + LEVEL_CRITICAL + LEVEL_ERROR + LEVEL_WARNING + 
	LEVEL_NOTICE + LEVEL_INFO;

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

void debuglogger(int component, int level, const DeviceInfo * info, const string & message)
{
	if ((debug_level & level) && (debug_components & component))
	{
		string tempmsg = "";
		if (info !=  NULL)
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
		}
		
		tempmsg = tempmsg + message;
		printf("%s\n", tempmsg.c_str());
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

void U_to_NULL(string *input)
{
	if ((*input) == "U")
	{
		(*input) = "NULL";
	}
	else
	{
		(*input) = string("'") + (*input) + string("'");
	}
}

uint worstof(uint a, uint b)
{
	if (a > b)
	{
		return a;
	}
	else
	{
		return b;
	}
}
