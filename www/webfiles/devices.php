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
			do_update("$db_cmd devices SET 
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
				do_update("INSERT INTO dev_parents SET grp_id={$_REQUEST['grp_id']}, dev_id=" . mysql_insert_id());
			} // end if dev+id = 0
		} // done editing
	} // end if we editing

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doaddtogrp")
	{
		check_auth(2);
		do_update("INSERT INTO dev_parents SET grp_id=$grp_id, dev_id=$dev_id");
	} // end if we're adding to a group

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "dodelete")
	{
		check_auth(2);
		delete_device($_REQUEST["dev_id"]);
	} // done deleting

	// Display a list
	if (isset($_REQUEST["grp_id"]))
	{
		$group_results = do_query("SELECT * FROM groups WHERE id={$_REQUEST['grp_id']}");
		$group_array = mysql_fetch_array($group_results);

		$title = "Monitored Devices in Group '" . $group_array["name"] . "'";

		$custom_add_link = "{$_SERVER['PHP_SELF']}?action=add&grp_id={$_REQUEST['grp_id']}";

	}
	else
	{
		$title = "Monitored Devices";

	} // end if we have a group id
	begin_page("devices.php", "Devices");
	js_confirm_dialog("del", "Are you sure you want to delete device ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&grp_id={$_REQUEST['grp_id']}&dev_id=");
	make_display_table($title,
	   "Name", "{$_SERVER['PHP_SELF']}?orderby=name",
	   "SNMP Options","");

	if (!isset($orderby)) { $orderby = "name"; };

	if (!(isset($_REQUEST["grp_id"])))
	{
		$dev_results = do_query("
			SELECT devices.name AS name, devices.ip, devices.id
			FROM devices
			ORDER BY $orderby");

	}
	else
	{
		$dev_results = do_query("
			SELECT devices.name AS name, devices.ip, devices.id 
			FROM dev_parents
			LEFT JOIN devices ON dev_parents.dev_id=devices.id 
			WHERE grp_id={$_REQUEST['grp_id']}
			ORDER BY $orderby");

	} // end if we have group id or not

	$dev_total = mysql_num_rows($dev_results);

	// For each device
	for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count)
	{
		$dev_row = mysql_fetch_array($dev_results);
		$dev_id  = $dev_row["id"];

		make_display_item($dev_row["name"], "sub_devices.php?dev_id=$dev_id",
			formatted_link("View Interface Cache",	"snmp_cache_view.php?dev_id=$dev_id&action=view&type=interface") . "&nbsp;" .
			formatted_link("Recache Interfaces",	"recache.php?dev_id=$dev_id&type=interface") . "&nbsp;" .
			formatted_link("View Disk Cache",	"snmp_cache_view.php?dev_id=$dev_id&action=view&type=disk") . "&nbsp;" . 
			formatted_link("Recache Disks",		"recache.php?dev_id=$dev_id&type=disk"), "",
			formatted_link("View",			"view.php?pos_id_type=1&pos_id=$dev_id") . "&nbsp;" .
			formatted_link("Edit",			"{$_SERVER['PHP_SELF']}?action=edit&dev_id=$dev_id&grp_id={$_REQUEST["grp_id"]}") . "&nbsp;" .
			formatted_link("Delete",		"javascript:del('" . $dev_row["name"] . "', '" . $dev_row["id"] . "')"), "");

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
	$dev_results = do_query($dev_select);
	$dev_row = mysql_fetch_array($dev_results);
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

