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
check_auth(1);

if ((!isset($_REQUEST["action"])) || ($_REQUEST["action"] == "doedit") || ($_REQUEST["action"] == "dodelete") || ($_REQUEST["action"] == "doadd") || ($_REQUEST["action"] =="doaddtogrp"))
{

	if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "doedit" || $_REQUEST["action"] == "doadd"))
	{
		check_auth(2);
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
			if (!isset($_REQUEST["snmp_recache"])) { $_REQUEST["snmp_recache"] = 0; }
			if (!isset($_REQUEST["disabled"])) { $_REQUEST["disabled"] = 0; }
			if (!isset($_REQUEST["snmp_check_ifnumber"])) { $_REQUEST["snmp_check_ifnumber"] = 0; }
	        if (!isset($_REQUEST["snmp_enabled"])) { $_REQUEST["snmp_enabled"] = 0; }
			$_REQUEST['dev_name'] = db_escape_string($_REQUEST['dev_name']);
			$_REQUEST['dev_ip'] = db_escape_string($_REQUEST['dev_ip']);
			$_REQUEST['snmp_read_community'] = db_escape_string($_REQUEST['snmp_read_community']);
			db_update("$db_cmd devices SET 
				name='{$_REQUEST['dev_name']}',
				ip='{$_REQUEST['dev_ip']}',
				snmp_read_community='{$_REQUEST['snmp_read_community']}', 
				dev_type='{$_REQUEST['dev_type']}', 
				snmp_recache='{$_REQUEST['snmp_recache']}', 
				disabled='{$_REQUEST['disabled']}', 
				snmp_check_ifnumber='{$_REQUEST['snmp_check_ifnumber']}',
				snmp_enabled='{$_REQUEST['snmp_enabled']}' 
				$db_end");

			if ($_REQUEST["dev_id"] == 0)
			{
				db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id=" . db_insert_id());
			} // end if dev+id = 0
		} // done editing
		
		header("Location: devices.php?grp_id={$_REQUEST['grp_id']}");
		exit();
	} // end if we editing

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doaddtogrp")
	{
		check_auth(2);
		db_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id={$_REQUEST['dev_id']}");
		header("Location: devices.php?grp_id={$_REQUEST['grp_id']}");
		exit();
	} // end if we're adding to a group

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "dodelete")
	{
		check_auth(2);
		delete_device($_REQUEST["dev_id"]);
		header("Location: devices.php?grp_id={$_REQUEST['grp_id']}");
		exit();
	} // done deleting

	// Display a list
	$title = "";
	$addlink = "";
	if (isset($_REQUEST["grp_id"]))
	{
		$group_results = db_query("SELECT * FROM groups WHERE id={$_REQUEST['grp_id']}");
		$group_array = db_fetch_array($group_results);

		$title = "Monitored Devices in Group '" . $group_array["name"] . "'";

		$addlink = "{$_SERVER['PHP_SELF']}?action=add&grp_id={$_REQUEST['grp_id']}";

	}
	else
	{
		$title = "Monitored Devices";

	} // end if we have a group id
	begin_page("devices.php", "Devices");
	DrawGroupNavHistory("group", $_REQUEST["grp_id"]);
	js_confirm_dialog("del", "Are you sure you want to delete device ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&grp_id={$_REQUEST['grp_id']}&dev_id=");
	make_display_table($title, $addlink,
		array("text" => "Name"),
		array("text" => "SNMP Options")
	);

	if (!isset($orderby)) { $orderby = "name"; };

	if (!(isset($_REQUEST["grp_id"])))
	{
		$dev_results = db_query("
			SELECT devices.name AS name, devices.ip, devices.id
			FROM devices
			ORDER BY $orderby");

	}
	else
	{
		$dev_results = db_query("
			SELECT devices.name AS name, devices.ip, devices.id, devices.snmp_enabled,  
				count(snmp.ifIndex) AS interface_count, count(disk.disk_index) AS disk_count
			FROM dev_parents
			LEFT JOIN devices ON dev_parents.dev_id=devices.id
			LEFT JOIN snmp_interface_cache snmp ON devices.id=snmp.dev_id
			LEFT JOIN snmp_disk_cache disk ON devices.id=disk.dev_id 
			WHERE grp_id={$_REQUEST['grp_id']}
			GROUP BY devices.id
			ORDER BY $orderby");

	} // end if we have group id or not

	$dev_total = db_num_rows($dev_results);

	// For each device
	for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count)
	{
		$dev_row = db_fetch_array($dev_results);
		$dev_id  = $dev_row["id"];
		$links   = 
		cond_formatted_link($dev_row["interface_count"] > 0, "View&nbsp;Interface&nbsp;Cache", 
			"snmp_cache_view.php?dev_id=$dev_id&action=view&type=interface") . " " .
		cond_formatted_link($dev_row["snmp_enabled"] == 1, "Recache&nbsp;Interfaces", 
			"recache.php?dev_id=$dev_id&type=interface") . " " .
		cond_formatted_link($dev_row["disk_count"] > 0, "View&nbsp;Disk&nbsp;Cache", 
			"snmp_cache_view.php?dev_id=$dev_id&action=view&type=disk") . " " . 
		cond_formatted_link($dev_row["snmp_enabled"] == 1, "Recache&nbsp;Disks", 
			"recache.php?dev_id=$dev_id&type=disk");

		make_display_item("editfield".(($dev_count-1)%2),
			array("text" => $dev_row["name"], "href" => "sub_devices.php?dev_id=$dev_id"),
			array("text" => $links),
			array("text" => formatted_link("View", "view.php?object_type=device&object_id=$dev_id") . "&nbsp;" .
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&dev_id=$dev_id&grp_id={$_REQUEST["grp_id"]}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($dev_row["name"]) . "', '" . $dev_row["id"] . "')"))
		); // end make_display_item();

	} // end devices

