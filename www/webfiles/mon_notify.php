<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Event Notification Editing Page            #
#           mon_notify.php                             #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);
begin_page("mon_notify.php", "Notifications");


if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
	unset($action);
} // end if action is set or not


if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if (!empty($action) && ($action == "doedit" || $action == "doadd"))
{
	check_auth(2);
	if (!isset($disabled)) { $disabled = 0; }
	if ($action == "doedit")
	{
		if ($_REQUEST["notify_id"] == 0)
		{
			$db_cmd = "INSERT INTO";
			$db_end = "";
		}
		else
		{
			$db_cmd = "UPDATE";
			$db_end = "WHERE id='{$_REQUEST['notify_id']}'";
		}
		if (empty($_REQUEST["disabled"])) { $_REQUEST["disabled"] = ""; }
		do_update("$db_cmd mon_notify SET name='{$_REQUEST['notify_name']}',
			cmd='{$_REQUEST['notify_cmd']}', disabled='{$_REQUEST['disabled']}'
			$db_end");
	} // done editing
}


if (!empty($action) && $action == "dodelete")
{
	do_update("DELETE FROM mon_notify WHERE id='{$_REQUEST['notify_id']}'");
} // done deleting


// Display a list
make_display_table("Notifications","Name","","Command","");

$test_results = do_query("SELECT * FROM mon_notify");
$test_total = mysql_num_rows($test_results);

// For each notification
for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{
	$test_row = mysql_fetch_array($test_results);
	$notify_id  = $test_row["id"];

	make_display_item($test_row["name"],"",$test_row["cmd"],"",formatted_link("Edit","{$_SERVER['PHP_SELF']}?action=edit&notify_id=$notify_id") . formatted_link("Delete", "{$_SERVER['PHP_SELF']}?action=delete&notify_id=$notify_id"), "");
} // end for

?>
</table>
<?php
} // End if no action

if (!empty($action) && ($action == "edit" || $action == "add"))
{
	// Display editing screen
	check_auth(2);
	if ($action == "add")
	{
		$notify_id = 0;
	}
	else
	{
		$notify_id = $_REQUEST["notify_id"];
	} // end if add

	$notify_results = do_query("SELECT * FROM mon_notify WHERE id=$notify_id");
	$notify_row = mysql_fetch_array($notify_results);

	make_edit_table("Edit Notificiation Method");
	make_edit_text("Name:","notify_name","25","100",$notify_row["name"]);
	make_edit_text("Command:","notify_cmd","25","200",$notify_row["cmd"]);
	echo("<tr><td>You may use keywords %dev_name, %ip, %test_name, %test_result in your command parameters</td></tr>");
	make_edit_checkbox("Disabled","disabled",$notify_row["disabled"]);
	make_edit_hidden("notify_id",$notify_id);
	make_edit_hidden("action","doedit");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

if (!empty($action) && $action == "delete")
{
// Display delete confirmation
?>
<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this notification?

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="notify_id" value="<?php echo $_REQUEST["notify_id"]; ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="submit" value="No">
</form>

<?php

} // end delete confirmation

end_page();

?>
