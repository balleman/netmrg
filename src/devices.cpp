/********************************************
* NetMRG Integrator
*
* devices.cpp
* NetMRG Gatherer Devices Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "devices.h"

#include "snmp.h"
#include "utils.h"
#include "monitors.h"
#include "mappings.h"

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
		db_update(mysql, &info, "INSERT INTO sub_dev_variables SET type = 'dynamic', sub_dev_id = "
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

	debuglogger(DEBUG_DEVICE, LEVEL_INFO, &info, info.name + " / " + info.ip + " / " + info.snmp_read_community);
	
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
