/********************************************
* NetMRG Integrator
*
* netmrg.cpp
* NetMRG Gatherer
*
* see doc/LICENSE for copyright information
********************************************/

/*

   NetMRG Monitoring Procedure
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

   MySQL examples from http://mysql.turbolift.com/mysql/chapter4.php3
   pthreads examples from http://www.math.arizona.edu/swig/pthreads/threads.html
   net-snmp examples from http://net-snmp.sf.net/
   Thanks to Patrick Haller (http://haller.ws) for helping to debug threading.

*/

#include <cstdio>
#include <cstdlib>
#include <unistd.h>
#include <mysql.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <string>
#include <ucd-snmp/ucd-snmp-config.h>
#include <ucd-snmp/ucd-snmp-includes.h>
#include <ucd-snmp/system.h>
#include <getopt.h>
#include <errno.h>
#include <ucd-snmp/mib.h>
#include <list>

using namespace std;

#include "config.h"

// RRDTOOL Pipe
FILE *rrdtool_pipe;

int active_threads = 0;

// Include the NetMRG Libraries
#include "types.h"
#include "utils.h"
#include "locks.h"
#include <netmrg-snmp.cc>
#include <netmrg-db.cc>
#include <netmrg-misc.cc>




// rrd_cmd
//
// issues a command to RRDTOOL via the RRDTOOL pipe, and logs it

void rrd_cmd(DeviceInfo info, string cmd)
{
	debuglogger(DEBUG_RRD, &info, "RRD: '" + cmd + "'");
	cmd = " " + cmd + "\n";

	mutex_lock(lkRRD);
	fprintf(rrdtool_pipe, cmd.c_str());
	mutex_unlock(lkRRD);
}

// get_rrd_file
//
// returns the name of the rrd file in use for a given monitor

string get_rrd_file(string mon_id)
{
	string filename = string(NETMRG_ROOT) + "rrd/mon_" + mon_id + ".rrd";
	return filename;
}

// create_rrd
//
// creates a new RRD file

void create_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command;

	command = "create " + get_rrd_file(inttostr(info.monitor_id)) +
			" DS:mon_" + inttostr(info.monitor_id) + ":" + rrd.data_type +
			":600:" + rrd.min_val + ":" + rrd.max_val + " " +
			"RRA:AVERAGE:0.5:1:600 " +
			"RRA:AVERAGE:0.5:6:700 " 	+
			"RRA:AVERAGE:0.5:24:775 " 	+
			"RRA:AVERAGE:0.5:288:797 " 	+
			"RRA:LAST:0.5:1:600 " 		+
			"RRA:LAST:0.5:6:700 " 		+
			"RRA:LAST:0.5:24:775 " 		+
			"RRA:LAST:0.5:288:797 " 	+
			"RRA:MAX:0.5:1:600 " 		+
			"RRA:MAX:0.5:6:700 "		+
			"RRA:MAX:0.5:24:775 "		+
			"RRA:MAX:0.5:288:797";
	rrd_cmd(info, command);
}

// tune_rrd
//
// modifies the maximum and minimum values acceptable for a given RRD

void tune_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "tune " + get_rrd_file(inttostr(info.monitor_id)) + " -a mon_" +
	       			inttostr(info.monitor_id) + ":" + rrd.max_val +
				" -i mon_" + inttostr(info.monitor_id) + ":" + rrd.min_val;
	rrd_cmd(info, command);
}

// update_rrd
//
// update an RRD with a current value

void update_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "update " + get_rrd_file(inttostr(info.monitor_id)) + " N:" + stripnl(info.curr_val);
	rrd_cmd(info, command);
}

// update_monitor_rrd
//
// for a monitor:
//	1. Create a RRD file if one doesn't exist.
//	2. Tune the RRD file if necessary.
//	3. Update the RRD file with current data.

void update_monitor_rrd(DeviceInfo info, RRDInfo rrd)
{
	if (!(file_exists(get_rrd_file(inttostr(info.monitor_id)))))
	{
		create_rrd(info, rrd);
	}

	if (rrd.tuned == 0)
	{
		tune_rrd(info, rrd);
	}

	update_rrd(info, rrd);
}

// db_connect
//
// make a new MySQL connection to the NetMRG database

