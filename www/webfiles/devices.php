<?php
/********************************************
* NetMRG Integrator
*
* devices.php
* Monitored Devices Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadAll"]);

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
		check_auth($PERMIT["ReadWrite"]);
		if (isset($_REQUEST["dev_id"]))
		{
			foreach ($_REQUEST["dev_id"] as $key => $val)
			{
				delete_device($key, $_REQUEST["grp_id"]);
			} // end foreach group, delete
		}
		display();
	
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
	check_auth($PERMIT["ReadWrite"]);
	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit")
	{
		if ($_REQUEST["dev_id"] == 0)
		{
			$db_cmd = "INSERT INTO";
			$db_end = "";
		}
		else
		{
			$db_cmd = "UPDATE";
			$db_end = "WHERE id={$_REQUEST['dev_id']}";
		} // end if dev_id = 0 or not
		if (!isset($_REQUEST["disabled"])) { $_REQUEST["disabled"] = 0; }
		if (!isset($_REQUEST["snmp_version"])) { $_REQUEST["snmp_version"] = 0; }
		if (!isset($_REQUEST["no_snmp_uptime_check"])) { $_REQUEST["no_snmp_uptime_check"] = 0; }
		db_update("$db_cmd devices SET
			name='{$_REQUEST['dev_name']}',
			ip='{$_REQUEST['dev_ip']}',
			snmp_read_community='{$_REQUEST['snmp_read_community']}',
			dev_type='{$_REQUEST['dev_type']}',
			snmp_recache_method='{$_REQUEST['snmp_recache_method']}',
			disabled='{$_REQUEST['disabled']}',
			snmp_version='{$_REQUEST['snmp_version']}',
			snmp_port='{$_REQUEST['snmp_port']}',
			snmp_timeout='{$_REQUEST['snmp_timeout']}',
			snmp_retries='{$_REQUEST['snmp_retries']}',
			no_snmp_uptime_check='{$_REQUEST['no_snmp_uptime_check']}' 
			$db_end");

		if ($_REQUEST["dev_id"] == 0)
		{
			db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id=" . db_insert_id());
		} // end if dev+id = 0
	} // done editing

	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end if we editing

function doaddtogrp()
{
	check_auth($PERMIT["ReadWrite"]);
	db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id={$_REQUEST['dev_id']}");
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}");
	exit();
} // end if we're adding to a group

function dodelete()
{
	check_auth($PERMIT["ReadWrite"]);
	delete_device($_REQUEST["dev_id"], $_REQUEST["grp_id"]);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // done deleting

function doduplicate()
{
	check_auth($PERMIT["ReadWrite"]);
	duplicate_device($_REQUEST['dev_id']);
	header("Location: grpdev_list.php?parent_id={$_REQUEST['grp_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // done duplicating

function displayadd()
{
	check_auth($PERMIT["ReadWrite"]);
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

	check_auth($PERMIT["ReadWrite"]);
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
	check_auth($PERMIT["ReadWrite"]);
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
		$dev_row["snmp_recache_method"] = 3;
		$dev_row["snmp_port"] = 161;
		$dev_row["snmp_timeout"] = 1000000;
		$dev_row["snmp_retries"] = 3;
		$dev_row["no_snmp_uptime_check"] = 0;
	}

	make_edit_table("Edit Device");
	make_edit_group("General");
	make_edit_text("Name:", "dev_name", "25", "100", $dev_name);
	make_edit_text("IP or Host Name:", "dev_ip", "25", "100", $dev_ip);
	make_edit_select_from_table("Device Type:", "dev_type", "dev_types", $dev_row["dev_type"]);
	make_edit_checkbox("Disabled (do not monitor this device)", "disabled", $dev_row["disabled"]);
	make_edit_group("SNMP");
	make_edit_select_from_array("SNMP Support:", "snmp_version", $GLOBALS["SNMP_VERSIONS"], $dev_row["snmp_version"]);
	make_edit_text("SNMP Read Community:", "snmp_read_community", 50, 200, $dev_row["snmp_read_community"]);
	make_edit_select_from_array("Recaching Method:", "snmp_recache_method", $GLOBALS["RECACHE_METHODS"], $dev_row["snmp_recache_method"]);
	make_edit_group("Advanaced SNMP Options");
	make_edit_checkbox("Disable SNMP Uptime Check", "no_snmp_uptime_check", $dev_row["no_snmp_uptime_check"] == 1);
	make_edit_text("SNMP UDP Port", "snmp_port", 5, 5, $dev_row["snmp_port"]);
	make_edit_text("SNMP Timeout (microseconds):", "snmp_timeout", 10, 20, $dev_row["snmp_timeout"]);
	make_edit_text("SNMP Retries:", "snmp_retries", 3, 10, $dev_row["snmp_retries"]);
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

