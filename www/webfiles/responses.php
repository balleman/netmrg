<?php
/********************************************
* NetMRG Integrator
*
* responses.php
* Responses Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadAll"]);

if (!isset($_REQUEST["action"]))
	$action = "";
else
	$action = $_REQUEST["action"];

switch ($action)
{
	case "add":		$_REQUEST['id'] = 0;
				display_edit();
				break;

	case "edit":		display_edit();
				break;

	case "doedit":		do_edit();
				break;

	case "dodelete":        do_delete();
				break;

	default:		display_list();
}

function display_list()
{
	begin_page("responses.php", "Responses");
	PrepGroupNavHistory("event", $_REQUEST["event_id"]);
	DrawGroupNavHistory("event", $_REQUEST["event_id"]);
	js_confirm_dialog("del", "Are you sure you want to delete response ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}&id=");
	make_display_table("Responses", "responses.php?action=add&event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}", 
		array("text" => "Name"),
		array("text" => "Parameters")
	);

	$res = db_query("	SELECT responses.id, notifications.name, responses.parameters
				FROM responses, notifications
				WHERE responses.notification_id=notifications.id
				AND event_id={$_REQUEST['event_id']}");
	$rowcount = 0;
	while ($row = db_fetch_array($res))
	{
		make_display_item("editfield".($rowcount%2),
			array("text" => $row['name']),
			array("text" => $row['parameters']),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('{$row['name']}','{$row['id']}')"))
		); // end make_display_item();
		$rowcount++;
	}
	make_status_line("response", $rowcount);
	?>
	</table>
	<?php
	end_page();
} // end display_list();

function display_edit()
{
	if ($_REQUEST['id'] == 0)
	{
		$row['notification_id'] = 0;
		$row['parameters'] = "";
		$row['id'] = 0;
		$row['event_id'] = $_REQUEST['event_id'];
	}
	else
	{
		$res = db_query("SELECT * FROM responses WHERE id={$_REQUEST['id']}");
		$row = db_fetch_array($res);
	}
	
	begin_page("responses.php", "Responses");
	make_edit_table("Edit Response");
	make_edit_select_from_table("Notification:", "notification_id", "notifications", $row['notification_id']);
	make_edit_text("Parameters:", "parameters", "50", "100", $row['parameters']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("tripid", $_REQUEST['tripid']);
	make_edit_hidden("event_id", $row['event_id']);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();
	end_page();
}

function do_edit()
{
	check_auth($PERMIT["ReadWrite"]);
	
	if ($_REQUEST['id'] == 0)
	{
		$pre  = "INSERT INTO";
		$post = ", event_id={$_REQUEST['event_id']}";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE id = {$_REQUEST['id']}";
	}
	
	$_REQUEST['parameters'] = db_escape_string($_REQUEST['parameters']);
	db_update("$pre responses SET notification_id = '{$_REQUEST['notification_id']}', parameters ='{$_REQUEST['parameters']}' $post");
	
	header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}");
}

function do_delete()
{
	check_auth($PERMIT["ReadWrite"]);
	delete_response($_REQUEST['id']);
	header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}&tripid={$_REQUEST['tripid']}");
}

?>
