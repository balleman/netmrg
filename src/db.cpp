/********************************************
* NetMRG Integrator
*
* db.cpp
* NetMRG Gatherer Database Library
*
* see doc/LICENSE for copyright information
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
	netmrg_mutex_lock(lkMySQL);
	mysql_init(connection);
	if (!(mysql_real_connect(connection, get_setting(setDBHost).c_str(), get_setting(setDBUser).c_str(), get_setting(setDBPass).c_str(), get_setting(setDBDB).c_str(), 0, NULL, 0)))
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
