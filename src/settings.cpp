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

const string & get_setting(Setting x)
{
	return current_settings[x];
}

int get_setting_int(Setting x)
{
	return strtoint(get_setting(x));
}

void set_setting(Setting x, const string & newvalue)
{
	current_settings[x] = newvalue;
}

void set_setting_int(Setting x, int newvalue)
{
	set_setting(x, inttostr(newvalue));
}
