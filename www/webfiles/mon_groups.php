<?php
########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Monitored Device Groups Editing Page       #
#           mon_groups.php                             #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

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
	begin_page("mon_groups.php", "Groups");
	js_confirm_dialog("del", "Are you sure you want to delete group ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&grp_id=");
	make_display_table("Device Groups",
	   "Name", "{$_SERVER['PHP_SELF']}?orderby=name",
	   "Comment", "{$_SERVER['PHP_SELF']}?orderby=comment");

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

	$grp_select = "SELECT * FROM mon_groups WHERE parent_id=$parent_id ORDER BY $orderby";
	$grp_results = do_query($grp_select);
	$grp_total = mysql_num_rows($grp_results);

	// For each group
	for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count)
	{
		$grp_row = mysql_fetch_array($grp_results);
		$grp_id  = $grp_row["id"];

		$child_query = do_query("SELECT id FROM mon_groups WHERE parent_id=$grp_id");
		if (mysql_num_rows($child_query) > 0)
		{
			$group_link = "mon_groups.php?parent_id=$grp_id";
		}
		else
		{
			$group_link = "mon_devices.php?grp_id=$grp_id";
		} // end if we have children

		make_display_item($grp_row["name"], $group_link,
			$grp_row["comment"],"",
			formatted_link("View", "view.php?pos_id_type=0&pos_id={$grp_row['id']}") . "&nbsp;" .
			formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&grp_id=$grp_id") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . $grp_row["name"] . "', '" . $grp_row["id"] . "')"), "");
	} // end foreach group

?>
</table>
<?php
} // End if no action

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add"))
{
	// Display editing screen
	check_auth(2);
	begin_page("mon_groups.php", "Groups");
	if ($_REQUEST["action"] == "add")
	{
		$grp_id = -1;
	}
	else
	{
		$grp_id = $_REQUEST["grp_id"];
	} // end if this is an add, no parent group

	$grp_results = do_query("SELECT * FROM mon_groups WHERE id=$grp_id");
	$grp_row = mysql_fetch_array($grp_results);
	$grp_name = $grp_row["name"];
	$grp_comment = $grp_row["comment"];

	if ($_REQUEST["action"] == "add") { $grp_row["parent_id"] = 0; }

	make_edit_table("Edit Group");
	make_edit_text("Name:","grp_name","25","100",$grp_name);
	make_edit_text("Comment:","grp_comment","50","200",$grp_comment);
	make_edit_select_from_table("Parent:", "parent_id", "mon_groups", $grp_row["parent_id"], "", array(0 => "-Root-"));
	make_edit_hidden("grp_id", $grp_id);
	make_edit_hidden("action","doedit");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