MYSQL db_connect(MYSQL connection)
{
	mutex_lock(lkMySQL);

	if (!(mysql_connect(&connection, MYSQL_HOST, MYSQL_USER, MYSQL_PASS)))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Connection Failure.");
		pthread_exit(NULL);
	}
	
	mutex_unlock(lkMySQL);

	if (mysql_select_db(&connection, MYSQL_DB))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Database Selection Failure.");
		pthread_exit(NULL);
	}

	return connection;
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

void do_snmp_interface_recache(DeviceInfo *info, MYSQL *mysql)
{
	// clear cache for this device
        db_update(mysql, info, "DELETE FROM snmp_interface_cache WHERE dev_id=" + inttostr((*info).device_id));

	list<SNMPPair> ifIndexList = snmp_walk(*info, "ifIndex");

	for (list<SNMPPair>::iterator current = ifIndexList.begin(); current != ifIndexList.end(); current++)
	{
		string ifIndex = (*current).value;
		string ifName  = snmp_get(*info, "ifName."  + ifIndex);			U_to_NULL(&ifName);
		string ifDescr = snmp_get(*info, "ifDescr." + ifIndex);			U_to_NULL(&ifDescr);
		string ifAlias = snmp_get(*info, "ifAlias." + ifIndex);			U_to_NULL(&ifAlias);
		string ifType  = snmp_get(*info, "ifType."  + ifIndex);
		string ifMAC   = snmp_get(*info, "ifPhysAddress." + ifIndex);           U_to_NULL(&ifMAC);
		string ifOperStatus  = snmp_get(*info, "ifOperStatus."  + ifIndex);
		string ifAdminStatus = snmp_get(*info, "ifAdminStatus." + ifIndex);

		db_update(mysql, info, string("INSERT INTO snmp_interface_cache SET ")  +
			"dev_id = " 		+ inttostr((*info).device_id) 	+ ", "  +
			"ifIndex = '"		+ ifIndex 			+ "', " +
			"ifName = "		+ ifName			+ ", "  +
			"ifDescr = "		+ ifDescr			+ ", "  +
			"ifAlias = "		+ ifAlias			+ ", "  +
			"ifType = '"		+ ifType			+ "', " +
			"ifMAC = "		+ ifMAC				+ ", "  +
			"ifOperStatus = '" 	+ ifOperStatus			+ "', " +
			"ifAdminStatus = '" 	+ ifAdminStatus			+ "'");

	}

	list<SNMPPair> ifIPList = snmp_walk(*info, "ipAdEntIfIndex");
	ifIPList = snmp_trim_rootoid(ifIPList, ".1.3.6.1.2.1.4.20.1.2.");

        for (list<SNMPPair>::iterator current = ifIPList.begin(); current != ifIPList.end(); current++)
	{
	 	string ip 	= (*current).oid;
		string ifIndex	= (*current).value;

		db_update(mysql, info, string("UPDATE snmp_interface_cache SET ifIP = '") +
			ip + "' WHERE dev_id=" + inttostr((*info).device_id) +
			" AND ifIndex=" + ifIndex);
	}
}

void do_snmp_disk_recache(DeviceInfo *info, MYSQL *mysql)
{
	// clear cache for this device
        db_update(mysql, info, "DELETE FROM snmp_disk_cache WHERE dev_id=" + inttostr((*info).device_id));

	list<SNMPPair> ifIndexList = snmp_walk(*info, "dskIndex");

	for (list<SNMPPair>::iterator current = ifIndexList.begin(); current != ifIndexList.end(); current++)
	{
		string dskIndex  = (*current).value;
		string dskPath   = snmp_get(*info, "dskPath."   + dskIndex);	U_to_NULL(&dskPath);
		string dskDevice = snmp_get(*info, "dskDevice." + dskIndex);	U_to_NULL(&dskDevice);

		db_update(mysql, info, string("INSERT INTO snmp_disk_cache SET ")  +
			"dev_id = " 		+ inttostr((*info).device_id) 	+ ", "  +
			"disk_index  = "	+ dskIndex 			+ ", "  +
			"disk_device = "	+ dskDevice			+ ", " +
			"disk_path   = "	+ dskPath);
	}

}


void process_responses(DeviceInfo info, MYSQL *mysql)
{
}

