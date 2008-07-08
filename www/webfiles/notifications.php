<?php
/********************************************
* NetMRG Integrator
*
* mon_notify.php
* Event Notification Editing Page
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

if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
} // end if action is set or not

switch ($action)
{
	case "doedit":
	case "doadd":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_addedit();
		break;

	case "add":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	case "edit":
		addedit();
		break;

	case "dodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_delete();
		break;

	case "duplicate":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		duplicate();
		break;

	default:
		display();
		break;
}

function duplicate()
{
	$q = db_query("SELECT * FROM notifications WHERE id = '{$_REQUEST['id']}'");
	$r = db_fetch_array($q);
	$r['name'] = db_escape_string($r['name']);
	$r['command'] = db_escape_string($r['command']); 
	db_update("INSERT INTO notifications SET name='{$r['name']} (duplicate)', command='{$r['command']}', disabled='{$r['disabled']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
}

function do_addedit()
{
	if (!isset($disabled)) { $disabled = 0; }

	if ($_REQUEST["id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id='{$_REQUEST['id']}'";
	}
	if (empty($_REQUEST["disabled"])) { $_REQUEST["disabled"] = ""; }
	db_update("$db_cmd notifications SET name='{$_REQUEST['name']}',
		command='{$_REQUEST['command']}', disabled='{$_REQUEST['disabled']}'
		$db_end");

	header("Location: {$_SERVER['PHP_SELF']}");
}

function do_delete()
{
	db_update("DELETE FROM notifications WHERE id='{$_REQUEST['id']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
} // done deleting

function display()
{
	// Display a list
	begin_page("notifications.php", "Notifications");
	js_confirm_dialog("del", "Are you sure you want to delete notification ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&id=");
	
	make_display_table("Notifications", "", 
		array("text" => "Name"),
		array("text" => "Disabled"),
		array("text" => "Command")
	); // end make_display_table();
	
	$res = db_query("SELECT * FROM notifications");
	
	// For each notification
	for ($i = 0; $i < db_num_rows($res); $i++)
	{
		$row = db_fetch_array($res);
	
		make_display_item("editfield".($i%2),
			array("text" => $row["name"]),
			array("text" => ($row['disabled'] == 1 ? "Yes" : "No")),
			array("text" => $row["command"]),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}", "", "edit") . "&nbsp;" .
				formatted_link("Duplicate", "{$_SERVER["PHP_SELF"]}?action=duplicate&id=" . $row['id'], "", "duplicate") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('". addslashes($row['name'])."', '{$row['id']}')", "", "delete"))
		); // end make_display_item();
	}
	
	?>
	</table>
	<?php
	end_page();
} // End display
	
function addedit()
{
	GLOBAL $action;
	if (!empty($action) && ($action == "edit" || $action == "add"))
	{
	begin_page("notifications.php", "Notifications");
	
		// Display editing screen
		if ($action == "add")
		{
			$id = 0;
		}
		else
		{
			$id = $_REQUEST["id"];
		} // end if add
	
		$res = db_query("SELECT * FROM notifications WHERE id=$id");
		$row = db_fetch_array($res);
	
		make_edit_table("Edit Notificiation Method");
		make_edit_text("Name:", "name", "50", "100", $row["name"]);
		make_edit_textarea("Command:", "command", "1","40", $row["command"]);
		make_edit_label("You may use keywords %dev_name%, %ip%, %event_name%, %situation%, %current_value%, %delta_value%, %rate_value%, and %last_value% in your command parameters.  See the documentation for details.");
		make_edit_checkbox("Disabled", "disabled", $row["disabled"]);
		make_edit_hidden("id", $id);
		make_edit_hidden("action", "doedit");
		make_edit_submit_button();
		make_edit_end();
	
	} // End editing screen
	
	end_page();
}
?>
