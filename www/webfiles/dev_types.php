<?php
/********************************************
* NetMRG Integrator
*
* dev_types.php
* Device Types Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["ReadAll"]);


if ((!isset($_REQUEST["action"])) || ($_REQUEST["action"] == "doedit") || ($_REQUEST["action"] == "dodelete") || ($_REQUEST["action"] == "doadd"))
{
	check_auth($GLOBALS['PERMIT']["ReadWrite"]);

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit")
	{
		if ($_REQUEST["id"] == 0)
		{
			$db_cmd = "INSERT INTO";
			$db_end = "";
		}
		else
		{
			$db_cmd = "UPDATE";
			$db_end = "WHERE id={$_REQUEST['id']}";
		} // end if id is 0 or not
		db_update("$db_cmd dev_types SET name='{$_REQUEST['name']}', comment='{$_REQUEST['comment']}' $db_end");
	} // done editing

	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "dodelete")
	{
		db_update("DELETE FROM dev_types WHERE id={$_REQUEST['id']}");
	} // done deleting


	# Display a list
	begin_page("dev_types.php", "Device Types");
	js_confirm_dialog("del", "Are you sure you want to delete device type ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&id=");
	make_display_table("Device Types", "",
		array("text" => "Name", "href" => "{$_SERVER['PHP_SELF']}?orderby=name"),
		array("text" => "Comment", "href" => "{$_SERVER['PHP_SELF']}?orderby=comment")
	); // end make_display_table();

	if (!isset($_REQUEST["orderby"]))
	{
		$orderby = "name";
	}
	else
	{
		$orderby = $_REQUEST["orderby"];
	} // end if orderby

	$grp_results = db_query("SELECT * FROM dev_types ORDER BY $orderby");
	$grp_total = db_num_rows($grp_results);

	# For each group
	for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count)
	{ 
		$row = db_fetch_array($grp_results);
		$id  = $row["id"];

		make_display_item("editfield".(($grp_count-1)%2),
			array("text" => $row["name"]),
			array("text" => $row["comment"]),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id=$id") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($row["name"]) . "', '" . $row["id"] . "')"))
		); // end make_display_item();
	} // end foreach group

?>
</table>
<?php
} // End if no action

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add"))
{
	// Display editing screen
	check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	begin_page("dev_types.php", "Device Types");
	if ($_REQUEST["action"] == "add")
	{
		$id = 0;
	}
	else
	{
		$id = $_REQUEST["id"];
	} // end if id

	$grp_results = db_query("SELECT * FROM dev_types WHERE id=$id");
	$row = db_fetch_array($grp_results);
	$name = $row["name"];
	$comment = $row["comment"];

	make_edit_table("Edit Group");
	make_edit_text("Name:","name","25","100",$name);
	make_edit_text("Comment:","comment","50","200",$comment);
	make_edit_hidden("id",$id);
	make_edit_hidden("action","doedit");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