uint process_condition(DeviceInfo info, long long int compare_value, int value_type, int condition)
{
	long long int actual_value;

	switch (value_type)
	{
		case 0:	if (info.curr_val == "U")
				return 0;
			actual_value = strtoint(info.curr_val);
			break;

		case 1: if (info.delta_val == "U")
				return 0;
			actual_value = strtoint(info.delta_val);
			break;

		case 3: break;		// rate of change: not yet implemented
	}

	switch (condition)
	{
		case 0:	if (actual_value < compare_value)
				return 1;
			break;

		case 1: if (actual_value == compare_value)
				return 1;
			break;

		case 2: if (actual_value > compare_value)
				return 1;
			break;

		case 3: if (actual_value <= compare_value)
				return 1;
			break;

		case 4: if (actual_value != compare_value)
				return 1;
			break;

		case 5: if (actual_value >= compare_value)
				return 1;
			break;
	}

	return 0;
}

uint process_event(DeviceInfo info, MYSQL *mysql, int trigger_type, int last_status, int situation)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	uint		status = 0;

	string query = "SELECT value, value_type, condition, logic_condition FROM conditions WHERE event_id=" + inttostr(info.event_id) + " ORDER BY id";
	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		if (i == 0)
		{
			status = process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
		}
		else
		{
			switch (strtoint(mysql_row[3]))
			{
				case 0:	status = status && process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
					break;
				case 1:	status = status || process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
					break;
			}
		}
	}

	mysql_free_result(mysql_res);

	if (status == 0)
	{
		debuglogger(DEBUG_EVENT, &info, "Not Triggered.");
		db_update(mysql, &info, "UPDATE events SET last_status=0 WHERE id=" + inttostr(info.event_id));
		return 0;
	}
	else
	{
		debuglogger(DEBUG_EVENT, &info, "Triggered.");

		if ((uint) last_status != status)
		{
			db_update(mysql, &info, "UPDATE events SET last_triggered=NOW(), last_status=1 WHERE id=" + inttostr(info.event_id));
		}

		process_responses(info, mysql);

		return 1;
	}
}

uint worstof(uint a, uint b)
{
	if (a > b)
	{
		return a;
	}
	else
	{
		return b;
	}
}

uint process_events(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;
	uint		status = 0;

	string query = "SELECT id, trigger_type, last_status, situation FROM events WHERE mon_id=" + inttostr(info.monitor_id) + " AND trigger_type = 1";
	mysql_res = db_query(mysql, &info, query);

        for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		info.event_id = strtoint(mysql_row[0]);
		if (process_event(info, mysql, strtoint(mysql_row[1]), strtoint(mysql_row[2]), strtoint(mysql_row[3])))
		{
			status = worstof(status, strtoint(mysql_row[3]));
		}
	}

	mysql_free_result(mysql_res);

	return status;
}

