<?php
/********************************************
* NetMRG Integrator
*
* sub_devices.php
* Sub-Devices Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");

if (!isset($_REQUEST["action"]))
{
	// Display the list of sub-devices for a particular device.

	check_auth(1);
	begin_page("sub_devices.php", "Sub Device");
	DrawGroupNavHistory("device", $_REQUEST["dev_id"]);
	js_confirm_dialog("del", "Are you sure you want to delete subdevice ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&dev_id={$_REQUEST['dev_id']}&sub_dev_id=");

	$results = db_query("SELECT name, id, type FROM sub_devices WHERE sub_devices.dev_id={$_REQUEST['dev_id']} ORDER BY name");

	make_display_table("Sub-Devices for " . get_device_name($_REQUEST["dev_id"]),
		"{$_SERVER['PHP_SELF']}?action=add&dev_id={$_REQUEST['dev_id']}",
		array("text" => "Sub-Devices"),
		array("text" => "Type")
	); // end make_display_table();

	GLOBAL $SUB_DEVICE_TYPES;

	for ($i = 0; $i < db_num_rows($results); $i++)
	{
		$row = db_fetch_array($results);
		make_display_item("editfield".($i%2),
			array("text" => $row["name"], "href" => "monitors.php?sub_dev_id=" . $row["id"]),
			array("text" => $SUB_DEVICE_TYPES[$row["type"]]),
			array("text" => formatted_link("Parameters", "sub_dev_param.php?dev_id={$_REQUEST['dev_id']}&sub_dev_id=" . $row["id"]) . "&nbsp;" .
				formatted_link("View", "view.php?object_type=subdevice&object_id={$row['id']}") . "&nbsp;" . 
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&dev_id={$_REQUEST['dev_id']}&sub_dev_id=" . $row["id"]) . "&nbsp;" .
				formatted_link("Delete", "javascript:del('".addslashes($row['name'])."','{$row['id']}')"))
		); // end make_display_item();
	}

	?></table><?php

	end_page();
}

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit")
{
	if ($_REQUEST["sub_dev_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id={$_REQUEST['sub_dev_id']}";
	}
	
	$_REQUEST['name'] = db_escape_string($_REQUEST['name']);

	db_update("$db_cmd sub_devices SET
		name='{$_REQUEST['name']}',
		type='{$_REQUEST['type']}',
		dev_id='{$_REQUEST['dev_id']}'
		$db_end");

	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}");
}

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "dodelete"))
{
	delete_subdevice($_REQUEST["sub_dev_id"]);
	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}");
}


if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add"))
{
	check_auth(2);
	begin_page("sub_devices.php", "Add/Edit Sub Device");

	if ($_REQUEST["action"] == "add")
	{
		$_REQUEST["sub_dev_id"] = 0;
		$row = array();
		$row["name"] = "";
		$row["type"] = "";
	} // end if add

	if ($_REQUEST["sub_dev_id"] > 0)
	{
		$query = db_query("SELECT * FROM sub_devices WHERE id = {$_REQUEST['sub_dev_id']}");
		$row   = db_fetch_array($query);
	}

	make_edit_table("Sub-Device Properties");
	make_edit_text("Name:", "name", 40, 80, $row["name"]);
	make_edit_select_from_array("Sub-Device Type:", "type", $GLOBALS['SUB_DEVICE_TYPES'], $row["type"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id", $_REQUEST["sub_dev_id"]);
	make_edit_hidden("dev_id", $_REQUEST["dev_id"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

}


?>
