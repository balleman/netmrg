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
   Copyright 2001-2003 Brady Alleman, All Rights Reserved.

   MySQL examples from http://mysql.turbolift.com/mysql/chapter4.php3
   pthreads examples from http://www.math.arizona.edu/swig/pthreads/threads.html
   net-snmp examples from http://net-snmp.sf.net/
   Thanks to Patrick Haller (http://haller.ws) for helping to debug threading.

*/

#include "config.h"
#include <cstdio>
#include <cstdlib>
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <string>
#include <getopt.h>
#include <errno.h>
#include <list>

using namespace std;

int active_threads = 0;

// Include the NetMRG Headers
#include "types.h"
#include "utils.h"
#include "locks.h"
#include "settings.h"
#include "snmp.h"
#include "db.h"
#include "rrd.h"
#include "events.h"

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

	// this is a hack to see if we're on a CatOS platform
	string sysdescr = snmp_get(*info, "system.sysDescr.0");
	bool catos = false;
	if (sysdescr.find("WS-C") != string::npos)
		catos = true;

	list<SNMPPair> ifIndexList = snmp_walk(*info, "ifIndex");

	for (list<SNMPPair>::iterator current = ifIndexList.begin(); current != ifIndexList.end(); current++)
	{
		string ifIndex = (*current).value;
		string ifName  = snmp_get(*info, "ifName."  + ifIndex);
		string ifDescr = snmp_get(*info, "ifDescr." + ifIndex);			U_to_NULL(&ifDescr);
		// use CatOS port name in place of ifAlias
		string ifAlias;
		if (catos)
		{
			// CatOS port names are indexed by slot and port, not by ifIndex
			int slash_pos = ifName.find("/");
			int slot = strtoint(ifName.substr(0, slash_pos));
			int port = strtoint(ifName.substr(slash_pos +  1, ifName.length() - 1));
			debuglogger(DEBUG_SNMP, LEVEL_DEBUG, info, "ifname='" + ifName + "', slash_pos=" + inttostr(slash_pos) + ", slot=" + inttostr(slot) + ", port=" + inttostr(port));
			if ( (slot != 0) && (port != 0) )
			{
				ifAlias = snmp_get(*info, ".1.3.6.1.4.1.9.5.1.4.1.1.4." + inttostr(slot) + "." + inttostr(port));
				ifAlias.erase(0, 1);
				ifAlias.erase(ifAlias.length() - 1, 1);
			}
		}
		else
		{
			ifAlias = snmp_get(*info, "ifAlias." + ifIndex);
		}
		U_to_NULL(&ifAlias);
		U_to_NULL(&ifName);
		string ifType  = snmp_get(*info, "ifType."  + ifIndex);
		string ifMAC   = snmp_get(*info, "ifPhysAddress." + ifIndex);           U_to_NULL(&ifMAC);
		string ifOperStatus  = snmp_get(*info, "ifOperStatus."  + ifIndex);
		string ifAdminStatus = snmp_get(*info, "ifAdminStatus." + ifIndex);

		db_update(mysql, info, string("INSERT INTO snmp_interface_cache SET ")  +
			"dev_id = " 		+ inttostr((*info).device_id) 	+ ", "  +
			"ifIndex = '"		+ ifIndex 			+ "', " +
			"ifName = "			+ ifName			+ ", "  +
			"ifDescr = "		+ ifDescr			+ ", "  +
			"ifAlias = "		+ ifAlias			+ ", "  +
			"ifType = '"		+ ifType			+ "', " +
			"ifMAC = "			+ ifMAC				+ ", "  +
			"ifOperStatus = '" 	+ ifOperStatus		+ "', " +
			"ifAdminStatus = '" + ifAdminStatus		+ "'");

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

int setup_interface_parameters(DeviceInfo *info, MYSQL *mysql)
{

	// This function examines the parameters for the subdevice and determines if any
	// are to be used as SNMP index values.  If so, it adds parameters with all available
	// information from the snmp_cache, so that things like %ifIndex% and %ifName% in monitors
	// will get expanded into the correct values when the monitors are processed.

	string          index   = "";
	string          value   = "";

	MYSQL_RES       *mysql_res;
	MYSQL_ROW       mysql_row;

	int		retval	= 0;

	for (list<ValuePair>::iterator current = info->parameters.begin(); current != info->parameters.end(); current++)
	{
		value = current->value;

		if (current->name == "ifIndex")
		{
			index = "ifIndex";
                	break;
		}
		else
		if (current->name == "ifName")
		{
			index = "ifName";
			break;
		}
		else
		if (current->name == "ifDescr")
		{
			index = "ifDescr";
			break;
		}
		else
		if (current->name == "ifAlias")
		{
			index = "ifAlias";
			break;
		}
		else
		if (current->name == "ifIP")
		{
			index = "ifIP";
			break;
		}
		else
		if (current->name == "ifMAC")
		{
			index = "ifMAC";
			break;
		}

	} // end for each parameter

	if (index == "")
	{
		debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, info, "Interface subdevice has no interface parameters.");
		retval = -1;
	}
	else
	{
		string query =
			string("SELECT ifIndex, ifName, ifIP, ifDescr, ifAlias, ifMAC FROM snmp_interface_cache WHERE dev_id=") +
			inttostr(info->device_id) + string(" AND ") + index + "=\"" + value + "\"";

		mysql_res = db_query(mysql, info, query);

		if (mysql_num_rows(mysql_res) > 0)
		{
			mysql_row = mysql_fetch_row(mysql_res);

			if ((mysql_row[0] != NULL) && (index != "ifIndex"))
			{
				info->parameters.push_front(ValuePair("ifIndex", mysql_row[0]));
			}

			if ((mysql_row[1] != NULL) && (index != "ifName"))
			{
				info->parameters.push_front(ValuePair("ifName", mysql_row[1]));
			}

			if ((mysql_row[2] != NULL) && (index != "ifIP"))
			{
				info->parameters.push_front(ValuePair("ifIP", mysql_row[2]));
			}

			if ((mysql_row[3] != NULL) && (index != "ifDescr"))
			{
				info->parameters.push_front(ValuePair("ifDescr", mysql_row[3]));
			}

			if ((mysql_row[4] != NULL) && (index != "ifAlias"))
			{
				info->parameters.push_front(ValuePair("ifAlias", mysql_row[4]));
			}

			if ((mysql_row[5] != NULL) && (index != "ifMAC"))
			{
				info->parameters.push_front(ValuePair("ifMAC", mysql_row[5]));
			}
		}
		else
		{
			debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, info, "Interface index not found.");
			retval = -2;
		}
	 	mysql_free_result(mysql_res);
	}
	return retval;
}

