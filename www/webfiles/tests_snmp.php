<?php
/********************************************
* NetMRG Integrator
*
* tests_snmp.php
* SNMP Test Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "";
}
// compatibility
$action = $_REQUEST["action"];

// if no action (list), and do inserts/updates/deletes
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
		$db_end = "WHERE id={$_REQUEST["test_id"]}";
	}

	$_REQUEST['test_name'] = db_escape_string(fix_magic_quotes($_REQUEST['test_name']));
	$_REQUEST['test_oid'] = db_escape_string(fix_magic_quotes($_REQUEST['test_oid']));

	db_update("$db_cmd tests_snmp SET name=\"{$_REQUEST["test_name"]}\", oid=\"{$_REQUEST["test_oid"]}\", dev_type={$_REQUEST["dev_type"]} $db_end");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done editing

if ($action == "dodelete")
{
	db_update("DELETE FROM tests_snmp WHERE id={$_REQUEST["test_id"]}");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done deleting

/** start page **/
begin_page("tests_snmp.php", "SNMP - Tests");
js_confirm_dialog("del", "Are you sure you want to delete SNMP test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

// Display a list

make_display_table("SNMP Tests", "",
	array("text" => "Name"),
	array("text" => "OID")
); // end make_display_table();

$test_results = db_query("SELECT * FROM tests_snmp ORDER BY name");
$test_total = db_num_rows($test_results);

// For each test
for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{
	$test_row = db_fetch_array($test_results);

	make_display_item("editfield".(($test_count-1)%2),
		array("text" => htmlspecialchars($test_row["name"])),
		array("text" => htmlspecialchars($test_row["oid"])),
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
	begin_page("tests_snmp.php", "SNMP - Tests");
	js_confirm_dialog("del", "Are you sure you want to delete SNMP test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");

	if ($action == "add")
	{
		$_REQUEST["test_id"] = 0;
	}

	$test_results = db_query("SELECT * FROM tests_snmp WHERE id={$_REQUEST["test_id"]}");
	$test_row = db_fetch_array($test_results);

	make_edit_table("Edit SNMP Test");
	make_edit_text("Name:", "test_name", "25", "50", htmlspecialchars($test_row["name"]));
	make_edit_text("SNMP OID:", "test_oid", "75", "200", htmlspecialchars($test_row["oid"]));
	make_edit_select_from_table("For use with this device:","dev_type","dev_types",$test_row["dev_type"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("test_id",$_REQUEST["test_id"]);
	make_edit_submit_button();
	make_edit_end();

	end_page();

} // End editing screen


?>
