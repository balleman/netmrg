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


// db_connect
//
// make a new MySQL connection to the NetMRG database

void db_connect(MYSQL *connection)
{
	mutex_lock(lkMySQL);

	if (!(mysql_connect(connection, MYSQL_HOST, MYSQL_USER, MYSQL_PASS)))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Connection Failure.");
		pthread_exit(NULL);
	}

	mutex_unlock(lkMySQL);

	if (mysql_select_db(connection, MYSQL_DB))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Database Selection Failure.");
		pthread_exit(NULL);
	}
}

// db_query
//
// perform a MySQL query and return the results

MYSQL_RES *db_query(MYSQL *mysql, DeviceInfo *info, string query)
{
	MYSQL_RES *mysql_res;

	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, info, "MySQL Query Failed (" + query + ")");
		pthread_exit(NULL);
	}

	if (!(mysql_res = mysql_store_result(mysql)))
	{
		debuglogger(DEBUG_MYSQL, info, "MySQL Store Result failed.");
		pthread_exit(NULL);
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
		debuglogger(DEBUG_MYSQL, info, "MySQL Query Failed (" + query + ")");
	}
}
