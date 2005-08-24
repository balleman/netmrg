/********************************************
* NetMRG Integrator
*
* mappings.cpp
* NetMRG Gatherer Mappings Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "mappings.h"
#include "utils.h"
#include "snmp.h"
#include "db.h"

string remove_surrounding_quotes(string input)
{
	if (input[0] == '"')
		input.erase(0,1);
	if (input[input.length() - 1] == '"')
		input.erase(input.length() - 1, 1);
	return input;
}

void do_snmp_interface_recache(DeviceInfo *info, MYSQL *mysql)
{
	// clear cache for this device
	db_update(mysql, info, "DELETE FROM snmp_interface_cache WHERE dev_id=" + inttostr(info->device_id));

	// this is a hack to see if we're on a CatOS platform
	string sysdescr = snmp_get(*info, "system.sysDescr.0");

	IfMIBType mibtype = imtStandard;

	if (sysdescr.find("WS-C") != string::npos)
		mibtype = imtCatOS;

	if (sysdescr.find("Cisco Systems Catalyst 1900") != string::npos)
		mibtype = imtOldCiscoSwitch;

	list<SNMPPair> ifIndexList = snmp_walk(*info, "ifIndex");

	for (list<SNMPPair>::iterator current = ifIndexList.begin(); current != ifIndexList.end(); current++)
	{
		string ifIndex = current->value;
		string ifName  = snmp_get(*info, "ifName."  + ifIndex);

		if ((mibtype == imtStandard) &&
			(sysdescr.find("Cisco") != string::npos) &&
			(sysdescr.find("IOS") != string::npos) &&
			(ifName == "U")
		   )
		{
			mibtype = imtOldCiscoRouter;
		}
		
		string ifDescr = snmp_get(*info, "ifDescr." + ifIndex);
		// use CatOS port name in place of ifAlias
		string ifAlias;
		if (mibtype == imtCatOS)
		{
			// CatOS port names are indexed by slot and port, not by ifIndex
			string::size_type slash_pos = ifName.find("/");
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
		else if (mibtype == imtOldCiscoSwitch)
		{
			ifAlias = snmp_get(*info, ".1.3.6.1.4.1.437.1.1.3.3.1.1.3." + ifIndex);
			ifAlias = remove_surrounding_quotes(ifAlias);
			ifName  = ifDescr;	
		}
		else if (mibtype == imtOldCiscoRouter)
		{
			ifAlias = snmp_get(*info, ".1.3.6.1.4.1.9.2.2.1.1.28." + ifIndex);
			ifAlias = remove_surrounding_quotes(ifAlias);
			ifName  = ifDescr;
		}
		else
		{
			ifAlias = snmp_get(*info, "ifAlias." + ifIndex);
		}
		U_to_NULL(ifAlias);
		U_to_NULL(ifName);
		U_to_NULL(ifDescr);
		string ifType			= snmp_get(*info, "ifType."  + ifIndex);
		string ifMAC			= snmp_get(*info, "ifPhysAddress." + ifIndex);
		U_to_NULL(ifMAC);
		string ifOperStatus		= snmp_get(*info, "ifOperStatus."  + ifIndex);
		string ifAdminStatus	= snmp_get(*info, "ifAdminStatus." + ifIndex);
		string ifSpeed			= snmp_get(*info, "ifSpeed." + ifIndex);

		db_update(mysql, info, string("INSERT INTO snmp_interface_cache SET ")  +
			"dev_id = " 		+ inttostr((*info).device_id)		+ ", "  +
			"ifIndex = '"		+ ifIndex							+ "', " +
			"ifName = "			+ ifName							+ ", "  +
			"ifDescr = "		+ ifDescr							+ ", "  +
			"ifAlias = "		+ ifAlias							+ ", "  +
			"ifType = '"		+ ifType							+ "', " +
			"ifMAC = "			+ ifMAC								+ ", "  +
			"ifOperStatus = '" 	+ ifOperStatus						+ "', " +
			"ifAdminStatus = '" + ifAdminStatus						+ "', "
			"ifSpeed = '"		+ ifSpeed							+ "'");

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

	// try UCD Disk MIB
	
	list<SNMPPair> dskIndexList = snmp_walk(*info, "dskIndex");

	for (list<SNMPPair>::iterator current = dskIndexList.begin(); current != dskIndexList.end(); current++)
	{
		string dskIndex  = (*current).value;
		string dskPath   = snmp_get(*info, "dskPath."   + dskIndex);	U_to_NULL(dskPath);
		string dskDevice = snmp_get(*info, "dskDevice." + dskIndex);	U_to_NULL(dskDevice);

		db_update(mysql, info, string("INSERT INTO snmp_disk_cache SET ")  +
			"dev_id = " 		+ inttostr((*info).device_id) 	+ ", "  +
			"disk_index  = "	+ dskIndex 			+ ", "  +
			"disk_device = "	+ dskDevice			+ ", " +
			"disk_path   = "	+ dskPath);
	}
	
	// try Windows Disk MIB
	
	if (dskIndexList.empty())
	{
		string dskPath, dskIndex;
		dskIndexList = snmp_walk(*info, ".1.3.6.1.2.1.25.2.3.1.1");
		for (list<SNMPPair>::iterator current = dskIndexList.begin(); current != dskIndexList.end(); current++)
		{
			dskIndex  = (*current).value;
			dskPath   = snmp_get(*info, ".1.3.6.1.2.1.25.2.3.1.3." + dskIndex); U_to_NULL(dskPath);
			if (dskPath[1] == '\\' && dskPath[2] == '"' &&
			    dskPath[dskPath.size()-3] == '\\' && dskPath[dskPath.size()-2] == '"')
				{
					dskPath.erase(1, 2);
					dskPath.erase(dskPath.size()-3, 2);
				}
			string::size_type i = dskPath.find(" ", 0);
			if (i != string::npos)
			{
				dskPath = dskPath.substr(0, i+1) + "'";
			}
			db_update(mysql, info, string("INSERT INTO snmp_disk_cache SET ")	+
				"dev_id = "             + inttostr((*info).device_id)			+ ", "  +
				"disk_index  = "        + dskIndex								+ ", "  +
				"disk_device = "        + dskPath								+ ", "  +
				"disk_path   = "        + dskPath);
		}
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

		if (
				current->name == "ifIndex" ||
				current->name == "ifName" ||
				current->name == "ifDescr" ||
				current->name == "ifAlias" ||
				current->name == "ifIP" ||
				current->name == "ifMAC"
			)
		{
			index = current->name;
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
			string("SELECT ifIndex, ifName, ifIP, ifDescr, ifAlias, ifMAC, ifSpeed FROM snmp_interface_cache WHERE dev_id=") +
			inttostr(info->device_id) + string(" AND ") + index + "='" + db_escape(value) + "'";

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
				parse_fancy_alias(info, mysql_row[4]);
			}

			if ((mysql_row[5] != NULL) && (index != "ifMAC"))
			{
				info->parameters.push_front(ValuePair("ifMAC", mysql_row[5]));
			}

			if (mysql_row[6] != NULL)
				info->parameters.push_front(ValuePair("ifSpeed", mysql_row[6]));
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

void parse_fancy_alias(DeviceInfo *info, string alias)
{
	// see if the interface description looks parsible, and parse it.
	if (alias.find("(",0) != string::npos)
	{
		info->parameters.push_front(ValuePair("ifCktName", alias.substr(0, alias.find("(",0))));
		info->parameters.push_front(ValuePair("ifCktID", alias.substr(alias.find("(",0) + 1, alias.length() - alias.find("(",0) - 2)));
	}
	else
	{
		info->parameters.push_front(ValuePair("ifCktName", alias));
		info->parameters.push_front(ValuePair("ifCktID", "N/A"));
	}
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
		inttostr(info->device_id) + string(" AND ") + index + "='" + db_escape(value) + "'";

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

