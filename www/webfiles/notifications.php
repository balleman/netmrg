<?php
/********************************************
* NetMRG Integrator
*
* mon_notify.php
* Event Notification Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
	unset($action);
} // end if action is set or not


begin_page("notifications.php", "Notifications");
js_confirm_dialog("del", "Are you sure you want to delete notification ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&id=");

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if (!empty($action) && ($action == "doedit" || $action == "doadd"))
{
	check_auth(2);
	if (!isset($disabled)) { $disabled = 0; }
	if ($action == "doedit")
	{
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
		do_update("$db_cmd notifications SET name='{$_REQUEST['name']}',
			command='{$_REQUEST['command']}', disabled='{$_REQUEST['disabled']}'
			$db_end");

		header("Location: {$_SERVER['PHP_SELF']}");
		exit(0);
	} // done editing
}

if (!empty($action) && $action == "dodelete")
{
	do_update("DELETE FROM notifications WHERE id='{$_REQUEST['id']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit(0);
} // done deleting

// Display a list
make_display_table("Notifications", "", 
	array("text" => "Name"),
	array("text" => "Command")
); // end make_display_table();

$res = do_query("SELECT * FROM notifications");

// For each notification
for ($i = 0; $i < mysql_num_rows($res); $i++)
{
	$row = mysql_fetch_array($res);

	make_display_item("editfield".($i%2),
		array("text" => $row["name"]),
		array("text" => $row["command"]),
		array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('".addslashes($row['name'])."', '{$row['id']}')"))
	); // end make_display_item();
}

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
		$id = 0;
	}
	else
	{
		$id = $_REQUEST["id"];
	} // end if add

	$res = do_query("SELECT * FROM notifications WHERE id=$id");
	$row = mysql_fetch_array($res);

	make_edit_table("Edit Notificiation Method");
	make_edit_text("Name:", "name", "50", "100", $row["name"]);
	make_edit_text("Command:", "command", "50","200", $row["command"]);
	echo("<tr><td>You may use keywords %dev_name, %ip, %test_name, %test_result in your command parameters</td></tr>");
	make_edit_checkbox("Disabled", "disabled", $row["disabled"]);
	make_edit_hidden("id", $id);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
