<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Event Notification Editing Page            #
#           mon_notify.php                             #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);
begin_page();

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{
# Change databases if necessary and then display list

/*if ($action == "doadd") {
do_update("INSERT INTO mon_notify SET name=\"$test_name\", cmd=\"$test_cmd\"");
} # done adding

if ($action == "doedit") {
do_update("UPDATE mon_notify SET name=\"$test_name\", cmd=\"$test_cmd\" WHERE id=$notify_id");
} # done editing*/

if (($action == "doedit") || ($action == "doadd"))
{
	check_auth(2);
	if (!isset($disabled)) { $disabled = 0; }
	if ($action == "doedit")
	{
		if ($notify_id == 0)
		{
			$db_cmd = "INSERT INTO";
			$db_end = "";
		}
		else
		{
			$db_cmd = "UPDATE";
			$db_end = "WHERE id=$notify_id";
		}
		do_update("$db_cmd mon_notify SET name=\"$notify_name\", cmd=\"$notify_cmd\", disabled=$disabled $db_end");
	} # done editing
}


if ($action == "dodelete")
{
	do_update("DELETE FROM mon_notify WHERE id=$notify_id");
} # done deleting


# Display a list

make_display_table("Notifications","Name","","Command","");

$test_results = do_query("SELECT * FROM mon_notify");
$test_total = mysql_num_rows($test_results);

# For each device
for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{ 

$test_row = mysql_fetch_array($test_results);
$notify_id  = $test_row["id"];

make_display_item($test_row["name"],"",$test_row["cmd"],"","Edit","$SCRIPT_NAME?action=edit&notify_id=$notify_id","Delete","$SCRIPT_NAME?action=delete&notify_id=$notify_id");

} # end testices

?>
</table>
<?
} # End if no action

if (($action == "edit") || ($action == "add"))
{
	# Display editing screen
	check_auth(2);
	if ($action == "add") { $notify_id = 0; }

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

} # End editing screen

if ($action == "delete")
{
# Display delete confirmation
?>
<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this notification?

<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="notify_id" value="<? print("$notify_id"); ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="No">
</form>

<?

} # end delete confirmation

end_page();

?>
