<?php
/********************************************
* NetMRG Integrator
*
* tests_sql.php
* SQL Test Editing Page
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["ReadWrite"]);

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "";
}
// compatibility
$action = $_REQUEST["action"];

if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd") || ($action == "multidodelete"))
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
		$db_end = "WHERE id='{$_REQUEST['test_id']}'";
	}
	
	$_REQUEST['column_num'] = $_REQUEST['column_num'] * 1;
	
	db_update("$db_cmd tests_sql SET name='{$_REQUEST['test_name']}',
		sub_dev_type='{$_REQUEST['dev_type']}', host='{$_REQUEST['host']}',
		user='{$_REQUEST['sql_user']}', password='{$_REQUEST['sql_password']}',
		query='{$_REQUEST['query']}', column_num='{$_REQUEST['column_num']}',
		timeout='{$_REQUEST['timeout']}' 
		$db_end");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done editing

if ($action == "dodelete")
{
	db_update("DELETE FROM tests_sql WHERE id='{$_REQUEST['test_id']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit();
} // done deleting

if ($action == "multidodelete")
{
	if (isset($_REQUEST['test']))
	{
		while (list($key,$value) = each($_REQUEST["test"]))
		{
			db_update("DELETE FROM tests_sql WHERE id='$key'");
		}
	}
	Header("Location: {$_SERVER['PHP_SELF']}");
	exit();
}

/** start page **/
begin_page("tests_sql.php", "SQL - Tests");
js_checkbox_utils();
js_confirm_dialog("del", "Are you sure you want to delete SQL test ", " ? ", "{$_SERVER['PHP_SELF']}?action=dodelete&test_id=");
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
<input type="hidden" name="action" value="">
<?php

// Display a list

make_display_table("SQL Tests", "",
	array("text" => checkbox_toolbar()),
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
		array("checkboxname" => "test", "checkboxid" => $test_row['id']),
		array("text" => htmlspecialchars($test_row["name"])),
		array("text" => htmlspecialchars($test_row["host"])),
		array("text" => htmlspecialchars($test_row["user"])),
		array("text" => htmlspecialchars(paraphrase($test_row["query"],75))),
		array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=" . $test_row["id"], "", "edit") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . addslashes(htmlspecialchars($test_row["name"])) . "', '" . $test_row["id"] . "')", "", "delete"))
	); // end make_display_item();
} // end tests

	make_checkbox_command("", 6,
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked SQL tests?")
	); // end make_checkbox_command
	make_status_line("SQL test", $test_count - 1);
?>
</table>
</form>
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

	$test_results = db_query("SELECT * FROM tests_sql WHERE id='{$_REQUEST['test_id']}'");
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
	make_edit_text("Timeout (seconds):", "timeout", "2", "4", $test_row["timeout"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("test_id",$_REQUEST["test_id"]);
	make_edit_submit_button();
	make_edit_end();

	end_page();

} // End editing screen


?>
