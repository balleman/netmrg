<?php
/********************************************
* NetMRG Integrator
*
* groups.php
* Monitored Device Groups Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

if (!isset($_REQUEST["action"]) || ($_REQUEST["action"] == "doedit" || $_REQUEST["action"] == "dodelete" || $_REQUEST["action"] == "doadd"))
{
// Change databases if necessary and then display list

	if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "doedit" || $_REQUEST["action"] == "doadd"))
	{
		check_auth(2);
		if ($_REQUEST["action"] == "doedit")
		{
			if ($_REQUEST["grp_id"] == -1)
			{
				create_group($_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["parent_id"]);
			}
			else
			{
				update_group($_REQUEST["grp_id"], $_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["parent_id"]);
			} // end if group id
		} // end if action is to edit
	} // done editing


	if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "dodelete")
	{
		check_auth(2);
		delete_group($_REQUEST["grp_id"]);
	} // done deleting


	// Display a list
	begin_page("groups.php", "Groups");
	js_confirm_dialog("del", "Are you sure you want to delete group ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&grp_id=");
	make_display_table("Device Groups", "", 
		array("text" => "Name"),
		array("text" => "Comment")
	); // end make_display_table();

	if (!isset($_REQUEST["orderby"]))
	{ 
		$orderby = "name";
	}
	else
	{
		$orderby = $_REQUEST["orderby"];
	} // end if orderby

	if (!isset($_REQUEST["parent_id"]))
	{
		$parent_id = 0;
	}
	else
	{
		$parent_id = $_REQUEST["parent_id"];
	} // end if parent id

	$grp_select = "SELECT * FROM groups WHERE parent_id=$parent_id ORDER BY $orderby";
	$grp_results = db_query($grp_select);
	$grp_total = db_num_rows($grp_results);

	// For each group
	for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count)
	{
		$grp_row = db_fetch_array($grp_results);
		$grp_id  = $grp_row["id"];

		$child_query = db_query("SELECT id FROM groups WHERE parent_id=$grp_id");
		if (db_num_rows($child_query) > 0)
		{
			$group_link = "groups.php?parent_id=$grp_id";
		}
		else
		{
			$group_link = "devices.php?grp_id=$grp_id";
		} // end if we have children

		make_display_item("editfield".(($grp_count-1)%2),
			array("text" => $grp_row["name"], "href" => $group_link),
			array("text" => $grp_row["comment"]),
			array("text" => formatted_link("View", "view.php?object_type=group&object_id={$grp_row['id']}") . "&nbsp;" .
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&grp_id=$grp_id") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($grp_row["name"]) . "', '" . $grp_row["id"] . "')"))
		); // end make_display_item();
	} // end foreach group

?>
</table>
<?php
} // End if no action

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add"))
{
	// Display editing screen
	check_auth(2);
	begin_page("groups.php", "Groups");
	if ($_REQUEST["action"] == "add")
	{
		$grp_id = -1;
	}
	else
	{
		$grp_id = $_REQUEST["grp_id"];
	} // end if this is an add, no parent group

	$grp_results = db_query("SELECT * FROM groups WHERE id=$grp_id");
	$grp_row = db_fetch_array($grp_results);
	$grp_name = $grp_row["name"];
	$grp_comment = $grp_row["comment"];

	if ($_REQUEST["action"] == "add") { $grp_row["parent_id"] = 0; }

	make_edit_table("Edit Group");
	make_edit_text("Name:","grp_name","25","100",$grp_name);
	make_edit_text("Comment:","grp_comment","50","200",$grp_comment);
	make_edit_select_from_table("Parent:", "parent_id", "groups", $grp_row["parent_id"], "", array(0 => "-Root-"), array(), "id != '$grp_id'");
	make_edit_hidden("grp_id", $grp_id);
	make_edit_hidden("action","doedit");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
