/*

   NetMRG Utilities
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

*/

// file_exists
//
// evaluates to true if filename specifies an existing file

int file_exists(string filename)
{
	struct stat file_stat;
	stat(filename.c_str(), &file_stat);
	return S_ISREG(file_stat.st_mode);
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

// keyword_replace - replace a keyword with a value throughout a string
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

// debuglogger - netmrg's version of syslog
// level is defined as follows:
// 0 is the LSB for the value.
//
// Bit-by-bit meanings:
//
// 0 - Global
// 1 - Thread
// 2 - Device
// 3 - Sub-Device
// 4 - Monitor
// 5 - Event
// 6 - Response
// 6 - RRD
// 7 - SNMP
// 8 - Detailed Gatherer (scripts, snmp, sql)
// 9 - Detailed mysql
//

#define DEBUG_GLOBAL		1
#define	DEBUG_THREAD		2
#define DEBUG_DEVICE		4
#define DEBUG_SUBDEVICE		8
#define DEBUG_MONITOR		16
#define DEBUG_EVENT		32
#define DEBUG_RESPONSE		64
#define DEBUG_RRD		128
#define DEBUG_SNMP		256
#define DEBUG_GATHERER		512
#define DEBUG_MYSQL		1024

// Debugging Options
int debug_level = DEBUG_GLOBAL + DEBUG_THREAD + DEBUG_DEVICE + DEBUG_SUBDEVICE + DEBUG_MONITOR +
			DEBUG_EVENT + DEBUG_RESPONSE + DEBUG_RRD + DEBUG_SNMP + DEBUG_GATHERER + DEBUG_MYSQL;



void debuglogger(int level, DeviceInfo *info_in, string message)
{
	DeviceInfo info;

	if (info_in != NULL)
	{
		info = *info_in;
	}

	if (debug_level && level)
	{

		string tempmsg = "";

		if (info.device_id != -1)
		{
			tempmsg = tempmsg + string("[Dev: ") +
				inttopadstr(info.device_id, 4) + string("] ");
		}

		if (info.subdevice_id != -1)
		{
			tempmsg = tempmsg + string("[Sub: ") +
				inttopadstr(info.subdevice_id, 4) + string("] ");
		}

		if (info.monitor_id != -1)
		{
			tempmsg = tempmsg + string("[Mon: ") +
				inttopadstr(info.monitor_id, 4) + string("] ");
		}

		if (info.event_id != -1)
		{
			tempmsg = tempmsg + string("[Ev: ") +
				inttopadstr(info.event_id, 4) + string("] ");
		}

		if (info.response_id != -1)
		{
			tempmsg = tempmsg + string("[Resp: ") +
				inttopadstr(info.response_id, 4) + string("] ");
		}

		tempmsg = tempmsg + message;

		printf("%s\n", tempmsg.c_str());
	}

} // end debuglogger

// count_file_lines
//
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
                	debuglogger(DEBUG_MONITOR, &info, "Internal Test: Line Count: Unable to read file (" + info.test_params + ")");
			return "U";
		}
  	}
	else
	{
		debuglogger(DEBUG_MONITOR, &info, "Internal Test: Line Count: File does not exist (" + info.test_params + ")");
		return "U";
	}

	return inttostr(linecount);
}
