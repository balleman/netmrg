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

const int settings_count = 12;

enum Setting 
{
	setDBHost,
	setDBUser,
	setDBPass,
	setDBDB,
	setThreadCount,
	setThreadSleep,
	setPathRRDTOOL,
	setPathLockFile,
	setPathRuntimeFile,
	setPathLibexec,
	setPathRRDs,
	setPollInterval
};

// functions to set and get settings
string		get_setting(Setting);
long int	get_setting_int(Setting);
void		set_setting(Setting, const string &);
void		set_setting_int(Setting, long int);

// functions to load settings
void		load_settings_default();
void		load_settings_file(const string & filename);

// other settings functions
void		print_settings();

#endif