int setup_disk_parameters(DeviceInfo *info, MYSQL *mysql)
{
	// just like setup_interface_parameters, but for disks instead

	string		index   = "";
	string		value   = "";

	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;

	int		retval	= 0;

	for (list<ValuePair>::iterator current = info->parameters.begin(); current != info->parameters.end(); current++)
	{
		value = current->value;

		if (current->name == "dskIndex")
		{
			index = "disk_index";
			break;
		}
		else
		if (current->name == "dskPath")
		{
			index = "disk_path";
			break;
		}
		else
		if (current->name == "dskDevice")
		{
			index = "disk_device";
			break;
		}

	} // end for each parameter

	if (index == "")
	{
		debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, info, "Disk subdevice has no disk parameters.");
		retval = -1;
	}
	else
	{
		string query =
		string("SELECT disk_index, disk_path, disk_device FROM snmp_disk_cache WHERE dev_id=") +
		inttostr(info->device_id) + string(" AND ") + index + "=\"" + value + "\"";

		mysql_res = db_query(mysql, info, query);

		if (mysql_num_rows(mysql_res) > 0)
		{
			mysql_row = mysql_fetch_row(mysql_res);

			if ((mysql_row[0] != NULL) && (index != "disk_index"))
			{
				info->parameters.push_front(ValuePair("dskIndex", mysql_row[0]));
			}

			if ((mysql_row[1] != NULL) && (index != "disk_path"))
			{
				info->parameters.push_front(ValuePair("dskPath", mysql_row[1]));
			}

			if ((mysql_row[2] != NULL) && (index != "disk_device"))
			{
				info->parameters.push_front(ValuePair("dskDevice", mysql_row[2]));
			}
		}
		else
		{
			debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, info, "Disk index not found.");
			retval = -2;
		}
	 	mysql_free_result(mysql_res);
	}
	return retval;
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
		mysql_init(&test_mysql);

		if (!(mysql_real_connect(&test_mysql,mysql_row[0],mysql_row[1],mysql_row[2], NULL, 0, NULL, 0)))
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