void setup_interface_parameters(DeviceInfo *info, MYSQL *mysql)
{

        // This function examines the parameters for the subdevice and determines if any
	// are to be used as SNMP index values.  If so, it adds parameters with all available
	// information from the snmp_cache, so that things like %ifIndex% and %ifName% in monitors
	// will get expanded into the correct values when the monitors are processed.

	string          index   = "";
	string          value   = "";

	MYSQL_RES       *mysql_res;
	MYSQL_ROW       mysql_row;

	for (list<ValuePair>::iterator current = (*info).parameters.begin(); current != (*info).parameters.end(); current++)
	{
		value = (*current).value;

		if ((*current).name == "ifIndex")
		{
			index = "ifIndex";
                	break;
		}
		else
		if ((*current).name == "ifName")
		{
			index = "ifName";
			break;
		}
		else
		if ((*current).name == "ifDescr")
		{
			index = "ifDescr";
			break;
		}
		else
		if ((*current).name == "ifAlias")
		{
			index = "ifAlias";
			break;
		}
		else
		if ((*current).name == "ifIP")
		{
			index = "ifIP";
			break;
		}
		else
		if ((*current).name == "ifMAC")
		{
			index = "ifMAC";
			break;
		}

	} // end for each parameter

	if (index == "")
	{
		debuglogger(DEBUG_SUBDEVICE, info, "Interface subdevice has no interface parameters.");
	}
	else
	{
                string query =
		string("SELECT ifIndex, ifName, ifIP, ifDescr, ifAlias, ifMAC FROM snmp_interface_cache WHERE dev_id=") +
		inttostr((*info).device_id) + string(" AND ") + index + "=\"" + value + "\"";

                mysql_res = db_query(mysql, info, query);

		if (mysql_num_rows(mysql_res) > 0)
		{
		        mysql_row = mysql_fetch_row(mysql_res);

		        if ((mysql_row[0] != NULL) && (index != "ifIndex"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifIndex", mysql_row[0]));
		        }

		        if ((mysql_row[1] != NULL) && (index != "ifName"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifName", mysql_row[1]));
		        }

		        if ((mysql_row[2] != NULL) && (index != "ifIP"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifIP", mysql_row[2]));
		        }

		        if ((mysql_row[3] != NULL) && (index != "ifDescr"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifDescr", mysql_row[3]));
		        }

		        if ((mysql_row[4] != NULL) && (index != "ifAlias"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifAlias", mysql_row[4]));
		        }
		}
		else
		{
		        debuglogger(DEBUG_SUBDEVICE, info, "Interface index not found.");
		}

	 	mysql_free_result(mysql_res);

	}

}

void setup_disk_parameters(DeviceInfo *info, MYSQL *mysql)
{
	// just like setup_interface_parameters, but for disks instead

	string          index   = "";
	string          value   = "";

	MYSQL_RES       *mysql_res;
	MYSQL_ROW       mysql_row;

	for (list<ValuePair>::iterator current = (*info).parameters.begin(); current != (*info).parameters.end(); current++)
	{
		value = (*current).value;

		if ((*current).name == "dskIndex")
		{
			index = "disk_index";
                	break;
		}
		else
		if ((*current).name == "dskPath")
		{
			index = "disk_path";
			break;
		}
		else
		if ((*current).name == "dskDevice")
		{
			index = "disk_device";
			break;
		}

	} // end for each parameter

	if (index == "")
	{
		debuglogger(DEBUG_SUBDEVICE, info, "Disk subdevice has no disk parameters.");
	}
	else
	{
                string query =
		string("SELECT disk_index, disk_path, disk_device FROM snmp_disk_cache WHERE dev_id=") +
		inttostr((*info).device_id) + string(" AND ") + index + "=\"" + value + "\"";

		mysql_res = db_query(mysql, info, query);

		if (mysql_num_rows(mysql_res) > 0)
		{
		        mysql_row = mysql_fetch_row(mysql_res);

		        if ((mysql_row[0] != NULL) && (index != "disk_index"))
		        {
        		        (*info).parameters.push_front(ValuePair("dskIndex", mysql_row[0]));
		        }

		        if ((mysql_row[1] != NULL) && (index != "disk_path"))
		        {
        		        (*info).parameters.push_front(ValuePair("dskPath", mysql_row[1]));
		        }

		        if ((mysql_row[2] != NULL) && (index != "disk_device"))
		        {
        		        (*info).parameters.push_front(ValuePair("dskDevice", mysql_row[2]));
		        }

		}
		else
		{
		        debuglogger(DEBUG_SUBDEVICE, info, "Disk index not found.");
		}

	 	mysql_free_result(mysql_res);

	}

}

// expand_parameters
//
// expand parameters within a string

string expand_parameters(DeviceInfo info, string input)
{
	for (list<ValuePair>::iterator current = info.parameters.begin(); current != info.parameters.end(); current++)
	{
		input = token_replace(input, "%" + (*current).name + "%", (*current).value);
	}

	return input;
}

string process_internal_monitor(DeviceInfo info, MYSQL *mysql)
{
	string test_result = "U";

	switch(info.test_id)
	{
		case 1:		test_result = count_file_lines(info);
				break;

		default:	debuglogger(DEBUG_MONITOR, &info, "Unknown Internal Test (" + inttostr(info.test_id) + ")");
	}

	return test_result;
}

string process_sql_monitor(DeviceInfo info, MYSQL *mysql)
{
        MYSQL           test_mysql;
        MYSQL_RES       *mysql_res, *test_res;
        MYSQL_ROW       mysql_row, test_row;
        string          value = "U";

        string query =
                string("SELECT host, user, password, query, column_num FROM tests_sql WHERE id = ") +
                inttostr(info.test_id);

        mysql_res = db_query(mysql, &info, query);
        mysql_row = mysql_fetch_row(mysql_res);

        // if the sql test exists
        if (mysql_row[0] != NULL)
        {

		string test_query = expand_parameters(info, mysql_row[3]);

                debuglogger(DEBUG_GATHERER, &info, "MySQL Query Test ('" +
                        string(mysql_row[0]) + "', '" + string(mysql_row[1]) + "', '" +
                        string(mysql_row[2]) + "', '" + test_query + "', '" +
                        string(mysql_row[4]) + "')");

                mutex_lock(lkMySQL);

	        if (!(mysql_connect(&test_mysql,mysql_row[0],mysql_row[1],mysql_row[2])))
	        {
        		mutex_unlock(lkMySQL);
                        debuglogger(DEBUG_GATHERER, &info, "Test MySQL Connection Failure.");
        	}
                else
                {
                        mutex_unlock(lkMySQL);

                        if (mysql_query(&test_mysql, test_query.c_str()))
	                {
		                debuglogger(DEBUG_GATHERER, &info, "Test MySQL Query Failed (" + test_query + ")");
			}
                        else
                	if (!(test_res = mysql_store_result(&test_mysql)))
	                {
		                debuglogger(DEBUG_GATHERER, &info, "Test MySQL Store Result failed.");
                	}
                        else
                        {
                                test_row = mysql_fetch_row(test_res);
                                //debuglogger(DEBUG_GATHERER, &info, "Res: " + string(test_row[0]));
                                value = string(test_row[strtoint(mysql_row[4])]);
                                mysql_free_result(test_res);
                                mysql_close(&test_mysql);
                        }
                }
         }
         else
         {
                debuglogger(DEBUG_MONITOR, &info, "Unknown SQL Test (" +
		        inttostr(info.test_id) + ").");
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

		debuglogger(DEBUG_GATHERER, &info, "Sending '" + command + "' to shell.");

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
				debuglogger(DEBUG_GATHERER, &info, "No data returned from script test.");
				value = "U";
			}
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, &info, "Unknown Script Test (" +
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
			debuglogger(DEBUG_MONITOR, &info, "Avoided.");
		}
	}
	else
	{
		value = "U";
		debuglogger(DEBUG_MONITOR, &info, "Unknown SNMP Test (" +
			inttostr(info.test_id) + ").");
	}

	mysql_free_result(mysql_res);

	return value;
}


uint process_monitor(DeviceInfo info, MYSQL *mysql, RRDInfo rrd)
{
	debuglogger(DEBUG_MONITOR, &info, "Starting Monitor.");

	switch (info.test_type)
	{
		case  1:	info.curr_val = process_script_monitor(info, mysql);
				break;

		case  2:	info.curr_val = process_snmp_monitor(info, mysql);
				break;

                case  3:        info.curr_val = process_sql_monitor(info, mysql);
                                break;

		case  4:	info.curr_val = process_internal_monitor(info, mysql);
				break;

		default:	{
					debuglogger(DEBUG_MONITOR, &info, "Unknown test type (" +
						inttostr(info.test_type) + ").");
					info.curr_val = "U";
				}
	} // end switch

	debuglogger(DEBUG_MONITOR, &info, "Value: " + info.curr_val);

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

uint process_sub_device(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	uint		status = 0;

	debuglogger(DEBUG_SUBDEVICE, &info, "Starting Subdevice.");


	// create an array containing the parameters for the subdevice

	string query =
	        string("SELECT name, value FROM sub_dev_variables WHERE sub_dev_id = ") +
		inttostr(info.subdevice_id);

        mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
	        mysql_row = mysql_fetch_row(mysql_res);

		info.parameters.push_front(ValuePair(mysql_row[0], mysql_row[1]));
	}

	mysql_free_result(mysql_res);

	// depending on the subdevice type, assign values to more parameters
	switch (info.subdevice_type)
	{
		case 1:			break; // group

		case 2:			setup_interface_parameters(&info, mysql);
					break; // interface

		case 3:			setup_disk_parameters(&info, mysql);
					break; // disk

		default:                debuglogger(DEBUG_SUBDEVICE, &info, "Unknown subdevice type (" +
					inttostr(info.subdevice_type) + ")");

	}  // end subdevice type switch

	// query the monitors for this subdevice

	query =
		string("SELECT ") 				+
		string("monitors.data_type, 	") 		+ // 0
		string("data_types.rrd_type, 	")		+ // 1
		string("monitors.min_val,	")		+ // 2
		string("monitors.max_val,	")		+ // 3
		string("monitors.tuned,		")		+ // 4
		string("monitors.test_type,	")		+ // 5
		string("monitors.test_id,	") 		+ // 6
		string("monitors.test_params,	")		+ // 7
		string("monitors.last_val,	")		+ // 8
		string("monitors.id		")		+ // 9
		string("FROM monitors ")			+
		string("LEFT JOIN data_types ON monitors.data_type=data_types.id ") +
		string("WHERE sub_dev_id = ") + inttostr(info.subdevice_id);


	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		info.monitor_id		= strtoint(mysql_row[9]);
		info.test_type  	= strtoint(mysql_row[5]);
		info.test_id		= strtoint(mysql_row[6]);
		info.test_params	= mysql_row[7];

		if (mysql_row[8] != NULL)
		{
			info.last_val = mysql_row[8];
		}

		RRDInfo rrd;

		// if we're using RRD
		if (strtoint(mysql_row[0]) > 0)
		{
			if (mysql_row[1] != NULL)
			{
				rrd.data_type   = mysql_row[1];
			}
			if (mysql_row[2] != NULL)
			{
				rrd.min_val	= mysql_row[2];
			}
			if (mysql_row[3] != NULL)
			{
				rrd.max_val	= mysql_row[3];
			}

			rrd.tuned		= strtoint(mysql_row[4]);
		} // end using rrd

		// process each monitor
		status = worstof(status, process_monitor(info, mysql, rrd));

	} // end for each monitor

        mysql_free_result(mysql_res);

	db_update(mysql, &info, "UPDATE sub_devices SET status=" + inttostr(status) + " WHERE id=" + inttostr(info.subdevice_id));

	return status;

} // end process subdevice


uint process_sub_devices(DeviceInfo info, MYSQL *mysql)
{

	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
        uint		status = 0;

	string query = string("SELECT id, type FROM sub_devices WHERE dev_id=") + inttostr(info.device_id);

	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		info.subdevice_id 	= strtoint(mysql_row[0]);
		info.subdevice_type	= strtoint(mysql_row[1]);
		status = worstof(status, process_sub_device(info, mysql));
	}

	mysql_free_result(mysql_res);

	return status;
}

void process_device(int dev_id)
{
	MYSQL 		mysql;
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;
	uint		status = 0;

	DeviceInfo info;

	info.device_id = dev_id;

	// connect to db, get info for this device

	debuglogger(DEBUG_DEVICE, &info, "Starting device thread.");
	mysql = db_connect(mysql);
	debuglogger(DEBUG_DEVICE, &info, "MySQL connection established.");

	string query = 	string("SELECT ") 		+
			string("name, ")  		+	// 0
			string("ip, ")			+ 	// 1
			string("snmp_enabled, ")	+	// 2
			string("snmp_read_community, ")	+	// 3
			string("snmp_recache, ")	+	// 4
			string("snmp_uptime, ")		+	// 5
			string("snmp_ifnumber, ")	+	// 6
			string("snmp_check_ifnumber ")	+	// 7
			string("FROM mon_devices ")	+
			string("WHERE id=") + inttostr(dev_id);

	mysql_res = db_query(&mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	info.name 			= mysql_row[0];
	info.ip				= mysql_row[1];
	info.snmp_read_community 	= mysql_row[3];

	// get SNMP-level info, if SNMP is used.

	if (strtoint(mysql_row[2]) == 1)
	{
		// get uptime
		info.snmp_uptime = get_snmp_uptime(info);
		debuglogger(DEBUG_SNMP, &info, "SNMP Uptime is " + inttostr(info.snmp_uptime));

		// store new uptime
		db_update(&mysql, &info, "UPDATE mon_devices SET snmp_uptime=" + inttostr(info.snmp_uptime) +
				" WHERE id=" + inttostr(dev_id));

		if (info.snmp_uptime == 0)
		{
			// device is snmp-dead
			info.snmp_avoid = 1;
			debuglogger(DEBUG_DEVICE, &info, "Device is SNMP-dead.  Avoiding SNMP tests.");
		}
		else
		{
			if (strtoint(mysql_row[5]) == 0)
			{
				// device came back from the dead
				info.snmp_recache = 1;
				debuglogger(DEBUG_DEVICE, &info, "Device has returned from SNMP-death.");
			}

			if (info.snmp_uptime < strtoint(mysql_row[5]))
			{
				// uptime went backwards
				info.snmp_recache = 1;
				debuglogger(DEBUG_SNMP, &info, "SNMP Agent Restart.");
			}


			if (strtoint(mysql_row[7]) == 1)
			{
				// we care about ifNumber

				info.snmp_ifnumber =  strtoint(snmp_get(info, string("interfaces.ifNumber.0")));

				debuglogger(DEBUG_SNMP, &info,
					"Number of Interfaces is " + inttostr(info.snmp_ifnumber));

				if (info.snmp_ifnumber != strtoint(mysql_row[6]))
				{
					// ifNumber changed
					info.snmp_recache = 1;
					db_update(&mysql, &info, "UPDATE mon_devices SET snmp_ifnumber = " +
						inttostr(info.snmp_ifnumber) + string(" WHERE id = ") +
						inttostr(dev_id));
					debuglogger(DEBUG_SNMP, &info,
						"Number of interfaces changed from " + string(mysql_row[6]));
				}

			}

			if (strtoint(mysql_row[4]) == 1)
			{
				// we recache this one every time.
				info.snmp_recache = 1;
			}

		} // end snmp_uptime > 0

		if (info.snmp_recache)
		{
			// we need to recache.
			do_snmp_interface_recache(&info, &mysql);
		}

	} // end snmp-enabled

	mysql_free_result(mysql_res);

	// process sub-devices
	status = process_sub_devices(info, &mysql);

	db_update(&mysql, &info, "UPDATE mon_devices SET status=" + inttostr(status) + " WHERE id=" + inttostr(dev_id));

	mysql_close(&mysql);

	debuglogger(DEBUG_DEVICE, &info, "Ending device thread.");


} // end process_device


// child - the thread spawned to process each device
void *child(void * arg)
{

	int device_id = *(int *) arg;

	process_device(device_id);

	mutex_lock(lkActiveThreads);
	active_threads--;
	mutex_unlock(lkActiveThreads);

	debuglogger(DEBUG_THREAD, NULL, "Thread Ended.");

	pthread_exit(0);

} // end child

// set things up, and spawn the threads for data gathering
void run_netmrg()
{

	MYSQL			mysql;
	MYSQL_RES		*mysql_res;
	MYSQL_ROW		mysql_row;
	time_t			start_time;
	FILE			*lockfile;
	long int		num_rows	= 0;
	pthread_t*		threads		= NULL;
	int*			ids		= NULL;
	string			temp_string;

	start_time = time( NULL );
	debuglogger(DEBUG_GLOBAL, NULL, "NetMRG starting.");
	debuglogger(DEBUG_GLOBAL, NULL, "Start time is " + inttostr(start_time));

	if (file_exists("/var/www/netmrg/dat/lockfile"))
       	{
		printf("ERROR:  My lockfile exists!  Is another netmrg running?\n  If not, remove the lockfile and try again\n");
		exit(254);
	}

	// create lockfile
	debuglogger(DEBUG_GLOBAL, NULL, "Creating Lockfile.");
	lockfile = fopen("/var/www/netmrg/dat/lockfile","w+");
	fprintf(lockfile, "%ld", (long int) start_time);
	fclose(lockfile);

	// SNMP library initialization
	snmp_init();

	// RRDTOOL command pipe setup
	debuglogger(DEBUG_GLOBAL + DEBUG_RRD, NULL, "Initializing RRDTOOL pipe.");
	string rrdtool = RRDTOOL;
	if (!(get_debug_level() && DEBUG_RRD))
		rrdtool = rrdtool + " >/dev/null";
	rrdtool_pipe = popen(rrdtool.c_str(), "w");
	// sets buffering to one line
	setlinebuf(rrdtool_pipe);


	// open mysql connection for initial queries

	mysql = db_connect(mysql);

	// request list of devices to process

	mysql_res = db_query(&mysql, NULL, "SELECT id FROM mon_devices WHERE disabled=0 ORDER BY id");

	num_rows 	= mysql_num_rows(mysql_res);
        threads 	= new pthread_t[num_rows];
	ids             = new int[num_rows];

	int dev_counter = 0;

	// deploy more threads as needed
	int last_active_threads = 0;
	while (dev_counter < num_rows)
	{
		if (mutex_trylock(lkActiveThreads) != EBUSY)
		{
			if (last_active_threads != active_threads)
			{
				debuglogger(DEBUG_THREAD, NULL, "[ACTIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
				last_active_threads = active_threads;
			}
			while ((active_threads < NTHREADS) && (dev_counter < num_rows))
			{
				mysql_row = mysql_fetch_row(mysql_res);
				int dev_id = strtoint(string(mysql_row[0]));
				ids[dev_counter] = dev_id;
				pthread_create(&threads[dev_counter], NULL, child, &ids[dev_counter]);
				pthread_detach(threads[dev_counter]);
				dev_counter++;
				active_threads++;
			}
			
			mutex_unlock(lkActiveThreads);
		}
		else
		{
			debuglogger(DEBUG_THREAD, NULL, "[ACTIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}

	// wait until all threads exit
	int canexit = 0;
	while (canexit == 0)
	{
		if (mutex_trylock(lkActiveThreads) != EBUSY)
		{
	                if (last_active_threads != active_threads)
        	        {
                	        debuglogger(DEBUG_THREAD, NULL, "[PASSIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
	                        last_active_threads = active_threads;
        	        }

			if (active_threads == 0) canexit = 1;

			mutex_unlock(lkActiveThreads);
		}
		else
		{
			debuglogger(DEBUG_THREAD, NULL, "[PASSIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}


	// free active devices results
	mysql_free_result(mysql_res);

	// generate change of status report
	mysql_res = db_query(&mysql, NULL,
			string("SELECT date, dev_name, situation, event_text, since_last_change ") +
			string("FROM event_log WHERE date >= ") +
			inttostr(start_time) + string(" ORDER BY situation"));

	num_rows = mysql_num_rows(mysql_res);

	if (num_rows > 0)
	{
		printf("ATTENTION: Creating Status Report.\n");
		lockfile = fopen("/var/www/netmrg/dat/status_report","w+");
		for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
		{
			mysql_row = mysql_fetch_row(mysql_res);
			fprintf(lockfile,
				"DATE/TIME: %s\nDevice: %s\nSituation: %s\nEvent: %s\nTime Since Last Change: %s\n\n",
				mysql_row[0], mysql_row[1], mysql_row[2], mysql_row[3], mysql_row[4]);
		}
		fclose(lockfile);
		system(DISTRIB_CMD);
	}

	// free report results
	mysql_free_result(mysql_res);

	delete [] threads;
	delete [] ids;

	// clean up mysql
	mysql_close(&mysql);
	debuglogger(DEBUG_GLOBAL, NULL, "Closed MySQL connection.");

	// clean up RRDTOOL command pipe
	pclose(rrdtool_pipe);
	debuglogger(DEBUG_GLOBAL, NULL, "Closed RRDTOOL pipe.");

	// clean up SNMP
        snmp_cleanup();

	// determine runtime and store it
	long int run_time = time( NULL ) - start_time;
	debuglogger(DEBUG_GLOBAL, NULL, "Runtime: " + inttostr(run_time));
	lockfile = fopen("/var/www/netmrg/dat/runtime","w+");
	fprintf(lockfile, "%ld", run_time);
	fclose(lockfile);

	// remove lock file
	unlink("/var/www/netmrg/dat/lockfile");

}

void show_version()
{
	printf("\nNetMRG Data Gatherer\n");
	printf("Version 0.79.3a\n\n");
}

void show_usage()
{
	printf("\nNetMRG Data Gatherer\n\n");
	printf("-v          Display Version\n");
	printf("-h          Show usage (you are here)\n");
	printf("-q          Quiet; display no debug messages\n");
	printf("-i <devid>  Recache the interfaces of device <devid>\n");
	printf("\n");
}

void external_snmp_recache(int device_id, int type)
{
	MYSQL 		mysql;
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
        DeviceInfo	info;

	mysql = db_connect(mysql);
	info.device_id = device_id;

	mysql_res = db_query(&mysql, &info, "SELECT ip, snmp_read_community, snmp_enabled FROM mon_devices WHERE id=" + inttostr(device_id));
        mysql_row = mysql_fetch_row(mysql_res);

	if (strtoint(mysql_row[2]) != 1)
	{
		debuglogger(DEBUG_GLOBAL, &info, "Can't recache a device without SNMP.");
		exit(1);
	}

	info.ip 			= mysql_row[0];
	info.snmp_read_community	= mysql_row[1];

	mysql_free_result(mysql_res);

	snmp_init();
	switch (type)
	{
		case 1: do_snmp_interface_recache(&info, &mysql); break;
		case 2: do_snmp_disk_recache(&info, &mysql); break;
	}
	snmp_cleanup();

	mysql_close(&mysql);

}

// main - the body of the program
int main(int argc, char **argv)
{
	int option_char;

	while ((option_char = getopt (argc, argv, "hvqi:d:")) != EOF)
		switch (option_char)
		{
			case 'h': show_usage(); exit(0); break;
			case 'v': show_version(); exit(0); break;
			case 'q': set_debug_level(0); break;
			case 'i': external_snmp_recache(strtoint(optarg), 1); exit(0); break;
			case 'd': external_snmp_recache(strtoint(optarg), 2); exit(0); break;
		}

	run_netmrg();
}
