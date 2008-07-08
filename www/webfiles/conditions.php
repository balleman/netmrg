<?php
/********************************************
* NetMRG Integrator
*
* conditions.php
* Conditions Editing Page
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

switch ($_REQUEST['action'])
{
	case "add":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	case "edit":
		display_edit();
		break;

	case "doedit":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_edit();
		break;

	case "multidodelete":
	case "dodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_delete();
		break;

	default:
		do_display();
} // end switch action


function do_display()
{
	// Display a list
	begin_page("conditions.php", "Conditions");
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="event_id" value="<?php echo $_REQUEST['event_id']; ?>">
	<input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
	<?php
	PrepGroupNavHistory("event", $_REQUEST["event_id"]);
	DrawGroupNavHistory("event", $_REQUEST["event_id"]);

	$query = db_query("SELECT * FROM `conditions` WHERE event_id = {$_REQUEST['event_id']} ORDER BY id");
	$rows = db_num_rows($query);
	if ($rows == 0)
	{
		$nologic = "&nologic=1";
	}
	else
	{
		$nologic = "";
	}

	js_confirm_dialog("del", "Are you sure you want to delete condition ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}&id=");
	make_display_table("Conditions", "{$_SERVER['PHP_SELF']}?action=add&event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}$nologic",
		array("text" => checkbox_toolbar()),
		array("text" => "Condition")
	); // end make_display_table();

	for ($i = 0; $i < $rows; $i++)
	{
		$row = db_fetch_array($query);
		$condition_name = $GLOBALS['VALUE_TYPES'][$row['value_type']] . "&nbsp;" . $GLOBALS['CONDITIONS'][$row['condition']] . "&nbsp;" . $row['value'];
		if ($i != 0)
		{
			$condition_name = $GLOBALS['LOGIC_CONDITIONS'][$row['logic_condition']] . "&nbsp;" . $condition_name;
			$nologic = "";
		}
		else
		{
			$nologic = "&nologic=1";
		}
		make_display_item("editfield".($i%2),
			array("checkboxname" => "condition", "checkboxid" => $row['id']),
			array("text" => $condition_name),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}&tripid={$_REQUEST['tripid']}$nologic", "", "edit") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . $condition_name . "','" . $row['id'] . "')", "", "delete"))
		); // end make_display_item();
	}
	make_checkbox_command("", 4,
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked conditions?")
	); // end make_checkbox_command
	make_status_line("condition", $rows);
	?>
	</table>
	</form>
	<?php
	end_page();
} // end do_display();


function display_edit()
{
	begin_page("conditions.php", "Edit Conditions");

	if ($_REQUEST['action'] == "add")
	{
		$row['id']			= 0;
		$row['event_id']	= $_REQUEST['event_id'];
		$row['logic_condition'] = 0;
		$row['condition']	= 1;
		$row['value']		= 0;
		$row['value_type']	= 0;
	}
	else
	{
		$query = db_query("SELECT * FROM `conditions` WHERE id={$_REQUEST['id']}");
		$row   = db_fetch_array($query);
	}

	make_edit_table("Edit Condition");
	if (!isset($_REQUEST['nologic']))
	{
		make_edit_select_from_array("Logical Operation:", "logic_condition", $GLOBALS['LOGIC_CONDITIONS'], $row['logic_condition']);
	}
	else
	{
		make_edit_hidden("logic_condition", "0");
	}
	make_edit_select_from_array("Value Type:", "value_type", $GLOBALS['VALUE_TYPES'], $row['value_type']);
	make_edit_select_from_array("Condition:", "condition", $GLOBALS['CONDITIONS'], $row['condition']);
	make_edit_text("Value:", "value", 5, 10, $row['value']);
	make_edit_hidden("event_id", $row['event_id']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("tripid", $_REQUEST['tripid']);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();
	end_page();

} // end display_edit();


function do_edit()
{
	if ($_REQUEST['id'] == 0)
	{
		$pre  = "INSERT INTO";
		$post = ", `event_id`='{$_REQUEST['event_id']}'";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE `id` = '{$_REQUEST['id']}'";
	}

	db_update("$pre `conditions` SET `logic_condition`='{$_REQUEST['logic_condition']}', `value_type`='{$_REQUEST['value_type']}', `condition`='{$_REQUEST['condition']}', `value`='{$_REQUEST['value']}' $post");

	header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}");
} // end do_edit();


function do_delete()
{
	if (isset($_REQUEST['condition']))
	{
		while (list($key,$value) = each($_REQUEST["condition"]))
		{
			db_update("DELETE FROM `conditions` WHERE id = '$key'");
		}
	}
	else if(isset($_REQUEST["id"]))
	{
		db_update("DELETE FROM `conditions` WHERE id = '{$_REQUEST['id']}'");
	}
	header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}");
} // end do_delete();


?>
