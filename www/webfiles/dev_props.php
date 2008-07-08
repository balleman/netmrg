<?php
/********************************************
* NetMRG 
*
* dev_props.php
* Device Properties Editing Page
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

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action

switch($_REQUEST["action"])
{
	case "doedit":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_edit();
		redirect();
		break;
	
	case "dodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		$_REQUEST['prop_id'] *= 1;
		db_update("DELETE FROM dev_props WHERE id = {$_REQUEST['prop_id']}");
		db_update("DELETE FROM dev_prop_vals WHERE prop_id = {$_REQUEST['prop_id']}");
		redirect();
		break;
	
	case "multidodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		while (list($key,$value) = each($_REQUEST["devprop"]))
		{
			$key *= 1;
			db_update("DELETE FROM dev_props WHERE id = $key");
		}
		redirect();
		break;
	
	case "add":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	case "edit":
		edit();
		break;
	
	default:
	case "list":
		do_list();
		break;
} // end switch action



/***** FUNCTIONS *****/
function do_list()
{
	$q = db_query("SELECT name FROM dev_types WHERE id = '{$_REQUEST['dev_type']}'");
	$r = db_fetch_array($q);
	begin_page("dev_props.php", "Device Properties for {$r['name']}");
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="dev_type" value="<?php echo $_REQUEST['dev_type']; ?>">
	<input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
	<?php

	//PrepGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	//DrawGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	
	js_confirm_dialog("del", "Are you sure you want to delete device property ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}&prop_id=");
	make_display_table("Device Properties for {$r['name']}", "{$_SERVER['PHP_SELF']}?action=add&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}",
		array("text" => checkbox_toolbar()),
		array("text" => "Name"),
		array("text" => "Test")
	); // end make_display_table();

	$results = db_query("SELECT * FROM dev_props WHERE dev_type_id = {$_REQUEST['dev_type']}");
	$prop_count = 0;

	while ($row = mysql_fetch_array($results))
	{
		$test_name = get_short_test_name($row['test_type'], $row['test_id'], $row['test_params']);
		$prop_id = $row['id'];

		make_display_item("editfield".($prop_count%2),
			array("checkboxname" => "devprop", "checkboxid" => $row['id']),
			array("text" => $row['name']),
			array("text" => $test_name),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&prop_id=$prop_id&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}", "", "edit") . "&nbsp;" .
				formatted_link("Delete","javascript:del('$java_name', '$prop_id')", "", "delete"))
		); // end make_display_item();
		
		$prop_count++;

	} // end for each monitor
	make_checkbox_command("", 5,
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked properties?")
	); // end make_checkbox_command
	make_status_line("property", $prop_count, "properties");
	?>
	</table>
	</form>
	<?php

	end_page();

} // end do_list()


function edit()
{
	begin_page("dev_props.php", "Device Properties");
	
	// if we're editing a property
	if ($_REQUEST["action"] == "edit")
	{
		make_edit_table("Edit Device Property");
	} // end if edit
	// if we're adding a monitor
	else
	{
		make_edit_table("Add Device Property");
	} // end else add
	
	// if we're editing a property
	if ($_REQUEST["action"] == "edit")
	{
		$results = db_query("
			SELECT
			id,
			name,
			test_type,
			test_id,
			test_params
			FROM dev_props
			WHERE dev_props.id='{$_REQUEST['prop_id']}'
			");
		$row = db_fetch_array($results);

	
	} // end if editing a property
	// if we're adding a property
	else
	{
		$id = 0;
		$row["test_id"] = 1;
		if (!empty($_REQUEST["type"]))
		{
			$row["test_type"] = $_REQUEST["type"];
		}
		else
		{
			$row["test_type"] = 0;
		}
		$row["test_params"] = "";
		$_REQUEST["prop_id"] = 0;
	} // end if adding a property
	
	make_edit_group("General Parameters");
	make_edit_text("Name:", "name", "25", "200", $row['name']);
	make_edit_select_test($row['test_type'], $row['test_id'], $row['test_params']);	
	
	make_edit_hidden("action", "doedit");
	make_edit_hidden("prop_id", $_REQUEST["prop_id"]);
	make_edit_hidden("dev_type", $_REQUEST['dev_type']);
	make_edit_hidden("tripid", $_REQUEST["tripid"]);
	
	make_edit_submit_button();
	make_edit_end();
	
	end_page();
} // end edit();

function redirect()
{
	header("Location: dev_props.php?dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}");
} // end redirect()


function do_edit()
{
	if ($_REQUEST["prop_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id='{$_REQUEST['prop_id']}'";
	}
	
	db_update("$db_cmd dev_props SET
		name='{$_REQUEST['name']}',
		test_type='{$_REQUEST['test_type']}',
		test_id='{$_REQUEST['test_id']}',
		test_params='" . $_REQUEST['test_params'] ."',
		dev_type_id='{$_REQUEST['dev_type']}'
		$db_end");

	redirect();
	
} // end do_edit()

?>
