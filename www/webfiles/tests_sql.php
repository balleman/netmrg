<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           SQL Test Editing Page                      #
#           tests_sql.php                              #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);
begin_page();
js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "$SCRIPT_NAME?action=dodelete&test_id=");

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
// Change databases if necessary and then display list

if ($action == "doedit") {

	if ($test_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$test_id";
	}

	do_update("$db_cmd tests_sql SET name=\"$test_name\", sub_dev_type=$dev_type, host=\"$host\", user=\"$sql_user\", password=\"$sql_password\", query=\"$query\", column_num=$column_num $db_end");
} // done editing

if ($action == "dodelete")
{
        do_update("DELETE FROM tests_sql WHERE id=$test_id");
} // done deleting


// Display a list

make_display_table("SQL Tests", "Name", "", "Host", "", "User", "", "Query", "");

$test_results = do_query("SELECT * FROM tests_sql ORDER BY name");
$test_total = mysql_num_rows($test_results);

for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{
        // For each test

        $test_row = mysql_fetch_array($test_results);


        make_display_item(	$test_row["name"], "",
	        		$test_row["host"], "",
				$test_row["user"], "",
				$test_row["query"], "",
		        	formatted_link("Edit", "$SCRIPT_NAME?action=edit&test_id=" . $test_row["id"]) . "&nbsp;" .
			        formatted_link("Delete", "javascript:del('" . $test_row["name"] . "', '" . $test_row["id"] . "')"), "");


} // end tests

?>
</table>
<?
} // End if no action

if (($action == "edit") || ($action == "add"))
{
        // Display editing screen

        if ($action == "add") { $test_id = 0; }

        $test_results = do_query("SELECT * FROM tests_sql WHERE id=$test_id");
        $test_row = mysql_fetch_array($test_results);

	make_edit_table("Edit SQL Test");
        make_edit_group("General");
	make_edit_text("Name:","test_name","25","50",$test_row["name"]);
	make_edit_select_from_table("For use with this device:","dev_type","mon_device_types",$test_row["dev_type"]);
        make_edit_group("SQL");
	make_edit_text("Host:", "host", "75", "200", $test_row["host"]);
	make_edit_text("User:", "sql_user", "75", "200", $test_row["user"]);
	make_edit_text("Password:", "sql_password", "75", "200", $test_row["password"]);
	make_edit_text("Query:", "query", "75", "255", htmlspecialchars($test_row["query"]));
	make_edit_text("Column Number:", "column_num", "2", "4", $test_row["column_num"]);
        make_edit_hidden("action","doedit");
        make_edit_hidden("test_id",$test_id);
        make_edit_submit_button();
        make_edit_end();


} // End editing screen

end_page();

?>
