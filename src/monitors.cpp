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
#include "settings.h"

#ifdef OLD_MYSQL
#define MYSQL_CONNECT(a,b,c,d,e,f,g,h) mysql_connect(a,b,c,d)
#else
#define MYSQL_CONNECT(a,b,c,d,e,f,g,h) mysql_real_connect(a,b,c,d,e,f,g,h)
#endif

string process_internal_monitor(DeviceInfo info, MYSQL *mysql)
{
	string test_result = "U", temp, temp2;
	float disk_total, disk_used;

	switch(info.test_id)
	{
		// count lines in a file
		case 1:		test_result = count_file_lines(info);
					break;
		
		// TNT "good" modems (that is, available modems - suspect modems)
		case 2:		test_result = snmp_diff(info, ".1.3.6.1.4.1.529.15.1.0", ".1.3.6.1.4.1.529.15.3.0");
					break;
					
		// UCD CPU combined load (user + system)
		case 3:		temp = snmp_get(info, ".1.3.6.1.4.1.2021.11.9.0");
					temp2 = snmp_get(info, ".1.3.6.1.4.1.2021.11.10.0");
					if ( (temp != "U") && (temp2 != "U") )
					{
						test_result = inttostr(strtoint(temp) + strtoint(temp2));
					}
					break;

		// Windows disk usage %
		case 4:
					temp = snmp_get(info, expand_parameters(info, ".1.3.6.1.2.1.25.2.3.1.5.%dskIndex%"));
					disk_total = (float) strtoul(temp.c_str(), NULL, 10);
					temp = snmp_get(info, expand_parameters(info, ".1.3.6.1.2.1.25.2.3.1.6.%dskIndex%"));
					disk_used  = (float) strtoul(temp.c_str(), NULL, 10);
					if (disk_total != 0)
						test_result = inttostr((int) (100*disk_used/disk_total));
					break;

		// UCD Swap utilization %
		case 5:
					temp = snmp_get(info, ".1.3.6.1.4.1.2021.4.3.0");
					disk_total = (float) strtoul(temp.c_str(), NULL, 10);
					temp = snmp_get(info, ".1.3.6.1.4.1.2021.4.4.0");
					disk_used  = (float) strtoul(temp.c_str(), NULL, 10);
					if (disk_total != 0)
						test_result = inttostr((int) (100 - 100*disk_used/disk_total));
					break;
		
		// read value from file
		case 6:		test_result = read_value_from_file(info);
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

	// if the sql test exists
	if (mysql_res &&
		(mysql_num_rows(mysql_res) == 1) &&
		(mysql_row = mysql_fetch_row(mysql_res)) &&
		(mysql_row[0] != NULL))
	{
		string host = expand_parameters(info, mysql_row[0]);
		string user = expand_parameters(info, mysql_row[1]);
		string password = expand_parameters(info, mysql_row[2]);
		string test_query = expand_parameters(info, mysql_row[3]);
		debuglogger(DEBUG_GATHERER, LEVEL_DEBUG, &info, "MySQL Query Test ({'" +
			host + "'}, {'" + user + "'}, {'" + password + "'}, '" + test_query + "', '" +
			string(mysql_row[4]) + "')");

		netmrg_mutex_lock(lkMySQL);
		mysql_init(&test_mysql);

		if (!(MYSQL_CONNECT(&test_mysql,host.c_str(),user.c_str(),password.c_str(), NULL, 0, NULL, 0)))
		{
			netmrg_mutex_unlock(lkMySQL);
			debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "Test MySQL Connection Failure.");
		}
		else
		{
			netmrg_mutex_unlock(lkMySQL);
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

	// if the script test exists
	if (mysql_res &&
		(mysql_num_rows(mysql_res) == 1) &&
		(mysql_row = mysql_fetch_row(mysql_res))
		&& (mysql_row[0] != NULL))
	{
		string command = expand_parameters(info, string(mysql_row[0]));
		if (command[0] != '/')
			command = get_setting(setPathLibexec) + "/" + command;

		debuglogger(DEBUG_GATHERER, LEVEL_INFO, &info, "Sending '" + command + "' to shell.");

		// if error code is desired
		if (strtoint(mysql_row[1]) == 1)
		{
			value = inttostr(system(command.c_str()));
		}
		else
		{
			FILE *p_handle;
			char temp [256] = "";

			netmrg_mutex_lock(lkPipe);
			p_handle = popen(command.c_str(), "r");
			netmrg_mutex_unlock(lkPipe);

			if (p_handle == NULL)
			{
				debuglogger(DEBUG_GATHERER, LEVEL_ERROR, &info, "popen() failed.");
				value = "U";
			}
			else
			{
				fgets(temp, 256, p_handle);

				netmrg_mutex_lock(lkPipe);
				pclose(p_handle);
				netmrg_mutex_unlock(lkPipe);

				value = string(temp);

				if (value == "")
				{
					debuglogger(DEBUG_GATHERER, LEVEL_WARNING, &info, "No data returned from script test.");
					value = "U";
				}
			}
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown Script Test (" + inttostr(info.test_id) + ").");
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
		string("SELECT oid, type, subitem FROM tests_snmp WHERE id = ") +
		inttostr(info.test_id);

	mysql_res = db_query(mysql, &info, query);
	
	// if the snmp test exists
	if (mysql_res &&
		(mysql_num_rows(mysql_res) == 1) &&
		(mysql_row = mysql_fetch_row(mysql_res)) &&
		(mysql_row[0] != NULL))
	{
		string oid = expand_parameters(info, mysql_row[0]);
		int type 	= strtoint(mysql_row[1]);
		int subitem = strtoint(mysql_row[2]);

		if (info.snmp_avoid == 0)
		{
			if (type == 0)
			{
				// plain "get"
				
				value = snmp_get(info, oid);
			}
			else if (type == 1)
			{
				// walk to the Nth item
				
				list<SNMPPair> result = snmp_walk(info, oid);
				result.reverse();  // sigh, it's upside-down
				list<SNMPPair>::iterator x = result.begin();
				for (int k = 0; k < subitem; k++)
				{
					if (x == result.end())
					{
						debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "There is no subitem in position " + inttostr(subitem) + ".");
						value = "U";
						break;
					}
					else
					{
						value = x->value;
						x++;
					}
				}
			}
			else
			{
				value = "U";
				debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown SNMP Test Type (" + inttostr(type) + ").");
			}
			
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
		debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown SNMP Test (" + inttostr(info.test_id) + ").");
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

		default:	debuglogger(DEBUG_MONITOR, LEVEL_WARNING, &info, "Unknown test type (" + inttostr(info.test_type) + ").");
					info.curr_val = "U";

	} // end switch

	debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Value: " + info.curr_val);

	if (rrd.data_type != "")
	{
		update_monitor_rrd(info, rrd);
	}

	// destroy anything non-integer; we don't want it here.
	if (stripnl(info.curr_val) != inttostr(strtoint(info.curr_val)))
	{
		debuglogger(DEBUG_MONITOR, LEVEL_INFO, &info, "Value is non-integer.");
		info.curr_val = "U";
	}

	if ((info.curr_val == "U") || (info.last_val == "U"))
	{
		info.delta_val = "U";
		info.rate_val  = "U";
	}
	else
	{
		info.delta_val = inttostr(strtoint(info.curr_val) - strtoint(info.last_val));
		if (info.delta_time != 0)
		{
			info.rate_val  = inttostr(strtoint(info.delta_val) / info.delta_time);
		}
		else
		{
			info.rate_val  = "U";
		}
	}
	
	// populate parameters
	info.parameters.push_front(ValuePair("current_value", info.curr_val));
	info.parameters.push_front(ValuePair("delta_value", info.delta_val));
	info.parameters.push_front(ValuePair("rate_value", info.rate_val));
	info.parameters.push_front(ValuePair("last_value", info.last_val));

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

