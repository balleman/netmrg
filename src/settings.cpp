/********************************************
* NetMRG Integrator
*
* settings.cpp
* NetMRG Gatherer Settings Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "settings.h"
#include "locks.h"
#include "utils.h"

static string current_settings[settings_count];

string get_setting(Setting x)
{
	mutex_lock(lkSettings);
	string temp = current_settings[x];
	mutex_unlock(lkSettings);
	return temp;
}

long int get_setting_int(Setting x)
{
	return strtoint(get_setting(x));
}

void set_setting(Setting x, const string & newvalue)
{
	mutex_lock(lkSettings);
	current_settings[x] = newvalue;
	mutex_unlock(lkSettings);
}

void set_setting_int(Setting x, long int newvalue)
{
	set_setting(x, inttostr(newvalue));
}

void load_settings_default()
{
	// threads
	set_setting_int(setThreadCount, DEF_THREAD_COUNT);
	set_setting_int(setThreadSleep, DEF_THREAD_SLEEP);
	
	// database
	set_setting(setDBHost, DEF_DB_HOST);
	set_setting(setDBUser, DEF_DB_USER);
	set_setting(setDBPass, DEF_DB_PASS);
	set_setting(setDBDB, DEF_DB_DB);
}

void load_settings_file(const string & filename)
{
	// parses xml-based config file
}

