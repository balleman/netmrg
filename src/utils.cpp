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
#include "db.h"

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
		
		// censor or remove censoring data as appropriate
		if (debug_safety)
		{
			tempmsg = tempmsg + censor_message(message);
		}
		else
		{
			tempmsg = tempmsg + remove_braces(message);
		}
			
		// print the formatted message
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
