<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           SNMP Test Editing Page                     #
#           mon_test_snmp.php                          #
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
js_confirm_dialog("del", "Are you sure you want to delete SNMP test ", " ? ", "$SCRIPT_NAME?action=dodelete&test_id=");

if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
}

if ((empty($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
// Change databases if necessary and then display list

if ($action == "doedit") {

	if ($test_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id={$_REQUEST["test_id"]}";
	}

	do_update("$db_cmd tests_snmp SET name=\"{$_REQUEST["test_name"]}\", oid=\"{$_REQUEST["test_oid"]}\", dev_type={$_REQUEST["dev_type"]} $db_end");
} // done editing

if ($action == "dodelete")
{
        do_update("DELETE FROM tests_snmp WHERE id={$_REQUEST["test_id"]}");

} // done deleting


// Display a list

make_display_table("SNMP Tests","Name","","OID","");

$test_results = do_query("SELECT * FROM tests_snmp ORDER BY name");
$test_total = mysql_num_rows($test_results);

for ($test_count = 1; $test_count <= $test_total; ++$test_count)
{
        // For each test

        $test_row = mysql_fetch_array($test_results);


        make_display_item(	$test_row["name"],"",
	        		$test_row["oid"],"",
		        	formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&test_id=" . $test_row["id"]) . "&nbsp;" .
			        formatted_link("Delete", "javascript:del('" . $test_row["name"] . "', '" . $test_row["id"] . "')"), "");


} // end tests

?>
</table>
<?
} // End if no action

if (($action == "edit") || ($action == "add"))
{
        // Display editing screen

        if ($action == "add")
	{
		$_REQUEST["test_id"] = 0;
	}

        $test_results = do_query("SELECT * FROM tests_snmp WHERE id={$_REQUEST["test_id"]}");
        $test_row = mysql_fetch_array($test_results);

	make_edit_table("Edit SNMP Test");
        make_edit_text("Name:","test_name","25","50",$test_row["name"]);
        make_edit_text("SNMP OID:","test_oid","75","200",$test_row["oid"]);
        make_edit_select_from_table("For use with this device:","dev_type","mon_device_types",$test_row["dev_type"]);
        make_edit_hidden("action","doedit");
        make_edit_hidden("test_id",$_REQUEST["test_id"]);
        make_edit_submit_button();
        make_edit_end();


} // End editing screen

end_page();

?>
