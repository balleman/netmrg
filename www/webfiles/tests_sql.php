<?php
/********************************************
* NetMRG Integrator
*
* tests_sql.php
* SQL Test Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadWrite"]);

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "";
}
// compatibility
$action = $_REQUEST["action"];

if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{
// Change databases if necessary and then display list

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
		$db_end = "WHERE id={$_REQUEST['test_id']}";
	}
	$_REQUEST['test_name'] = db_escape_string(fix_magic_quotes($_REQUEST['test_name']));
	$_REQUEST['host'] = db_escape_string(fix_magic_quotes($_REQUEST['host']));
	$_REQUEST['sql_user'] = db_escape_string(fix_magic_quotes($_REQUEST['sql_user']));
	$_REQUEST['sql_password'] = db_escape_string(fix_magic_quotes($_REQUEST['sql_password']));
	$_REQUEST['query'] = db_escape_string(fix_magic_quotes($_REQUEST['query']));
	$_REQUEST['column_num'] = $_REQUEST['column_num'] * 1;


	db_update("$db_cmd tests_sql SET name='{$_REQUEST['test_name']}',
		sub_dev_type={$_REQUEST['dev_type']}, host='{$_REQUEST['host']}',
		user='{$_REQUEST['sql_user']}', password='{$_REQUEST['sql_password']}',
		query='{$_REQUEST['query']}', column_num='{$_REQUEST['column_num']}'
		$db_end");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done editing

if ($action == "dodelete")
{
	db_update("DELETE FROM tests_sql WHERE id={$_REQUEST['test_id']}");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done deleting

/** start page **/
begin_page("tests_sql.php", "SQL - Tests");
js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

// Display a list

make_display_table("SQL Tests", "",
	array("text" => "Name"),
	array("text" => "Host"),
	array("text" => "User"),
	array("text" => "Query")
); // end make_display_table();

$test_results = db_query("SELECT * FROM tests_sql ORDER BY name");
$test_total = db_num_rows($test_results);

// For each test
for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{
	$test_row = db_fetch_array($test_results);

	make_display_item("editfield".(($test_count-1)%2),
		array("text" => htmlspecialchars($test_row["name"])),
		array("text" => htmlspecialchars($test_row["host"])),
		array("text" => htmlspecialchars($test_row["user"])),
		array("text" => htmlspecialchars(paraphrase($test_row["query"],75))),
		array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=" . $test_row["id"]) . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . addslashes(htmlspecialchars($test_row["name"])) . "', '" . $test_row["id"] . "')"))
	); // end make_display_item();
} // end tests

?>
</table>
<?php
	end_page();
} // End if no action


// Display editing screen
if (($action == "edit") || ($action == "add"))
{
	/** start page **/
	begin_page("tests_sql.php", "SQL - Tests");
	js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

	if ($action == "add")
	{
		$_REQUEST["test_id"] = 0;
	}

	$test_results = db_query("SELECT * FROM tests_sql WHERE id={$_REQUEST['test_id']}");
	$test_row = db_fetch_array($test_results);

	make_edit_table("Edit SQL Test");
	make_edit_group("General");
	make_edit_text("Name:", "test_name", "25", "50", htmlspecialchars($test_row["name"]));
	make_edit_select_from_table("For use with this device:", "dev_type", "dev_types", $test_row["sub_dev_type"]);
	make_edit_group("SQL");
	make_edit_text("Host:", "host", "75", "200", htmlspecialchars($test_row["host"]));
	make_edit_text("User:", "sql_user", "75", "200", htmlspecialchars($test_row["user"]));
	make_edit_text("Password:", "sql_password", "75", "200", htmlspecialchars($test_row["password"]));
	make_edit_text("Query:", "query", "75", "255", htmlspecialchars($test_row["query"]));
	make_edit_text("Column Number:", "column_num", "2", "4", $test_row["column_num"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("test_id",$_REQUEST["test_id"]);
	make_edit_submit_button();
	make_edit_end();

	end_page();

} // End editing screen


?>
