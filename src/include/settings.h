/********************************************
* NetMRG Integrator
*
* settings.h
* NetMRG Gatherer Settings Library Header
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_SETTINGS
#define NETMRG_SETTINGS
#include "types.h"

using std::string;

const int settings_count = 10;

enum Setting 
{
	SET_DB_HOST,
	SET_DB_USER,
	SET_DB_PASS,
	SET_DB_DB,
	SET_THREAD_COUNT,
	SET_THREAD_SLEEP,
	SET_ROOT,
	SET_RRDTOOL
};

// functions to set and get settings
const string &	get_setting(Setting);
int				get_setting_int(Setting);
void			set_setting(Setting, const string &);
void			set_setting_int(Setting, int);

#endif
