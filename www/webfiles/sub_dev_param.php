<?php
/********************************************
* NetMRG Integrator
*
* sub_dev_param.php
* Sub-Devices Parameters Page
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
check_auth($GLOBALS['PERMIT']["ReadAll"]);

if (empty($_REQUEST["action"]))
{
	// Display the list of sub-devices for a particular device.

	begin_page("sub_dev_param.php", "Sub Device Parameters");
	PrepGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	DrawGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	js_confirm_dialog("del", "Are you sure you want to delete subdevice parameter ", "", "{$_SERVER['PHP_SELF']}?action=dodelete&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&name=");

	$results = db_query("SELECT name, value FROM sub_dev_variables WHERE type='static' AND sub_dev_id={$_REQUEST['sub_dev_id']}");

	make_display_table("Configured Parameters for " . get_dev_sub_device_name($_REQUEST["sub_dev_id"]), 
		"{$_SERVER['PHP_SELF']}?action=add&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => "Name"),
		array("text" => "Value")
	); // end make_display_table();

	for ($i = 0; $i < db_num_rows($results); $i++)
	{
		$row = db_fetch_array($results);
		make_display_item("editfield".($i%2),
			array("text" => $row["name"]),
			array("text" => $row["value"]),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&name=" . $row["name"]) . "&nbsp;" . 
				formatted_link("Delete", "javascript:del('".addslashes(htmlspecialchars($row['name']))."', '".addslashes(htmlspecialchars($row['name']))."')"), "")
		); // end make_display_item();
	}

	?></table><br><br><?php

	$results = db_query("SELECT name, value FROM sub_dev_variables WHERE type='dynamic' AND sub_dev_id={$_REQUEST['sub_dev_id']}");

	make_display_table("Dynamic Parameters for " . get_dev_sub_device_name($_REQUEST["sub_dev_id"]), "#",
		array("text" => "Name"),
		array("text" => "Value")
	); // end make_display_table();

	for ($i = 0; $i < db_num_rows($results); $i++)
	{
		$row = db_fetch_array($results);
		make_display_item("editfield".($i%2),
			array("text" => $row["name"]),
			array("text" => $row["value"]),
			array("text" => "")
		); // end make_display_item();
	}

	?></table><?php

	end_page();
}

elseif ($_REQUEST["action"] == "doedit")
{
	check_auth($GLOBALS['PERMIT']["ReadWrite"]);
        if ($_REQUEST["type"] == "add")
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE name=\"{$_REQUEST['oldname']}\" AND sub_dev_id={$_REQUEST['sub_dev_id']}";
	}

	db_update("$db_cmd sub_dev_variables SET
			name=\"{$_REQUEST['name']}\",
			value=\"{$_REQUEST['value']}\",
			sub_dev_id={$_REQUEST['sub_dev_id']}
			$db_end");

	header("Location: " . $_SERVER["PHP_SELF"] . "?sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}");
}

elseif (($_REQUEST["action"] == "edit") || ($_REQUEST["action"] == "add"))
{
	check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	begin_page("sub_dev_param.php", "Add/Edit Sub Device Parameter");
       	make_edit_table("Sub-Device Parameter");

	if ($_REQUEST["action"] == "edit")
	{
		$query = db_query("SELECT * FROM sub_dev_variables WHERE sub_dev_id = {$_REQUEST['sub_dev_id']} AND name = \"{$_REQUEST['name']}\"");
		if (db_num_rows($query) > 0)
		{
			$row   = db_fetch_array($query);
			make_edit_hidden("oldname", $row['name']);
		}
	}
	else
	{
		$row["name"] = "";
		$row["value"] = "";
	}

	make_edit_text("Name:", "name", 40, 80, $row["name"]);
	make_edit_text("Value:", "value", 40, 80, $row["value"]);
	make_edit_hidden("type", $_REQUEST['action']);
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id",$_REQUEST["sub_dev_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

}

elseif ($_REQUEST["action"] == "dodelete")
{
	check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	db_update("DELETE FROM sub_dev_variables WHERE sub_dev_id={$_REQUEST['sub_dev_id']} AND name='{$_REQUEST['name']}' AND type='static'");
	header("Location: " . $_SERVER["PHP_SELF"] . "?sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}");
}

?>
