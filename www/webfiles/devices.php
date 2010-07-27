<?php
/********************************************
* NetMRG Integrator
*
* devices.php
* Monitored Devices Editing Page
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


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["ReadWrite"]);

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "add";
}

switch ($_REQUEST["action"])
{
	case "doadd":
	case "doedit":
		doedit();
		break;
		
	case "delete":
	case "dodelete":
		dodelete();
		break;
		
	case "deletemulti" :
		if (isset($_REQUEST["dev_id"]))
		{
			foreach ($_REQUEST["dev_id"] as $key => $val)
			{
				delete_device($key, $_REQUEST["grp_id"]);
			} // end foreach group, delete
		}
		display();
		break;
	
	case "doaddtogrp":
		doaddtogrp();
		break;
		
	case "addtogrp":
		displayaddtogrp();
		break;
		
	case "add":
		displayadd();
		break;
		
	case "addnew":
	case "edit":
		displayedit();
		break;
		
	case "duplicate":
		doduplicate();
		break;
}


/***** FUNCTIONS *****/
function doedit()
{
	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit")
	{

		if (!isset($_REQUEST["disabled"])) { $_REQUEST["disabled"] = 0; }
		if (!isset($_REQUEST["snmp_version"])) { $_REQUEST["snmp_version"] = 0; }
		if (!isset($_REQUEST["no_snmp_uptime_check"])) { $_REQUEST["no_snmp_uptime_check"] = 0; }
		if (!isset($_REQUEST["unknowns_on_snmp_restart"])) { $_REQUEST["unknowns_on_snmp_restart"] = 0; }

		if ($_REQUEST["dev_id"] == 0)
		{
			$db_cmd = "INSERT INTO";
			$db_end = "";
			$just_now_disabled = false;
			$dev_type_changed = false;
		}
		else
		{
			$db_cmd = "UPDATE";
			$db_end = "WHERE id={$_REQUEST['dev_id']}";
			$q = db_query("SELECT disabled, dev_type FROM devices WHERE id={$_REQUEST['dev_id']}");
			$r = db_fetch_array($q);
			$just_now_disabled = (($r['disable'] == 0) && ($_REQUEST['disabled'] == 1));
			$dev_type_changed = ($r['dev_type'] != $_REQUEST['dev_type']);
			
		} // end if dev_id = 0 or not

		db_update("$db_cmd devices SET
			name='{$_REQUEST['dev_name']}',
			ip='{$_REQUEST['dev_ip']}',
			snmp_read_community='{$_REQUEST['snmp_read_community']}',
			snmp3_user='{$_REQUEST['snmp3_user']}',
			snmp3_seclev='{$_REQUEST['snmp3_seclev']}',
			snmp3_aprot='{$_REQUEST['snmp3_aprot']}',
			snmp3_apass='{$_REQUEST['snmp3_apass']}',
			snmp3_pprot='{$_REQUEST['snmp3_pprot']}',
			snmp3_ppass='{$_REQUEST['snmp3_ppass']}',
			dev_type='{$_REQUEST['dev_type']}',
			snmp_recache_method='{$_REQUEST['snmp_recache_method']}',
			disabled='{$_REQUEST['disabled']}',
			snmp_version='{$_REQUEST['snmp_version']}',
			snmp_port='{$_REQUEST['snmp_port']}',
			snmp_timeout='{$_REQUEST['snmp_timeout']}',
			snmp_retries='{$_REQUEST['snmp_retries']}',
			no_snmp_uptime_check='{$_REQUEST['no_snmp_uptime_check']}',
			unknowns_on_snmp_restart='{$_REQUEST['unknowns_on_snmp_restart']}' 
			$db_end");

		if ($_REQUEST["dev_id"] == 0)
		{
			db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id=" . db_insert_id());
		} // end if dev+id = 0

		if ($just_now_disabled)
		{
			db_update("UPDATE devices SET status=0 WHERE id = {$_REQUEST['dev_id']}");
			db_update("UPDATE sub_devices SET status=0 WHERE dev_id = {$_REQUEST['dev_id']}");
			$q = db_query("SELECT id FROM sub_devices WHERE dev_id = {$_REQUEST['dev_id']}");
			while ($r2 = db_fetch_array($q))
			{
				db_update("UPDATE monitors SET status=0 WHERE sub_dev_id = {$r2['id']}");
				$q1 = db_query("SELECT id FROM monitors WHERE sub_dev_id = {$r2['id']}");
				while ($r1 = db_fetch_array($q1))
				{
					db_update("UPDATE events SET last_status=0 WHERE mon_id = {$r1['id']}");
				}
			}
		}

		if ($dev_type_changed)
		{
			// the device type changed, so we need to migrate and/or clean up device properties
			
			$old_props_query = db_query("SELECT * FROM dev_props LEFT JOIN dev_prop_vals ON dev_props.id=dev_prop_vals.prop_id WHERE dev_type_id = {$r['dev_type']} AND dev_id = {$_REQUEST['dev_id']}");
			while ($old_prop_row = db_fetch_array($old_props_query))
			{
				$new_props_query = db_query("SELECT * FROM dev_props WHERE dev_type_id = {$_REQUEST['dev_type']} AND name = '{$old_prop_row['name']}'");
				if ($new_prop_row = db_fetch_array($new_props_query))
				{
					db_update("INSERT INTO dev_prop_vals SET dev_id={$_REQUEST['dev_id']}, prop_id={$new_prop_row['id']}, value='{$old_prop_row['value']}'");
				}
				
				db_update("DELETE FROM dev_prop_vals WHERE dev_id={$_REQUEST['dev_id']} AND prop_id={$old_prop_row['prop_id']}");
			}
		}

	} // done editing

	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end if we editing

function doaddtogrp()
{
	db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id={$_REQUEST['dev_id']}");
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}");
	exit();
} // end if we're adding to a group

function dodelete()
{
	delete_device($_REQUEST["dev_id"], $_REQUEST["grp_id"]);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // done deleting

function doduplicate()
{
	duplicate_device($_REQUEST['dev_id']);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // done duplicating

function displayadd()
{
	begin_page("devices.php", "Add Device");
	echo "<big><b>\n";
	echo '<a href="';
	echo "devices.php?grp_id={$_REQUEST['grp_id']}&action=addnew&tripid={$_REQUEST['tripid']}";
	echo '">Create a new device</a><br><br>'."\n";
	echo '<a href="';
	echo "devices.php?grp_id={$_REQUEST['grp_id']}&action=addtogrp&tripid={$_REQUEST['tripid']}";
	echo '">Add an existing device to this group</a>'."\n";
	echo "</b></big>\n";
	end_page();
} // end if add

function displayaddtogrp()
{
	begin_page("devices.php", "Add Device Group");
	make_edit_table("Add Existing Device to a Group");
	make_edit_select_from_table("Device:","dev_id","devices",-1);
	make_edit_hidden("action","doaddtogrp");
	make_edit_hidden("grp_id",$_REQUEST["grp_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
} // end if add to group

function displayedit()
{
	// Display editing screen
	begin_page("devices.php", "Edit Device");

	if ($_REQUEST["action"] == "addnew")
	{
		$dev_id = 0;
	}
	else
	{
		$dev_id = $_REQUEST["dev_id"];
	} // end if device id

	$dev_select = "SELECT * FROM devices WHERE id=$dev_id";
	$dev_results = db_query($dev_select);
	$dev_row = db_fetch_array($dev_results);
	$dev_name = $dev_row["name"];
	$dev_ip = $dev_row["ip"];
	if ($_REQUEST["action"] == "addnew")
	{
		$dev_row["dev_type"] = "";
		$dev_row["disabled"] = 0;
		$dev_row["snmp_version"] = 0;
		$dev_row["snmp_read_community"] = "";
		$dev_row["snmp3_user"] = "";
		$dev_row["snmp3_seclev"] = 0;
		$dev_row["snmp3_aprot"] = 0;
		$dev_row["snmp3_apass"] = "";
		$dev_row["snmp3_pprot"] = 0;
		$dev_row["snmp3_ppass"] = "";
		$dev_row["snmp_recache_method"] = 3;
		$dev_row["snmp_port"] = 161;
		$dev_row["snmp_timeout"] = 1000000;
		$dev_row["snmp_retries"] = 3;
		$dev_row["no_snmp_uptime_check"] = 0;
		$dev_row["unknowns_on_snmp_restart"] = 1;
	}

	make_edit_table("Edit Device");
	make_edit_group("General");
	make_edit_text("Name:", "dev_name", "25", "100", $dev_name);
	make_edit_text("IP or Host Name:", "dev_ip", "25", "100", $dev_ip);
	make_edit_select_from_table("Device Type:", "dev_type", "dev_types", $dev_row["dev_type"]);
	make_edit_checkbox("Disabled (do not monitor this device)", "disabled", $dev_row["disabled"]);
	make_edit_group("SNMP");
	make_edit_select_from_array("SNMP Support:", "snmp_version", $GLOBALS["SNMP_VERSIONS"], $dev_row["snmp_version"]);
	make_edit_group("SNMP v1/v2c");
	make_edit_text("Read Community:", "snmp_read_community", 50, 200, $dev_row["snmp_read_community"]);
	make_edit_group("SNMP v3");
	make_edit_text("User:", "snmp3_user", "25", "100", $dev_row["snmp3_user"]);
	make_edit_select_from_array("Security Level:", "snmp3_seclev", $GLOBALS['SNMP_SECLEVS'], $dev_row["snmp3_seclev"]);
	make_edit_select_from_array("Authentication Protocol:", "snmp3_aprot", $GLOBALS['SNMP_APROTS'], $dev_row["snmp3_aprot"]);
	make_edit_text("Authentication Password", "snmp3_apass", "25", "100", $dev_row["snmp3_apass"]);
	make_edit_select_from_array("Privacy Protocol:", "snmp3_pprot", $GLOBALS['SNMP_PPROTS'], $dev_row["snmp3_pprot"]);
	make_edit_text("Privacy Password", "snmp3_ppass", "25", "100", $dev_row['snmp3_ppass']);
	make_edit_group("Advanaced SNMP Options");
	make_edit_select_from_array("Recaching Method:", "snmp_recache_method", $GLOBALS["RECACHE_METHODS"], $dev_row["snmp_recache_method"]);
	make_edit_checkbox("Disable Uptime Check", "no_snmp_uptime_check", $dev_row["no_snmp_uptime_check"] == 1);
	make_edit_checkbox("Unknowns on Agent Restart", "unknowns_on_snmp_restart", $dev_row["unknowns_on_snmp_restart"] == 1);
	make_edit_text("UDP Port", "snmp_port", 5, 5, $dev_row["snmp_port"]);
	make_edit_text("Timeout (microseconds):", "snmp_timeout", 10, 20, $dev_row["snmp_timeout"]);
	make_edit_text("Retries:", "snmp_retries", 3, 10, $dev_row["snmp_retries"]);
	make_edit_hidden("dev_id", $dev_id);
	make_edit_hidden("action", "doedit");
	make_edit_hidden("grp_id", $_REQUEST["grp_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

} // end if edit


function display()
{
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end display();

?>

