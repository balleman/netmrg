<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Device Types Editing Page                  #
#           mon_device_types.php                       #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);


if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
check_auth(2);
# Change databases if necessary and then display list

if ($action == "doedit") {
	if ($id == 0) {
			$db_cmd = "INSERT INTO";
			$db_end = "";
		} else {
			$db_cmd = "UPDATE";
			$db_end = "WHERE id=$id";
		}
do_update("$db_cmd mon_device_types SET name=\"$name\", comment=\"$comment\" $db_end");
} # done editing

if ($action == "dodelete") {
check_auth(2);
do_update("DELETE FROM mon_device_types WHERE id=$id");
} # done deleting


# Display a list
begin_page();
js_confirm_dialog("del", "Are you sure you want to delete device type ", " ? ", "$SCRIPT_NAME?action=dodelete&id=");
make_display_table("Device Types",
				   "Name", "$SCRIPT_NAME?orderby=name",
				   "Comment", "$SCRIPT_NAME?orderby=comment");

if (!isset($orderby)) { $orderby = "name"; };

$grp_results = do_query("SELECT * FROM mon_device_types ORDER BY $orderby");
$grp_total = mysql_num_rows($grp_results);

for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count) { 
# For each group

$row = mysql_fetch_array($grp_results);
$id  = $row["id"];

make_display_item(	$row["name"],"",
			$row["comment"],"",
			formatted_link("Edit", "$SCRIPT_NAME?action=edit&id=$id") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . $row["name"] . "', '" . $row["id"] . "')"), "");
}

?>
</table>
<?
} # End if no action

/*if ($action == "add") {
# Display editing screen

make_edit_table("Add Group");
make_edit_text("Name:","grp_name","25","100","");
make_edit_text("Comment:","grp_comment","50","200","");
make_edit_hidden("action","doadd");
make_edit_submit_button();
make_edit_end();

} # End editing screen
*/
if (($action == "edit") || ($action == "add")) {
# Display editing screen
check_auth(2);
begin_page();
if ($action == "add") { $id = 0; }

$grp_results = do_query("SELECT * FROM mon_device_types WHERE id=$id");
$row = mysql_fetch_array($grp_results);
$name = $row["name"];
$comment = $row["comment"];

make_edit_table("Edit Group");
make_edit_text("Name:","name","25","100",$name);
make_edit_text("Comment:","comment","50","200",$comment);
make_edit_hidden("id",$id);
make_edit_hidden("action","doedit");
make_edit_submit_button();
make_edit_end();

} # End editing screen

end_page();

?>