?>
</table>
<?php
} // End if no action

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "add")
{
	check_auth(2);
	begin_page("devices.php", "Add Device");
	echo("<big><b>
		<a href='{$_SERVER['PHP_SELF']}?grp_id={$_REQUEST['grp_id']}&action=addnew'>Create a new device</a><br><br>
		<a href='{$_SERVER['PHP_SELF']}?grp_id={$_REQUEST['grp_id']}&action=addtogrp>Add an existing device to this group</a>
		</b></big>");

	end_page();
} // end if add

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "addtogrp")
{

	check_auth(2);
	begin_page("devices.php", "Add Device Group");
	make_edit_table("Add Existing Device to a Group");
	make_edit_select_from_table("Device:","dev_id","devices",-1);
	make_edit_hidden("action","doaddtogrp");
	make_edit_hidden("grp_id",$_REQUEST["grp_id"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
} // end if add to group

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "addnew")) {
	// Display editing screen
	check_auth(2);
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
		$dev_row["check_if_number"] = 1;
		$dev_row["dev_type"] = "";
		$dev_row["disabled"] = 0;
		$dev_row["snmp_enabled"] = 0;
		$dev_row["snmp_read_community"] = "";
		$dev_row["snmp_recache"] = 0;
		$dev_row["snmp_check_ifnumber"] = 0;
	}

	make_edit_table("Edit Device");
	make_edit_group("General");
	make_edit_text("Name:", "dev_name", "25", "100", $dev_name);
	make_edit_text("IP or Host Name:", "dev_ip", "25", "100", $dev_ip);
	make_edit_select_from_table("Device Type:", "dev_type", "dev_types", $dev_row["dev_type"]);
	make_edit_checkbox("Disabled (do not monitor this device)", "disabled", $dev_row["disabled"]);
	make_edit_group("SNMP");
	make_edit_checkbox("This device uses SNMP", "snmp_enabled", $dev_row["snmp_enabled"]);
	make_edit_text("SNMP Read Community:", "snmp_read_community", 50, 200, $dev_row["snmp_read_community"]);
	make_edit_checkbox("Do not cache interface mappings", "snmp_recache", $dev_row["snmp_recache"]);
	make_edit_checkbox("Clear interface cache when interface count changes", "snmp_check_ifnumber", $dev_row["snmp_check_ifnumber"]);
	make_edit_hidden("dev_id", $dev_id);
	make_edit_hidden("action", "doedit");
	make_edit_hidden("grp_id", $_REQUEST["grp_id"]);
	make_edit_submit_button();
	make_edit_end();

} // end if edit

end_page();

?>

