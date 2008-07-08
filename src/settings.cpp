/********************************************
* NetMRG Integrator
*
* settings.cpp
* NetMRG Gatherer Settings Library
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

#include <libxml/xmlmemory.h>
#include <libxml/parser.h>

#include "settings.h"
#include "locks.h"
#include "utils.h"

static string current_settings[settings_count];

string get_setting(Setting x)
{
	netmrg_mutex_lock(lkSettings);
	string temp = current_settings[x];
	netmrg_mutex_unlock(lkSettings);
	return temp;
}

long int get_setting_int(Setting x)
{
	return strtoint(get_setting(x));
}

void set_setting(Setting x, const string & newvalue)
{
	netmrg_mutex_lock(lkSettings);
	current_settings[x] = newvalue;
	netmrg_mutex_unlock(lkSettings);
}

void set_setting_int(Setting x, long int newvalue)
{
	set_setting(x, inttostr(newvalue));
}

void load_settings_default()
{
	// threads
	set_setting_int(setThreadCount, DEF_THREAD_COUNT);

	// database
	set_setting(setDBHost, DEF_DB_HOST);
	set_setting(setDBUser, DEF_DB_USER);
	set_setting(setDBPass, DEF_DB_PASS);
	set_setting(setDBDB,   DEF_DB_DB);
	set_setting(setDBSock, DEF_DB_SOCK);
	set_setting_int(setDBPort, DEF_DB_PORT);
	set_setting_int(setDBTimeout, DEF_DB_TIMEOUT);

	// paths
	set_setting(setPathRRDTOOL, DEF_RRDTOOL);
	set_setting(setPathLockFile, DEF_LOCKFILE);
	set_setting(setPathRuntimeFile, DEF_RUNTIME_FILE);
	set_setting(setPathLibexec, DEF_LIBEXEC);
	set_setting(setPathRRDs, DEF_RRDS);
	set_setting(setPathLocale, DEF_LOCALE);

	// other
	set_setting_int(setPollInterval, DEF_POLL_INTERVAL);
	set_setting_int(setMaxDeviceLogEntries, DEF_MAX_DEV_LOG);
	set_setting(setSyslogFacility, DEF_SYSLOG_FACILITY);

	// update intl info
	setup_intl();
}

void print_settings()
{
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Database --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Host:    " + get_setting(setDBHost));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "User:    " + get_setting(setDBUser));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Pass:    " + get_setting(setDBPass));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "DB:      " + get_setting(setDBDB));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Sock:    " + get_setting(setDBSock));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Port:    " + get_setting(setDBPort));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Timeout: " + get_setting(setDBTimeout));

	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Threads --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Count: " + get_setting(setThreadCount));

	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Paths --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "RRDTOOL:      " + get_setting(setPathRRDTOOL));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Lock File:    " + get_setting(setPathLockFile));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Runtime File: " + get_setting(setPathRuntimeFile));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Libexec:      " + get_setting(setPathLibexec));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "RRDs:         " + get_setting(setPathRRDs));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Locale:       " + get_setting(setPathLocale));

	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Other --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Poll Interval:   " + get_setting(setPollInterval));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Max Dev Logs:    " + get_setting(setMaxDeviceLogEntries));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Syslog Facility: " + get_setting(setSyslogFacility));
}

string xmltostring(const xmlChar * input)
{
	if (input == NULL) return string("");
	char temp[1024];
	snprintf(temp, 1023, "%s", input);
	return string(temp);
}

void parse_config_section(xmlDocPtr doc, xmlNodePtr cur, string section)
{
	// parses a section of an already loaded config file
	xmlChar * value;
	string val_str;

	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, (string)_("Parsing config section") + " '" + section + "'");

	cur = cur->xmlChildrenNode;
	while (cur != NULL)
	{
		value = xmlNodeListGetString(doc, cur->xmlChildrenNode, 1);
		val_str = string(xmltostring(value));
		if (section == "database")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "host"))
				set_setting(setDBHost, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "user"))
				set_setting(setDBUser, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "password"))
				set_setting(setDBPass, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "db"))
				set_setting(setDBDB, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "socket"))
				set_setting(setDBSock, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "port"))
				set_setting(setDBPort, val_str);
				
			if (!xmlStrcmp(cur->name, (const xmlChar *) "timeout"))
				set_setting(setDBTimeout, val_str);
		}
		else
		if (section == "paths")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "rrdtool"))
				set_setting(setPathRRDTOOL, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "lockfile"))
				set_setting(setPathLockFile, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "runtimefile"))
				set_setting(setPathRuntimeFile, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "libexec"))
				set_setting(setPathLibexec, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "rrds"))
				set_setting(setPathRRDs, val_str);

			if (!xmlStrcmp(cur->name, (const xmlChar *) "locale"))
				set_setting(setPathLocale, val_str);

		}
		else
		if (section == "threads")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "count"))
				set_setting(setThreadCount, val_str);
		}
		else
		if (section == "polling")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "interval"))
				set_setting(setPollInterval, val_str);
		}
		else
		if (section == "logging")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "max_device_entries"))
				set_setting(setMaxDeviceLogEntries, val_str);
			if (!xmlStrcmp(cur->name, (const xmlChar *) "syslog_facility"))
				set_setting(setSyslogFacility, val_str);
		}
		else
		debuglogger(DEBUG_GLOBAL, LEVEL_WARNING, NULL, (string)_("Second stage parser not aware of this section."));
		xmlFree(value);
		cur = cur->next;
	} // end while not null
} // end parse_config_section()

void load_settings_file(const string & filename)
{
	// parses xml-based config file
	xmlDocPtr doc;
	xmlNodePtr cur;

	doc = xmlParseFile(filename.c_str());
	if (doc == NULL)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, (string)_("Failed to parse configuration file") + " (" + filename + ")");
		return;
	}

	cur = xmlDocGetRootElement(doc);

	if (cur == NULL)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, (string)_("Empty configuration file") + " (" + filename + ")");
		xmlFreeDoc(doc);
		return;
	}

	if (xmlStrcmp(cur->name, (const xmlChar *) "netmrg"))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, (string)_("Configuration file of the wrong type.  Root node is not 'netmrg.'") + " (" + filename + ")");
		xmlFreeDoc(doc);
		return;
	}

	cur = cur->xmlChildrenNode;

	// read each section of the config file
	while (cur != NULL)
	{
		if (
				!xmlStrcmp(cur->name, (const xmlChar *) "database") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "paths") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "threads") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "polling") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "logging")
			)
		{
			parse_config_section(doc, cur, xmltostring(cur->name));
		}
		else
		if (
				!xmlStrcmp(cur->name, (const xmlChar *) "website") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "rrdtool") ||
				!xmlStrcmp(cur->name, (const xmlChar *) "text")
			)
		{
			// ignored sections
		}
		else
		debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, (string)_("Unexpected section in configuration file: ") + xmltostring(cur->name));

		cur = cur->next;
	}

	xmlFreeDoc(doc);

	// settings may have changed, reset intl info to be safe
	setup_intl();
}

// setup_intl - internationalization support
void setup_intl()
{
	// use language settings from the environment
	setlocale(LC_ALL, "");

	// set the location of translation tables for the domain
	bindtextdomain("netmrg", get_setting(setPathLocale).c_str());

	// select the domain
	textdomain("netmrg");
}

