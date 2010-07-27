/********************************************
* NetMRG Integrator
*
* devices.cpp
* NetMRG Gatherer Devices Library
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

#include "devices.h"

#include "snmp.h"
#include "utils.h"
#include "monitors.h"
#include "mappings.h"
#include "settings.h"

void do_properties_recache(DeviceInfo info, MYSQL *mysql)
{
	string query = string("SELECT id, test_type, test_id, test_params, name FROM dev_props WHERE dev_type_id = " + inttostr(info.device_type));

	MYSQL_RES *mysql_res = db_query(mysql, &info, query);
	MYSQL_ROW mysql_row;

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		string value;
		info.property_id	= strtoint(mysql_row[0]);
		info.test_type		= strtoint(mysql_row[1]);
		info.test_id		= strtoint(mysql_row[2]);
		info.test_params	= mysql_row[3];
		info.parameters.push_front(ValuePair("parameters", info.test_params));

		switch (info.test_type)
		{
			case  1:	value = process_script_monitor(info, mysql);
						value = strstripnl(value);
						break;
	
			case  2:	value = process_snmp_monitor(info, mysql);
						value = remove_surrounding_quotes(value);	
						break;
	
			case  3:	value = process_sql_monitor(info, mysql);
						break;
	
			case  4:	value = process_internal_monitor(info, mysql);
						break;
	
			default:	debuglogger(DEBUG_PROPERTY, LEVEL_WARNING, &info, "Unknown test type (" + inttostr(info.test_type) + ").");
						value = "U";
		} // end switch

		if (value != "U")
		{
			debuglogger(DEBUG_PROPERTY, LEVEL_INFO, &info, string("Device Property '") + mysql_row[4] + "' set to {'" + value + "'}");
			db_update(mysql, &info, "REPLACE INTO dev_prop_vals SET dev_id=" + inttostr(info.device_id) + ", prop_id=" + mysql_row[0] + ", value='" + db_escape(value) + "'");
		}
		else
		{
			debuglogger(DEBUG_PROPERTY, LEVEL_INFO, &info, "Device Property test failed, skipping update.");
		}
	}
	
	mysql_free_result(mysql_res);

} // end do_properties_recache

uint process_sub_device(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	uint		status = 0;
	int			subdev_status = 0;

	debuglogger(DEBUG_SUBDEVICE, LEVEL_INFO, &info, "Starting Subdevice.");

	// setup subdevice specific parameters
	info.parameters.push_front(ValuePair("subdev_name", info.subdevice_name));

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
		db_update(mysql, &info, "INSERT DELAYED INTO sub_dev_variables SET type = 'dynamic', sub_dev_id = "
			+ inttostr(info.subdevice_id) + ", name = '" + current->name + "', value = '" + db_escape(current->value)
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
		string("monitors.data_type, ")			+ // 0
		string("data_types.rrd_type, ")			+ // 1
		string("monitors.min_val, ")			+ // 2
		string("monitors.max_val, ")			+ // 3
		string("monitors.tuned, ")				+ // 4
		string("monitors.test_type, ")			+ // 5
		string("monitors.test_id, ") 			+ // 6
		string("monitors.test_params, ")		+ // 7
		string("monitors.last_val, ")			+ // 8
		string("monitors.id, ")					+ // 9
		string("NOW() - monitors.last_time ")	+ // 10
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

		if (mysql_row[10] != NULL)
		{
			info.delta_time = strtoint(mysql_row[10]);
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

	string query = string("SELECT id, type, name FROM sub_devices WHERE dev_id=") + inttostr(info.device_id);

	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		// setup subdevice variables
		info.subdevice_id 	= strtoint(mysql_row[0]);
		info.subdevice_type	= strtoint(mysql_row[1]);
		info.subdevice_name = mysql_row[2];

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
	time_t		start_time = time( NULL );
	DeviceInfo info;

	info.device_id = dev_id;

	// connect to db, get info for this device

	debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Starting device thread at " + timetostr(start_time) + ".");
	if (!db_connect(&mysql)) return;
	debuglogger(DEBUG_DEVICE, LEVEL_INFO, &info, "MySQL connection established.");
	info.mysql = (void *) &mysql;

	string query = 	string("SELECT ") 			+
			string("name, ")					+ // 0
			string("ip, ")						+ // 1
			string("snmp_version, ")			+ // 2
			string("snmp_read_community, ")		+ // 3
			string("snmp_recache_method, ")		+ // 4
			string("snmp_uptime, ")				+ // 5
			string("snmp_ifnumber, ")			+ // 6
			string("snmp_port, ")				+ // 7
			string("snmp_timeout, ")			+ // 8
			string("snmp_retries, ")			+ // 9
			string("no_snmp_uptime_check, ")	+ // 10
			string("dev_type, ")				+ // 11
			string("unknowns_on_snmp_restart,") + // 12
			string("snmp3_user, ")				+ // 13
			string("snmp3_seclev, ")			+ // 14
			string("snmp3_aprot, ")				+ // 15
			string("snmp3_apass, ")				+ // 16
			string("snmp3_pprot, ")				+ // 17
			string("snmp3_ppass ")				+ // 18
			string("FROM devices ")				+
			string("WHERE id=") + inttostr(dev_id);

	mysql_res = db_query(&mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	info.name					= mysql_row[0];
	info.ip						= mysql_row[1];
	info.snmp_version			= strtoint(mysql_row[2]);
	info.device_type			= strtoint(mysql_row[11]);

	debuglogger(DEBUG_DEVICE, LEVEL_INFO, &info, info.name + " / {" + info.ip + "}");

	// setup device-wide parameters
	info.parameters.push_front(ValuePair("dev_name", mysql_row[0]));
	info.parameters.push_front(ValuePair("ip", mysql_row[1]));

	// get SNMP-level info, if SNMP is used.
	if (info.snmp_version > 0)
	{
		// set SNMP parameters
		info.snmp_read_community	= mysql_row[3];
		info.snmp3_user				= mysql_row[13];
		info.snmp3_seclev			= strtoint(mysql_row[14]);
		info.snmp3_aprot			= strtoint(mysql_row[15]);
		info.snmp3_apass			= mysql_row[16];
		info.snmp3_pprot			= strtoint(mysql_row[17]);
		info.snmp3_ppass			= mysql_row[18];
		info.snmp_port				= strtoint(mysql_row[7]);
		info.snmp_timeout			= strtoint(mysql_row[8]);
		info.snmp_retries			= strtoint(mysql_row[9]);
		int  snmp_recache_method	= strtoint(mysql_row[4]);
		int  check_snmp_uptime		= strtoint(mysql_row[10]) ? 0 : 1;
		
		// add SNMP parameters to list
		info.parameters.push_front(ValuePair("snmp_read_community", mysql_row[3]));

		// init device snmp session
		snmp_session_init(info);

		if (check_snmp_uptime)
		{
			// get uptime
			info.snmp_uptime = get_snmp_uptime(info);
			debuglogger(DEBUG_SNMP, LEVEL_INFO, &info, "SNMP Uptime is " + 
			format_time_elapsed(info.snmp_uptime / 100) + 
			+ " (" + inttostr(info.snmp_uptime) + " centiseconds)");

			// store new uptime
			db_update(&mysql, &info, "UPDATE devices SET snmp_uptime=" + inttostr(info.snmp_uptime) +
					" WHERE id=" + inttostr(dev_id));
		}
		else
		{
			debuglogger(DEBUG_DEVICE, LEVEL_WARNING, &info, "Not checking SNMP uptime as per configuration.");
			debuglogger(DEBUG_DEVICE, LEVEL_WARNING, &info, "This option should only be used as a last resort.");
		}

		if ( check_snmp_uptime && (info.snmp_uptime == 0) )
		{
			// device is snmp-dead
			info.snmp_avoid = 1;
			debuglogger(DEBUG_DEVICE, LEVEL_WARNING, &info, "Device is SNMP-dead.  Avoiding SNMP tests.");
		}
		else
		{
			if (snmp_recache_method >= 1)
			{
				// we care about SNMP agent restarts

				if (check_snmp_uptime)
				{
					if (strtoint(mysql_row[5]) == 0)
					{
						// device came back from the dead
						info.snmp_recache = 1;
						debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Device has returned from SNMP-death.");

						// write unknowns to counters, if configured to
						if (strtoint(mysql_row[12]) == 1)
						{
							info.counter_unknowns = 1;
							debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Using unknowns for non-gauge values.");
						}
					}

					if (info.snmp_uptime < strtoint(mysql_row[5]))
					{
						// uptime went backwards
						info.snmp_recache = 1;
						debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info, "SNMP Agent Restart.");

						// write unknowns to counters, if configured to
						if (strtoint(mysql_row[12]) == 1)
						{
							info.counter_unknowns = 1;
							debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Using unknowns for non-gauge values.");
						}

					}
				}
			}

			if (snmp_recache_method >= 2)
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
						"Number of interfaces changed from " + string(mysql_row[6]) + " to " + inttostr(info.snmp_ifnumber));
				}
				else
				if (snmp_recache_method >= 3)
				{
					// we care about interface cache matching ifNumber
					MYSQL_RES 	*cache_mysql_res;
					MYSQL_ROW 	cache_mysql_row;

					cache_mysql_res = db_query(&mysql, &info, string("SELECT count(*) FROM snmp_interface_cache WHERE dev_id = ") + inttostr(info.device_id));
					cache_mysql_row = mysql_fetch_row(cache_mysql_res);
					unsigned int interface_cache_count = strtoint(cache_mysql_row[0]);
					mysql_free_result(cache_mysql_res);

					if (info.snmp_ifnumber != interface_cache_count)
					{
						// ifNumber doesn't match the interface cache in the database
						info.snmp_recache = 1;
						debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info,
							"Number of cached interfaces (" + inttostr(interface_cache_count) + ") " +
							"doesn't match reported number of interfaces.");
					}
				}
			}

			if (strtoint(mysql_row[4]) == 4)
			{
				// we recache this one every time.
				info.snmp_recache = 1;
			}
		} // end snmp_uptime > 0

		if (info.snmp_recache)
		{
			// we need to recache.
			debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info, "Performing SNMP Recache.");
			do_snmp_interface_recache(&info, &mysql);
			do_snmp_disk_recache(&info, &mysql);
			do_properties_recache(info, &mysql);
		}
	} // end snmp-enabled
	else
	{
		debuglogger(DEBUG_SNMP, LEVEL_INFO, &info, "This device is configured with no SNMP support; avoiding SNMP tests.");
		info.snmp_avoid = 1;
	}

	mysql_free_result(mysql_res);

	// process sub-devices
	status = process_sub_devices(info, &mysql);

	db_update(&mysql, &info, "UPDATE devices SET status=" + inttostr(status) + 
		", snmp_avoided=" + inttostr(info.snmp_avoid) + " WHERE id=" + inttostr(dev_id));

	if (info.snmp_sess_p)
	{
		snmp_session_cleanup(info);
	}

	// trim event log for device
	debuglogger(DEBUG_DEVICE, LEVEL_INFO, &info, "Trimming device event log.");
	mysql_res = db_query(&mysql, &info, "SELECT id FROM log WHERE dev_id=" + inttostr(info.device_id) + " ORDER BY id");
	int count = mysql_num_rows(mysql_res);
	for (int i = 0; i < count - get_setting_int(setMaxDeviceLogEntries); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		db_update(&mysql, &info, string("DELETE FROM log WHERE id=") + mysql_row[0]);
	}
	mysql_free_result(mysql_res);

	mysql_close(&mysql);
	info.mysql = NULL;

	debuglogger(DEBUG_DEVICE, LEVEL_NOTICE, &info, "Ending device thread at " + timetostr(time( NULL )) + ", after a duration of " + inttostr(time(NULL) - start_time) + " seconds.");

} // end process_device
