<?php
/********************************************
* NetMRG Integrator
*
* tests_script.php
* Script Test Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);
begin_page("tests_scripts.php", "Scripts - Tests");
js_confirm_dialog("del", "Are you sure you want to delete script test ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

function get_data_type_name($id)
{
	switch($id)
	{
		case 1: return "Error Code";
		case 2: return "Standard Out";
	} // end switch id
} // end get_data_type_name()


if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
}


if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if ($action == "doedit")
{

	if ($_REQUEST["test_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id={$_REQUEST["test_id"]}";
	}

	db_update("$db_cmd tests_script SET name=\"{$_REQUEST["test_name"]}\", cmd=\"{$_REQUEST["test_cmd"]}\", data_type={$_REQUEST["data_type"]}, dev_type={$_REQUEST["dev_type"]} $db_end");
} // done editing

if ($action == "dodelete")
{
        db_update("DELETE FROM tests_script WHERE id={$_REQUEST["test_id"]}");

} // done deleting


// Display a list

make_display_table("Script Tests", "", 
	array("text" => "Name"),
	array("text" => "Command"),
	array("text" => "Data Type")
); // end make_display_table();

$test_results = db_query("
	SELECT  id,
	name,
	cmd,
	data_type
	FROM tests_script
	ORDER BY name
"); // end test_results

$test_total = db_num_rows($test_results);

for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{

        $test_row = db_fetch_array($test_results);
        $test_id  = $test_row["id"];

        make_display_item("editfield".(($test_count-1)%2),
		array("text" => $test_row["name"]),
		array("text" => $test_row["cmd"]),
		array("text" => get_data_type_name($test_row["data_type"])),
		array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=$test_id") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . addslashes($test_row["name"]) . "', '" . $test_row["id"] . "')"))
	); // end make_display_item();
} // end tests

?>
</table>
<?php
} // End if no action


// Display editing screen
if (($action == "edit") || ($action == "add"))
{

	if ($action == "add")
	{
		$_REQUEST["test_id"] = 0;
	}

	$test_results = db_query("SELECT * FROM tests_script WHERE id={$_REQUEST["test_id"]}");
	$test_row = db_fetch_array($test_results);
	$test_name = $test_row["name"];
	$test_cmd = $test_row["cmd"];

	make_edit_table("Edit Script Test");
	make_edit_text("Name:","test_name","25","50",$test_row["name"]);
	make_edit_text("Command:","test_cmd","75","200",$test_row["cmd"]);
	make_edit_select("Data Type:", "data_type");
	make_edit_select_option(get_data_type_name(1), 1, 1 == $test_row["data_type"]);
	make_edit_select_option(get_data_type_name(2), 2, 2 == $test_row["data_type"]);
	make_edit_select_end();
	make_edit_select_from_table("For use with this device:","dev_type","dev_types",$test_row["dev_type"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("test_id",$_REQUEST["test_id"]);
	make_edit_submit_button();
	make_edit_end();


} // End editing screen

end_page();

?>