uint process_sub_device(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	uint		status = 0;
	int			subdev_status = 0;

	debuglogger(DEBUG_SUBDEVICE, LEVEL_INFO, &info, "Starting Subdevice.");

	// create an array containing the parameters for the subdevice

	string query =
		string("SELECT name, value FROM sub_dev_variables WHERE type = 'static' AND sub_dev_id = ") +
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

		case 2:			subdev_status = setup_interface_parameters(&info, mysql);
						break; // interface

		case 3:			subdev_status = setup_disk_parameters(&info, mysql);
						break; // disk

		default:		debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, &info, "Unknown subdevice type (" +
						inttostr(info.subdevice_type) + ")");
						subdev_status = -3;

	}  // end subdevice type switch
	
	// delete the old dynamic entries from the cache
	db_update(mysql, &info, "DELETE FROM sub_dev_variables WHERE type = 'dynamic' AND sub_dev_id = "
		+ inttostr(info.subdevice_id));
		
	// insert the new dynamic entries
	for (list<ValuePair>::iterator current = info.parameters.begin(); current != info.parameters.end(); current++)
	{
		db_update(mysql, &info, "INSERT INTO sub_dev_variables SET type = 'dynamic', sub_dev_id = "
			+ inttostr(info.subdevice_id) + ", name = '" + current->name + "', value = '" + current->value
			+ "'");
	}
	
	if (subdev_status < 0)
	{
		debuglogger(DEBUG_SUBDEVICE, LEVEL_WARNING, &info, "Subdevice aborted due to previous errors.");
		return 0;
	}

	// query the monitors for this subdevice

	query =
		string("SELECT ")						+
		string("monitors.data_type, 	")		+ // 0
		string("data_types.rrd_type, 	")		+ // 1
		string("monitors.min_val,	")			+ // 2
		string("monitors.max_val,	")			+ // 3
		string("monitors.tuned,		")			+ // 4
		string("monitors.test_type,	")			+ // 5
		string("monitors.test_id,	") 			+ // 6
		string("monitors.test_params,	")		+ // 7
		string("monitors.last_val,	")			+ // 8
		string("monitors.id		")				+ // 9
		string("FROM monitors ")				+
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

	debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Starting device thread.");
	db_connect(&mysql);
	debuglogger(DEBUG_DEVICE, LEVEL_INFO, &info, "MySQL connection established.");

	string query = 	string("SELECT ") 		+
			string("name, ")				+ // 0
			string("ip, ")					+ // 1
			string("snmp_enabled, ")		+ // 2
			string("snmp_read_community, ")	+ // 3
			string("snmp_recache, ")		+ // 4
			string("snmp_uptime, ")			+ // 5
			string("snmp_ifnumber, ")		+ // 6
			string("snmp_check_ifnumber ")	+ // 7
			string("FROM devices ")			+
			string("WHERE id=") + inttostr(dev_id);

	mysql_res = db_query(&mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	info.name					= mysql_row[0];
	info.ip						= mysql_row[1];
	info.snmp_read_community	= mysql_row[3];
	
	// setup device-wide parameters
	info.parameters.push_front(ValuePair("dev_name", mysql_row[0]));
	info.parameters.push_front(ValuePair("ip", mysql_row[1]));
	info.parameters.push_front(ValuePair("snmp_read_community", mysql_row[3]));
	
	// get SNMP-level info, if SNMP is used.
	if (strtoint(mysql_row[2]) == 1)
	{
		// get uptime
		info.snmp_uptime = get_snmp_uptime(info);
		debuglogger(DEBUG_SNMP, LEVEL_INFO, &info, "SNMP Uptime is " + inttostr(info.snmp_uptime));

		// store new uptime
		db_update(&mysql, &info, "UPDATE devices SET snmp_uptime=" + inttostr(info.snmp_uptime) +
				" WHERE id=" + inttostr(dev_id));

		if (info.snmp_uptime == 0)
		{
			// device is snmp-dead
			info.snmp_avoid = 1;
			debuglogger(DEBUG_DEVICE, LEVEL_WARNING, &info, "Device is SNMP-dead.  Avoiding SNMP tests.");
		}
		else
		{
			if (strtoint(mysql_row[5]) == 0)
			{
				// device came back from the dead
				info.snmp_recache = 1;
				debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Device has returned from SNMP-death.");
			}

			if (info.snmp_uptime < strtoint(mysql_row[5]))
			{
				// uptime went backwards
				info.snmp_recache = 1;
				debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info, "SNMP Agent Restart.");
			}


			if (strtoint(mysql_row[7]) == 1)
			{
				// we care about ifNumber

				info.snmp_ifnumber =  strtoint(snmp_get(info, string("interfaces.ifNumber.0")));

				debuglogger(DEBUG_SNMP, LEVEL_INFO, &info,
					"Number of Interfaces is " + inttostr(info.snmp_ifnumber));

				if (info.snmp_ifnumber != strtoint(mysql_row[6]))
				{
					// ifNumber changed
					info.snmp_recache = 1;
					db_update(&mysql, &info, "UPDATE devices SET snmp_ifnumber = " +
						inttostr(info.snmp_ifnumber) + string(" WHERE id = ") +
						inttostr(dev_id));
					debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info,
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
			do_snmp_disk_recache(&info, &mysql);
		}

	} // end snmp-enabled

	mysql_free_result(mysql_res);

	// process sub-devices
	status = process_sub_devices(info, &mysql);

	db_update(&mysql, &info, "UPDATE devices SET status=" + inttostr(status) + " WHERE id=" + inttostr(dev_id));

	mysql_close(&mysql);

	debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Ending device thread.");


} // end process_device


// child - the thread spawned to process each device
void *child(void * arg)
{

	int device_id = *(int *) arg;

	//mysql_thread_init();

	process_device(device_id);

	mutex_lock(lkActiveThreads);
	active_threads--;
	mutex_unlock(lkActiveThreads);
	debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "Thread Ended.");

	//mysql_thread_end();

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
	
	setlinebuf(stdout);

	debuglogger(DEBUG_GLOBAL, LEVEL_NOTICE, NULL, "NetMRG starting.");
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Start time is " + inttostr(start_time));

	if (file_exists("/var/www/netmrg/dat/lockfile"))
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, NULL, "Critical:  Lockfile exists.  Is another NetMRG running?");
		exit(254);
	}

	// create lockfile
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Creating Lockfile.");
	lockfile = fopen("/var/www/netmrg/dat/lockfile","w+");
	fprintf(lockfile, "%ld", (long int) start_time);
	fclose(lockfile);

	// SNMP library initialization
	snmp_init();

	// RRDTOOL command pipe setup
	rrd_init();

	// open mysql connection for initial queries
	db_connect(&mysql);

	// request list of devices to process
	mysql_res = db_query(&mysql, NULL, "SELECT id FROM devices WHERE disabled=0 ORDER BY id");

	num_rows	= mysql_num_rows(mysql_res);
	threads		= new pthread_t[num_rows];
	ids			= new int[num_rows];

	// reading settings isn't necessarily efficient.  storing them locally.
	int			THREAD_COUNT = get_setting_int(setThreadCount);
	long int	THREAD_SLEEP = get_setting_int(setThreadSleep);
	
	int dev_counter = 0;

	// deploy more threads as needed
	int last_active_threads = 0;
	while (dev_counter < num_rows)
	{
		if (mutex_trylock(lkActiveThreads) != EBUSY)
		{
			if (last_active_threads != active_threads)
			{
				debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[ACTIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
				last_active_threads = active_threads;
			}
			while ((active_threads < THREAD_COUNT) && (dev_counter < num_rows))
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
			debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "[ACTIVE] Sorry, can't lock thread counter.");
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
				debuglogger(DEBUG_THREAD, LEVEL_INFO, NULL, "[PASSIVE] Last: " +
				inttostr(last_active_threads) + ", Now: " +
				inttostr(active_threads));
				last_active_threads = active_threads;
			}
			if (active_threads == 0) canexit = 1;
			mutex_unlock(lkActiveThreads);
		}
		else
		{
			debuglogger(DEBUG_THREAD, LEVEL_NOTICE, NULL, "[PASSIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}

	// free active devices results
	mysql_free_result(mysql_res);

	// generate change of status report
	/*
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
	*/

	delete [] threads;
	delete [] ids;

	// clean up mysql
	mysql_close(&mysql);
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Closed MySQL connection.");

	// clean up RRDTOOL command pipe
	rrd_cleanup();
	
	// clean up SNMP
	snmp_cleanup();

	// determine runtime and store it
	long int run_time = time( NULL ) - start_time;
	debuglogger(DEBUG_GLOBAL, LEVEL_INFO, NULL, "Runtime: " + inttostr(run_time));
	lockfile = fopen("/var/www/netmrg/dat/runtime","w+");
	fprintf(lockfile, "%ld", run_time);
	fclose(lockfile);

	// remove lock file
	unlink("/var/www/netmrg/dat/lockfile");
}

void show_version()
{
	printf("\nNetMRG Data Gatherer\n");
	printf("Version %s\n\n", NETMRG_VERSION);
}

void show_usage()
{
	printf("\nNetMRG Data Gatherer\n\n");
	printf("-v          Display Version\n");
	printf("-h          Show usage (you are here)\n");
	printf("-q          Quiet; display no debug messages.\n");
	printf("-i <devid>  Recache the interfaces of device <devid>\n");
	printf("-d <devid>  Recache the disks of device <devid>\n");
	printf("-c <cm>     Use debug component mask <cm>\n");
	printf("-l <lm>     Use debug level mask <lm>\n");
	printf("\n");
}

void external_snmp_recache(int device_id, int type)
{
	MYSQL 		mysql;
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	DeviceInfo	info;

	db_connect(&mysql);
	info.device_id = device_id;

	mysql_res = db_query(&mysql, &info, "SELECT ip, snmp_read_community, snmp_enabled FROM devices WHERE id=" + inttostr(device_id));
	mysql_row = mysql_fetch_row(mysql_res);

	if (strtoint(mysql_row[2]) != 1)
	{
		debuglogger(DEBUG_GLOBAL, LEVEL_CRITICAL, &info, "Can't recache a device without SNMP.");
		exit(1);
	}

	info.ip 					= mysql_row[0];
	info.snmp_read_community	= mysql_row[1];

	mysql_free_result(mysql_res);

	snmp_init();
	switch (type)
	{
		case 1: do_snmp_interface_recache(&info, &mysql);	break;
		case 2: do_snmp_disk_recache(&info, &mysql); 		break;
	}
	snmp_cleanup();

	mysql_close(&mysql);
}

// main - the body of the program
int main(int argc, char **argv)
{
	int option_char;
	load_default_settings();

	while ((option_char = getopt(argc, argv, "hvqi:d:c:l:")) != EOF)
		switch (option_char)
		{
			case 'h': 	show_usage();
						exit(0); 
						break;
			case 'v': 	show_version();
						exit(0);
						break;
			case 'i': 	external_snmp_recache(strtoint(optarg), 1);
						exit(0);
						break;
			case 'd': 	external_snmp_recache(strtoint(optarg), 2);
						exit(0);
						break;
			case 'c':	set_debug_components(strtoint(optarg));
						break;
			case 'l':	set_debug_level(strtoint(optarg));
						break;
			case 'q': 	set_debug_level(0);
						break;			
			
		}
	run_netmrg();
}
