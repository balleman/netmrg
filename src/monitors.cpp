/********************************************
* NetMRG Integrator
*
* monitors.cpp
* NetMRG Gatherer Monitors Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "monitors.h"

#include "snmp.h"
#include "utils.h"
#include "rrd.h"
#include "locks.h"
#include "events.h"

string process_internal_monitor(DeviceInfo info, MYSQL *mysql)
{
	string test_result = "U";

	switch(info.test_id)
	{
		// count lines in a file
		case 1:		test_result = count_file_lines(info);
					break;
		
		// TNT "good" modems (that is, available modems - suspect modems)
		case 2:		test_result = snmp_diff(info, ".1.3.6.1.4.1.529.15.1.0", ".1.3.6.1.4.1.529.15.3.0");
					break;

		default:	debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown Internal Test (" + inttostr(info.test_id) + ")");
	}

	return test_result;
}

string process_sql_monitor(DeviceInfo info, MYSQL *mysql)
{
	MYSQL		test_mysql;
	MYSQL_RES	*mysql_res, *test_res;
	MYSQL_ROW	mysql_row, test_row;
	string		value = "U";

	string query =
		string("SELECT host, user, password, query, column_num FROM tests_sql WHERE id = ") + inttostr(info.test_id);
		mysql_res = db_query(mysql, &info, query);
		mysql_row = mysql_fetch_row(mysql_res);

	// if the sql test exists
	if (mysql_row[0] != NULL)
	{
		string test_query = expand_parameters(info, mysql_row[3]);
		debuglogger(DEBUG_GATHERER, LEVEL_DEBUG, &info, "MySQL Query Test ('" +
			string(mysql_row[0]) + "', '" + string(mysql_row[1]) + "', '" +
			string(mysql_row[2]) + "', '" + test_query + "', '" +
			string(mysql_row[4]) + "')");

		mutex_lock(lkMySQL);
#ifndef OLD_MYSQL
		mysql_init(&test_mysql);

		if (!(mysql_real_connect(&test_mysql,mysql_row[0],mysql_row[1],mysql_row[2], NULL, 0, NULL, 0)))
#else
		if (!(mysql_connect(&test_mysql,mysql_row[0],mysql_row[1],mysql_row[2])))
#endif
		{
			mutex_unlock(lkMySQL);
			debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "Test MySQL Connection Failure.");
		}
		else
		{
			mutex_unlock(lkMySQL);
			if (mysql_query(&test_mysql, test_query.c_str()))
			{
				debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "Test MySQL Query Failed (" + test_query + ")");
			}
			else
			if (!(test_res = mysql_store_result(&test_mysql)))
			{
				debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "Test MySQL Store Result failed.");
			}
			else
			{
				test_row = mysql_fetch_row(test_res);
				if (test_row != NULL)
				{
					if (test_row[strtoint(mysql_row[4])] != NULL)
					{
						value = string(test_row[strtoint(mysql_row[4])]);
					}
					else
					{
						debuglogger(DEBUG_GATHERER, LEVEL_NOTICE, &info, "Selected column is NULL.");
					}
				}
				else
				{
					debuglogger(DEBUG_GATHERER, LEVEL_NOTICE, &info, "There are no rows.");
				}
				mysql_free_result(test_res);
				mysql_close(&test_mysql);
			}
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown SQL Test (" + inttostr(info.test_id) + ").");
	}
		
	mysql_free_result(mysql_res);
	return value;
}

string process_script_monitor(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	string		value;

	string query =
		string("SELECT cmd, data_type FROM tests_script WHERE id = ") +
		inttostr(info.test_id);
	mysql_res = db_query(mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	// if the script test exists
	if (mysql_row[0] != NULL)
	{
		string command = expand_parameters(info, string(mysql_row[0]) + " " + info.test_params);

		debuglogger(DEBUG_GATHERER, LEVEL_INFO, &info, "Sending '" + command + "' to shell.");

		// if error code is desired
		if (strtoint(mysql_row[1]) == 1)
		{
			value = inttostr(system(command.c_str()));
		}
		else
		{
			FILE *p_handle;
			char temp [100] = "";

			p_handle = popen(command.c_str(), "r");
			fgets(temp, 100, p_handle);
			pclose(p_handle);

			value = string(temp);

			if (value == "")
			{
				debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "No data returned from script test.");
				value = "U";
			}
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown Script Test (" +
			inttostr(info.test_id) + ").");
		value = "U";
	}

	mysql_free_result(mysql_res);

	return value;

}

string process_snmp_monitor(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;
	string 		value;

	string query =
		string("SELECT oid FROM tests_snmp WHERE id = ") +
		inttostr(info.test_id);

	mysql_res = db_query(mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	// if the snmp test exists
	if (mysql_row[0] != NULL)
	{
		string oid = expand_parameters(info, mysql_row[0]);

		if (info.snmp_avoid == 0)
		{
			value = snmp_get(info, oid);
		}
		else
		{
			value = "U";
			debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Avoided.");
		}
	}
	else
	{
		value = "U";
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown SNMP Test (" +
		inttostr(info.test_id) + ").");
	}

	mysql_free_result(mysql_res);

	return value;
}

uint process_monitor(DeviceInfo info, MYSQL *mysql, RRDInfo rrd)
{
	debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Starting Monitor.");
	
	info.parameters.push_front(ValuePair("parameters", info.test_params));

	switch (info.test_type)
	{
		case  1:	info.curr_val = process_script_monitor(info, mysql);
					break;

		case  2:	info.curr_val = process_snmp_monitor(info, mysql);
					break;

		case  3:	info.curr_val = process_sql_monitor(info, mysql);
					break;

		case  4:	info.curr_val = process_internal_monitor(info, mysql);
					break;

		default:	{
					debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown test type (" +
						inttostr(info.test_type) + ").");
					info.curr_val = "U";
					}
	} // end switch

	debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Value: " + info.curr_val);

	if (rrd.data_type != "")
	{
		update_monitor_rrd(info, rrd);
	}

	// destroy anything non-integer; we don't want it here.
	if (info.curr_val != inttostr(strtoint(info.curr_val)))
	{
		info.curr_val = "U";
	}

	if ((info.curr_val == "U") || (info.last_val == "U"))
	{
		info.delta_val = "U";
	}
	else
	{
		info.delta_val = inttostr(strtoint(info.curr_val) - strtoint(info.last_val));
	}

	uint status = process_events(info, mysql);

	info.status = status;
	update_monitor_db(info, mysql, rrd);

	return status;
}

// update_monitor_db
//
// update the database with current values for a monitor

void update_monitor_db(DeviceInfo info, MYSQL *mysql, RRDInfo rrd)
{
	if (info.curr_val == "U")
	{
		info.curr_val = "NULL";
	}

	if (info.delta_val == "U")
	{
		info.delta_val = "NULL";
	}

	db_update(mysql, &info, "UPDATE monitors SET tuned=1, last_val=" + info.curr_val +
		", delta_val=" + info.delta_val +
		", delta_time=UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_time), "+
		"last_time=NOW(), status=" + inttostr(info.status) +
		" WHERE id=" + inttostr(info.monitor_id));
}

// expand_parameters
//
// expand parameters within a string

string expand_parameters(DeviceInfo info, string input)
{
	for (list<ValuePair>::iterator current = info.parameters.begin(); current != info.parameters.end(); current++)
	{
		input = token_replace(input, "%" + current->name + "%", current->value);
	}
	return input;
}

