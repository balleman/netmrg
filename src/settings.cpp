/********************************************
* NetMRG Integrator
*
* settings.cpp
* NetMRG Gatherer Settings Library
*
* see doc/LICENSE for copyright information
********************************************/

#include <libxml/xmlmemory.h>
#include <libxml/parser.h>

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
	
	// paths
	set_setting(setPathRRDTOOL, DEF_RRDTOOL);
	set_setting(setPathLockFile, DEF_LOCKFILE);
	set_setting(setPathRuntimeFile, DEF_RUNTIME_FILE);
	set_setting(setPathLibexec, DEF_LIBEXEC);
	set_setting(setPathRRDs, DEF_RRDS);
}

void print_settings()
{
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Database --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Host: " + get_setting(setDBHost));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "User: " + get_setting(setDBUser));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Pass: " + get_setting(setDBPass));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "DB:   " + get_setting(setDBDB));
	
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Threads --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Count: " + get_setting(setThreadCount));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Sleep: " + get_setting(setThreadSleep));
	
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "-- Paths --");
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "RRDTOOL:      " + get_setting(setPathRRDTOOL));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Lock File:    " + get_setting(setPathLockFile));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Runtime File: " + get_setting(setPathRuntimeFile));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Libexec:      " + get_setting(setPathLibexec));
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "RRDs:         " + get_setting(setPathRRDs));
}

string xmltostring(const xmlChar * input)
{
	char temp[1024];
	sprintf(temp, "%s", input);
	return string(temp);
}

void parse_config_section(xmlDocPtr doc, xmlNodePtr cur, string section)
{
	// parses a section of an already loaded config file
	xmlChar * value;
	string val_str;
	
	debuglogger(DEBUG_GLOBAL, LEVEL_DEBUG, NULL, "Parsing config section '" + section + "'");
	
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
			
		}
		else
		if (section == "threads")
		{
			if (!xmlStrcmp(cur->name, (const xmlChar *) "count"))
				set_setting(setThreadCount, val_str);
			
			if (!xmlStrcmp(cur->name, (const xmlChar *) "sleep"))
				set_setting(setThreadSleep, val_str);
		}
		else
		debuglogger(DEBUG_GLOBAL, LEVEL_WARNING, NULL, "Second stage parser not aware of this section.");
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
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Failed to parse configuration file (" + filename + ")");
		return;
	}
	
	cur = xmlDocGetRootElement(doc);
	
	if (cur == NULL)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Empty configuration file (" + filename + ")");
		xmlFreeDoc(doc);
		return;
	}
	
	if (xmlStrcmp(cur->name, (const xmlChar *) "netmrg"))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_ERROR, NULL, "Configuration file of the wrong type.  Root node is not 'netmrg.' (" + filename + ")");
		xmlFreeDoc(doc);
		return;
	}
	
	cur = cur->xmlChildrenNode;
	
	// read each section of the config file
	while (cur != NULL)
	{
		if (!xmlStrcmp(cur->name, (const xmlChar *) "database"))
		{
			parse_config_section(doc, cur, "database");
		}
		else
		if (!xmlStrcmp(cur->name, (const xmlChar *) "paths"))
		{
			parse_config_section(doc, cur, "paths");
		}
		else
		if (!xmlStrcmp(cur->name, (const xmlChar *) "threads"))
		{
			parse_config_section(doc, cur, "threads");
		}
		else
		if (!xmlStrcmp(cur->name, (const xmlChar *) "website"))
		{
			// this is used by the website, we ignore it.
		}
		else
		if (!xmlStrcmp(cur->name, (const xmlChar *) "text"))
		{
			// this seems to happen after every section.  ignore it.
		}
		else
		debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, "Unexpected section '" + xmltostring(cur->name) + "' in configuration file.");
		
		cur = cur->next;
	}
	
	xmlFreeDoc(doc);
}

