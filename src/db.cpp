/********************************************
* NetMRG Integrator
*
* db.cpp
* NetMRG Gatherer Database Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "db.h"
#include "config.h"
#include "locks.h"
#include "utils.h"
#include "settings.h"


// db_connect
//
// make a new MySQL connection to the NetMRG database

void db_connect(MYSQL *connection)
{
	mutex_lock(lkMySQL);
	mysql_init(connection);
	if (!(mysql_real_connect(connection, get_setting(setDBHost).c_str(), get_setting(setDBUser).c_str(), get_setting(setDBPass).c_str(), get_setting(setDBDB).c_str(), 0, NULL, 0)))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, NULL, "MySQL Connection Failure.");
	}
	mutex_unlock(lkMySQL);
}

// db_query
//
// perform a MySQL query and return the results

MYSQL_RES *db_query(MYSQL *mysql, DeviceInfo *info, string query)
{
	MYSQL_RES *mysql_res;

	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Query Failed (" + query + ")");
	}

	if (!(mysql_res = mysql_store_result(mysql)))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Store Result failed.");
	}
	
	return mysql_res;
}

// db_update
//
// query the database, but disregard the results and log any failure

void db_update(MYSQL *mysql, DeviceInfo *info, string query)
{
	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, LEVEL_ERROR, info, "MySQL Query Failed (" + query + ")");
	}
}
