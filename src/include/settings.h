/********************************************
* NetMRG Integrator
*
* settings.h
* NetMRG Gatherer Settings Library Header
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

#ifndef NETMRG_SETTINGS
#define NETMRG_SETTINGS
#include "types.h"

using std::string;

const int settings_count = 17;

enum Setting
{
	setDBHost,
	setDBUser,
	setDBPass,
	setDBDB,
	setDBSock,
	setDBPort,
	setThreadCount,
	setPathRRDTOOL,
	setPathLockFile,
	setPathRuntimeFile,
	setPathLibexec,
	setPathLocale,
	setPathRRDs,
	setPollInterval,
	setMaxDeviceLogEntries,
	setSyslogFacility,
	setDBTimeout
};

// functions to set and get settings
string		get_setting(Setting);
long int	get_setting_int(Setting);
void		set_setting(Setting, const string &);
void		set_setting_int(Setting, long int);

// functions to load settings
void		load_settings_default();
void		load_settings_file(const string & filename);

// other functions
void		print_settings();
void		setup_intl();

#endif
