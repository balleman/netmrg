<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Responses Editing Page                     #
#           mon_responses.php                          #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);
begin_page("mon_responses.php", "Responses");


if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
	unset($action);
} // end if action defined or not


if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if (!empty($action) && $action == "doadd")
{
	do_update("INSERT INTO mon_responses SET events_id='{$_REQUEST['event_id']}',
		notify_id='{$_REQUEST['notify_id']}', cmd_params='{$_REQUEST['cmd_params']}'");
} // done adding

if (!empty($action) && $action == "doedit")
{
	do_update("UPDATE mon_responses SET events_id='{$_REQUEST['event_id']}',
		notify_id='{$_REQUEST['notify_id']}', cmd_params='{$_REQUEST['cmd_params']}'
		WHERE id='{$_REQUEST['response_id']}'");
} // done editing

if (!empty($action) && $action == "dodelete")
{
	do_update("DELETE FROM mon_responses WHERE id='{$_REQUEST['response_id']}'");
} // done deleting

// Display a list
make_display_table("Event Responses","Event","","Notification Method","","Command Parameters","");

$responses_results = do_query("
	SELECT mon_responses.id, mon_devices.name as dev_name,
	mon_test.name as test_name, mon_notify.name as notify_name,
	mon_events.result as result, mon_responses.cmd_params,
	mon_events.condition as condition
	FROM mon_responses
	LEFT JOIN mon_events ON mon_responses.events_id=mon_events.id
	LEFT JOIN mon_monitors ON mon_events.monitors_id=mon_monitors.id
	LEFT JOIN mon_notify ON mon_responses.notify_id=mon_notify.id
	LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
	LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id");
$responses_total = mysql_num_rows($responses_results);

// For each response
for ($responses_count = 1; $responses_count <= $responses_total; ++$responses_count)
{

	$responses_row = mysql_fetch_array($responses_results);
	$response_id  = $responses_row[0];

	if ($responses_row["condition"] == 1)
	{
		// equal
		$operator = "=";
	}
	else
	{
		if ($responses_row["condition"] == 2)
		{
			// greater than
			$operator = ">";
		}
		else
		{
			// less than
			$operator = "<";
		}
	} // End Triggers


	make_display_item($responses_row["dev_name"] . " (" . $responses_row["test_name"] . " $operator " . $responses_row["result"] . ")","",
		$responses_row["notify_name"], "",
		$responses_row["cmd_params"], "",
		"Edit", "{$_SERVER['PHP_SELF']}?action=edit&response_id=$response_id",
		"Delete", "{$_SERVER['PHP_SELF']}?action=delete&response_id=$response_id");

} // end for

?>
</table>
<?php
} // End if no action

if (!empty($action) && $action == "add")
{
// Display editing screen

?>
<font size="4" color="#800000">Add Response</font><br><br>
Event:<br>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<select name="event_id">
<?php

$event_results = do_query("SELECT mon_events.id, mon_devices.name as dev_name, mon_test.name as test_name, mon_events.result as result
FROM mon_events LEFT JOIN mon_monitors ON mon_events.monitors_id=mon_monitors.id
LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id");

$event_total = mysql_num_rows($event_results);

for ($event_count = 1; $event_count <= $event_total; ++$event_count)
{

$event_row = mysql_fetch_array($event_results);

?><option value="<?phpprint($event_row["id"]);?>"><?phpprint($event_row["dev_name"] . " (" . $event_row["test_name"] . " = " . $event_row["result"] . ")");

} // end for
?>
</select><br><br>
Notification Method:<br><br>
<select name="notify_id">
<?php

$notify_results = do_query("SELECT * FROM mon_notify");
$notify_total = mysql_num_rows($notify_results);

for ($notify_count = 1; $notify_count <= $notify_total; ++$notify_count)
{

$notify_row = mysql_fetch_array($notify_results);
$notify_name = $notify_row["name"];
$notify_id	= $notify_row["id"];

?><option value="<?phpprint($notify_id);?>"><?phpprint("$notify_name\n");

} // end for
?>
</select>
<br><br>
Command Parameters:<br><br>
<input type="text" name="cmd_params" size="25" maxlength="100"><br>
You may use the keywords %dev_name, %ip, %test_name, and %test_result in your command parameters.
<br><br>
<input type="hidden" name="action" value="doadd">
<input type="submit" name="Submit" value="Submit">
</form>
<?php
} // End editing screen

if (!empty($action) && $action == "edit")
{
// Display editing screen

?>
<font size="4" color="#800000">Edit Response</font><br><br>
Event:<br>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<select name="event_id" >
<?php

$response_results = do_query("SELECT * FROM mon_responses WHERE id=$response_id");
$response_row = mysql_fetch_array($response_results);
$events_id_cur = $response_row["events_id"];
$notify_id_cur = $response_row["notify_id"];

$event_results = do_query("SELECT mon_events.id, mon_devices.name as dev_name, mon_test.name as test_name, mon_events.result as result
FROM mon_events LEFT JOIN mon_monitors ON mon_events.monitors_id=mon_monitors.id
LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id");

$event_total = mysql_num_rows($event_results);

for ($event_count = 1; $event_count <= $event_total; ++$event_count)
{

$event_row = mysql_fetch_array($event_results);

if ($events_id_cur != $event_row["id"])
{
?><option value="<?phpprint($event_row["id"]);?>"><?phpprint($event_row["dev_name"] . " (" . $event_row["test_name"] . " = " . $event_row["result"] . ")");
} else {
?><option selected value="<?phpprint($event_row["id"]);?>"><?phpprint($event_row["dev_name"] . " (" . $event_row["test_name"] . " = " . $event_row["result"] . ")");
} // end if
} // end for
?>
</select><br><br>
Notification Method:<br><br>
<select name="notify_id">
<?php

$notify_results = do_query("SELECT * FROM mon_notify");
$notify_total = mysql_num_rows($notify_results);

for ($notify_count = 1; $notify_count <= $notify_total; ++$notify_count)
{

$notify_row = mysql_fetch_array($notify_results);
$notify_name = $notify_row["name"];
$notify_id	= $notify_row["id"];

if ($notify_id_cur != $notify_id)
{
?><option value="<?phpprint($notify_id);?>"><?phpprint("$notify_name\n");
}
else
{
?><option selected value="<?phpprint($notify_id);?>"><?phpprint("$notify_name\n");
} // end if
} // end for
?>
</select>
<br><br>
Command Parameters:<br><br>
<input type="text" name="cmd_params" size="25" maxlength="100" value="<?phpprint($response_row["cmd_params"]);?>"><br>
You may use the keywords %dev_name, %ip, %test_name, and %test_result in your command parameters.
<br><br>
<input type="hidden" name="action" value="doedit">
<input type="hidden" name="response_id" value="<?phpprint("$response_id");?>">
<input type="submit" name="Submit" value="Submit">
</form>
<?php } // End editing screen

if (!empty($action) && $action == "delete")
{
// Display delete confirmation
?>
<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this monitor?

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="response_id" value="<?php print("$response_id"); ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="submit" value="No">
</form>

<?php

} // end delete confirmation

end_page();

?>
