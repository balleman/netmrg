<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Script Test Editing Page                   #
#           mon_test.php                               #
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
js_confirm_dialog("del", "Are you sure you want to delete script test ", " ?", "$SCRIPT_NAME?action=dodelete&test_id=");

function get_data_type_name($id)
{
        switch($id)
	{
	        case 1: return "Error Code";
		case 2: return "Standard Out";
	}
}

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if ($action == "doedit") {

	if ($test_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$test_id";
	}

	do_update("$db_cmd tests_script SET name=\"$test_name\", cmd=\"$test_cmd\", data_type=$data_type, dev_type=$dev_type $db_end");
} // done editing

if ($action == "dodelete")
{
        do_update("DELETE FROM tests_script WHERE id=$test_id");
} // done deleting


// Display a list

make_display_table("Script Tests","Name","","Command","","Data Type","");

$test_results = do_query("

        SELECT  id,
	        name,
                cmd,
                data_type

	FROM tests_script

	ORDER BY name

	");

$test_total = mysql_num_rows($test_results);

for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{

        $test_row = mysql_fetch_array($test_results);
        $test_id  = $test_row["id"];

        make_display_item(	$test_row["name"],"",
			        $test_row["cmd"],"",
			        get_data_type_name($test_row["data_type"]),"",
			        formatted_link("Edit", "$SCRIPT_NAME?action=edit&test_id=$test_id") . "&nbsp;" .
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

        $test_results = do_query("SELECT * FROM tests_script WHERE id=$test_id");
        $test_row = mysql_fetch_array($test_results);
        $test_name = $test_row["name"];
        $test_cmd = $test_row["cmd"];

        make_edit_table("Edit Script Test");
        make_edit_text("Name:","test_name","25","50",$test_row["name"]);
        make_edit_text("Command:","test_cmd","75","200",$test_row["cmd"]);
	make_edit_select("Data Type:", "data_type");
        make_edit_select_option(get_data_type_name(1), 1, 1 == $test_row["data_type"]);
	make_edit_select_option(get_data_type_name(2), 2, 2 == $test_row["data_type"]);
	make_edit_select_end();
        make_edit_select_from_table("For use with this device:","dev_type","mon_device_types",$test_row["dev_type"]);
        make_edit_hidden("action","doedit");
        make_edit_hidden("test_id",$test_id);
        make_edit_submit_button();
        make_edit_end();


} // End editing screen

end_page();

?>
