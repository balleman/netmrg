/********************************************
* NetMRG Integrator
*
* db.cpp
* NetMRG Gatherer Database Library
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

#include "common.h"
#include "db.h"
#include "locks.h"
#include "utils.h"
#include "settings.h"


// db_connect
//
// make a new MySQL connection to the NetMRG database

int db_connect(MYSQL *connection)
{
	const char * socket = NULL;
	if (get_setting(setDBSock) != "")
	{
		socket = get_setting(setDBSock).c_str();
	}

	int port = 0;
	if (get_setting_int(setDBPort) > 0)
	{
		port = get_setting_int(setDBPort);
	}

	netmrg_mutex_lock(lkMySQL);
	mysql_init(connection);
	uint timeout = get_setting_int(setDBTimeout);
	mysql_options(connection, MYSQL_OPT_CONNECT_TIMEOUT, (const char *) &timeout);

	if (!(mysql_real_connect(connection, get_setting(setDBHost).c_str(), get_setting(setDBUser).c_str(), get_setting(setDBPass).c_str(), get_setting(setDBDB).c_str(), port, socket, 0)))
	{
		netmrg_mutex_unlock(lkMySQL);
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, NULL, "MySQL Connection Failure. (" + string(mysql_error(connection)) + ")");
		return 0;
	}
	else
	{
		netmrg_mutex_unlock(lkMySQL);
		return 1;
	}
}

// db_query
//
// perform a MySQL query and return the results

MYSQL_RES *db_query(MYSQL *mysql, const DeviceInfo *info, const string & query)
{
	MYSQL_RES *mysql_res = NULL;

	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Query Failed. (" + query + ") (" + mysql_error(mysql) + ")");
	}
	else
	if (!(mysql_res = mysql_store_result(mysql)))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Store Result failed. (" + string(mysql_error(mysql)) + ")");
	}
	else
	{
		debuglogger(DEBUG_MYSQL, LEVEL_DEBUG, info, "Mysql Query Succeeded. (" + query + ")");
	}

	return mysql_res;
}

// db_update
//
// query the database, but disregard the results and log any failure

void db_update(MYSQL *mysql, const DeviceInfo *info, const string & query)
{
	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Update Failed. (" + query + ") (" + mysql_error(mysql) + ")");
	}
	else
	{
		debuglogger(DEBUG_MYSQL, LEVEL_DEBUG, info, "MySQL Update Succeeded. (" + query + ")");
	}
}

// db_escape
//
// turn a string into one appropriate for inclusion in an SQL query

string db_escape(const string & input)
{
	char *raw_output = new char[input.length() * 2 + 1];
	// mysql_real_escape avoided due to its requirement of a mysql connection
	mysql_escape_string(raw_output, input.c_str(), input.length());
	string output = string(raw_output);
	delete [] raw_output;
	return output;
}
