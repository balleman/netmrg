<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Events Editing Page                        #
#           events.php                                 #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);

if ((!isset($_REQUEST['action'])) || ($_REQUEST['action'] == "doedit") || ($_REQUEST['action'] == "dodelete") || ($_REQUEST['action'] == "doadd"))
{

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

if ($_REQUEST['action'] == "doadd")
{
	check_auth(2);
	do_update("INSERT INTO events SET monitors_id=$mon_id, result=$result, condition=$mon_conditions, options=$mon_options, situation=$mon_situations, display_name=\"$display_name\"");
} // done adding

if ($_REQUEST['action'] == "doedit")
{
	check_auth(2);
	do_update("UPDATE events SET monitors_id=$mon_id, result=$result, condition=$mon_conditions, options=$mon_options, situation=$mon_situations, display_name=\"$display_name\" WHERE id=$event_id");
} // done editing

if ($_REQUEST['action'] == "dodelete")
{
	check_auth(2);
	delete_event($event_id);
} // done deleting


// Display a list

$title = "Events for " . get_monitor_name($_REQUEST['mon_id']);

$custom_add_link = "{$_SERVER['PHP_SELF']}?action=add&mon_id={$_REQUEST['mon_id']}";

begin_page();
js_confirm_dialog("del", "Are you sure you want to delete event ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&mon_id=$mon_id&event_id=");
make_display_table("Events","Display Name","","Result to Trigger","","Trigger Condition","","When to Trigger","","Situation","");

$query = "
SELECT
events.id AS id,
events.display_name AS display_name,
mon_test.name AS test_name,
mon_devices.name AS dev_name,
events.result,
mon_conditions.name AS condition,
mon_options.name AS options,
mon_situations.name AS situation,
monitors.id AS mon_id
FROM events
LEFT JOIN monitors 		ON events.monitors_id=monitors.id
LEFT JOIN mon_test 		ON monitors.test_id=mon_test.id
LEFT JOIN mon_devices 		ON monitors.device_id=mon_devices.id
LEFT JOIN mon_conditions	ON events.condition=mon_conditions.id
LEFT JOIN mon_options 		ON events.options=mon_options.id
LEFT JOIN mon_situations	ON events.situation=mon_situations.id";

if (!isset($mon_id))
{
	$mon_results = do_query($query);
}
else
{
	$mon_results = do_query($query . " WHERE monitors.id=$mon_id");
}

$mon_total = mysql_num_rows($mon_results);

// For each device
for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count)
{

$mon_row = mysql_fetch_array($mon_results);
$event_id  = $mon_row[0];

make_display_item(get_monitor_name($mon_row["mon_id"]),"",
				#$mon_row["display_name"],"",
				  $mon_row["result"],"",
				  $mon_row["condition"],"",
				  $mon_row["options"],"",
				  $mon_row["situation"],"",
				  "Edit","{$_SERVER['PHP_SELF']}?action=edit&event_id=$event_id",
                  "Delete","javascript:del('" . $mon_row["display_name"] . "', '" . $event_id . "')");

} # end devices

?>
</table>
<?php
} // End if no action

if ($action == "add")
{
	# Display editing screen
	check_auth(2);
	begin_page();
	make_edit_table("Add Events");
	make_edit_select_monitor($mon_id);
	make_edit_text("Display Name:","display_name","50","100","");
	make_edit_text("Result:","result","25","100","");
	make_edit_select_from_table("Trigger Condition:","mon_conditions","mon_conditions",0);
	make_edit_select_from_table("When to Trigger:","mon_options","mon_options",0);
	make_edit_select_from_table("Situation Event Indicates:","mon_situations","mon_situations",0);
	make_edit_hidden("action","doadd");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

if ($action == "edit")
{
	# Display editing screen
	check_auth(2);
	begin_page();
	make_edit_table("Edit Event");
	$event_results = do_query("SELECT * FROM events WHERE id=$event_id");
	$event_row = mysql_fetch_array($event_results);
	$mon_id_cur	= $event_row["monitors_id"];
	$result_cur = $event_row["result"];
	make_edit_select_monitor($mon_id_cur);
	make_edit_text("Display Name:","display_name","50","100",$event_row["display_name"]);
	make_edit_text("Result:","result","25","100",$event_row["result"]);
	make_edit_select_from_table("Trigger Condition:","mon_conditions","mon_conditions",$event_row["condition"]);
	make_edit_select_from_table("When to Trigger:","mon_options","mon_options",$event_row["options"]);
	make_edit_select_from_table("Situation Event Indicates:","mon_situations","mon_situations",$event_row["situation"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("event_id",$event_id);
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

if ($action == "delete")
{
	// Display delete confirmation
	check_auth(2);
	begin_page();
?>
<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this event?

<form action="<?php print("{$_SERVER['PHP_SELF']}"); ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="event_id" value="<?php print("$event_id"); ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<?php print("{$_SERVER['PHP_SELF']}"); ?>" method="post">
<input type="submit" value="No">
</form>

<?php

} // end delete confirmation

end_page();

?>
