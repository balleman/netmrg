<?php
/********************************************
* NetMRG Integrator
*
* events.php
* Events Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadAll"]);

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case "edit":			display_edit(); 	break;
		case "add":				display_edit(); 	break;
		case "doedit":			do_edit(); 			break;
		case "multidodelete":
		case "dodelete":		do_delete(); 		break;
	}
}
else
{
	do_display();
}

function do_display()
{

	// Display a list

	check_auth($PERMIT["ReadAll"]);
	begin_page("events.php", "Events");
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="mon_id" value="<?php echo $_REQUEST['mon_id']; ?>">
	<input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
	<?php
	PrepGroupNavHistory("monitor", $_REQUEST["mon_id"]);
	DrawGroupNavHistory("monitor", $_REQUEST["mon_id"]);
	
	$title = "Events for " . get_monitor_name($_REQUEST['mon_id']);
	js_confirm_dialog("del", "Are you sure you want to delete event ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}&id=");
	make_display_table($title, "{$_SERVER['PHP_SELF']}?action=add&mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => checkbox_toolbar()),
		array("text" => "Name"),
		array("text" => "Trigger Options"),
		array("text" => "Situation"),
		array("text" => "Status")
	); // end make_display_table();

	$query = db_query("SELECT * FROM events WHERE mon_id = {$_REQUEST['mon_id']} ORDER BY name");

	$rowcount = 0;
	while (($row = db_fetch_array($query)) != NULL)
	{
		if ($row['last_status'] == 1)
		{
			$triggered = "<b>Triggered</b>";
			$name = "<b>" . $row['name'] . "</b>";
		}
		else
		{
			$triggered = "Not Triggered";
			$name = $row['name'];
		}

		make_display_item("editfield".($rowcount%2),
			array("checkboxname" => "event", "checkboxid" => $row['id']),
			array("text" => $name, "href" => "responses.php?event_id={$row['id']}&tripid={$_REQUEST['tripid']}"),
			array("text" => $GLOBALS['TRIGGER_TYPES'][$row['trigger_type']]),
			array("text" => $GLOBALS['SITUATIONS'][$row['situation']]),
			array("text" => $triggered),
			array("text" => formatted_link("Modify Conditions", "conditions.php?event_id={$row['id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($row['name']) . "','" . $row['id'] . "')"))
		); // end make_display_item();
		$rowcount++;
	} // end while rows left
	?>
	<tr>
		<td colspan="6" class="editheader" nowrap="nowrap">
			&lt;<a class="editheaderlink" onclick="document.form.action.value='multidodelete';javascript:if(window.confirm('Are you sure you want to delete the checked sub-devices?')){document.form.submit();}" href="#">Delete All Checked</a>&gt;
		</td>
	</tr>
	<?php
	make_status_line("event", $rowcount);
	?>
	</table>
	</form>
	<?php
	end_page();
}

function display_edit()
{
	check_auth($PERMIT["ReadWrite"]);
	begin_page("events.php", "Edit Event");

	if ($_REQUEST['action'] == "add")
	{
		$row['id'] 		= 0;
		$row['mon_id'] 		= $_REQUEST['mon_id'];
		$row['name']		= "";
		$row['trigger_type']	= 2;
		$row['situation']	= 1;
	}
	else
	{
		$query = db_query("SELECT * FROM events WHERE id={$_REQUEST['id']}");
		$row   = db_fetch_array($query);
	}

	make_edit_table("Edit Event");
        make_edit_text("Name:", "name", "25", "100", $row['name']);
	make_edit_select_from_array("Trigger Type:", "trigger_type", $GLOBALS['TRIGGER_TYPES'], $row['trigger_type']);
        make_edit_select_from_array("Situation:", "situation", $GLOBALS['SITUATIONS'], $row['situation']);
	make_edit_hidden("mon_id", $row['mon_id']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("tripid", $_REQUEST['tripid']);
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
		$post = ", mon_id={$_REQUEST['mon_id']}";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE id = {$_REQUEST['id']}";
	}

	$_REQUEST['name'] = db_escape_string($_REQUEST['name']);	
	db_update("$pre events SET name = '{$_REQUEST['name']}', trigger_type={$_REQUEST['trigger_type']}, situation={$_REQUEST['situation']} $post");
	header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}");
}

function do_delete()
{
	check_auth($PERMIT["ReadWrite"]);
	if (isset($_REQUEST['event']))
	{
		while (list($key,$value) = each($_REQUEST["event"]))
		{
			delete_event($key);
		}
	}
	else
	{
		delete_event($_REQUEST['id']);
	}
	header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}");
}
?>
