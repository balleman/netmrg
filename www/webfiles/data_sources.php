<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Custom Graphs Data Sources Page            #
#           data_sources.php                           #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
require_once(netmrg_root() . "lib/processing.php");
check_auth();
begin_page();

if ($action == "doadd") {
do_update("INSERT INTO graph_ds SET name=\"$ds_name\", comment=\"$ds_comment\", xsize=$xsize, ysize=$ysize, vert_label=\"$vert_label\"");
} # done adding

if ($action == "doedit") {
do_update("UPDATE graph_ds SET name=\"$ds_name\", comment=\"$ds_comment\", xsize=$xsize, ysize=$ysize, vert_label=\"$vert_label\" WHERE id=$ds_id");
} # done editing

if ($action == "dodelete") {
check_auth(2);
delete_ds($ds_id);
} # done deleting

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
# Change databases if necessary and then display list

make_display_table("Custom Graph Data Sources","Name","","Source","");

$ds_results = do_query("SELECT * FROM graph_ds");
$ds_total = mysql_num_rows($ds_results);

for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count) { 
# For each group

$ds_row = mysql_fetch_array($ds_results);
$ds_id  = $ds_row["id"];

make_display_item($ds_row["name"],"",
				  $ds_row["comment"],"",
				  "View ds","./enclose_ds.php?type=custom&id=" . $ds_row["id"],
				  "Edit", "$SCRIPT_NAME?action=edit&ds_id=$ds_id",
				  "Delete", "$SCRIPT_NAME?action=delete&ds_id=$ds_id");
				  

} # end groups


?></table><?

} # end no action

if ($action == "add") {
# Display editing screen

make_edit_table("Add ds");
make_edit_text("Name:","ds_name","25","100","");
make_edit_text("Comment:","ds_comment","50","200","");
make_edit_text("X Size","xsize","4","4","400");
make_edit_text("Y Size","ysize","4","4","100");
make_edit_text("Vertical Label","vert_label","50","100","");
make_edit_hidden("action","doadd");
make_edit_submit_button();
make_edit_end();

} # End editing screen

if ($action == "edit") {
# Display editing screen

$ds_results = do_query("SELECT * FROM dss WHERE id=$ds_id");
$ds_row = mysql_fetch_array($ds_results);
$ds_name = $ds_row["name"];
$ds_comment = $ds_row["comment"];

make_edit_table("Edit Group");
make_edit_text("Name:","ds_name","25","100",$ds_name);
make_edit_text("Comment:","ds_comment","50","200",$ds_comment);
make_edit_text("X Size","xsize","4","4",$ds_row["xsize"]);
make_edit_text("Y Size","ysize","4","4",$ds_row["ysize"]);
make_edit_text("Vertical Label","vert_label","50","100",$ds_row["vert_label"]);
make_edit_hidden("ds_id",$ds_id);
make_edit_hidden("action","doedit");
make_edit_submit_button();
make_edit_end();

} # End editing screen

if ($action == "delete") {
# Display delete confirmation

?>

<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this ds?

<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="ds_id" value="<? print("$ds_id"); ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="No">
</form>

<?

} # end delete confirmation

end_page();

?>