<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Monitors Editing Page                      #
#           mon_monitors.php                           #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/processing.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);
begin_page();

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
# Change databases if necessary and then display list

if (!isset($graphed)) { $graphed = 0; }


if (($action == "doedit") || ($action == "doadd")) {
if ($action == "doedit") {
check_auth(2);
	if ($mon_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$mon_id";
	}
	do_update("$db_cmd mon_monitors SET device_id=$dev_id, test_id=$test_id, params=\"$cmd_params\", rrd_id=$rrd_id, graphed=$graphed, snmp_test=$snmp_test, snmp_index_type=$snmp_index_type, snmp_index_value=\"$snmp_index_value\", snmp_data=$snmp_data, disk_index_type=$disk_index_type, disk_index_value=\"$disk_index_value\", disk_data=$disk_data, mon_type=$mon_type, max_val=\"$max_val\", tuned=0 $db_end");
	#unset($dev_id);
} # done editing
}

if ($action == "dodelete") {
check_auth(2);
delete_monitor($mon_id);
} # done deleting


# Display a list

if (isset($dev_id))
{
	$device_results = do_query("SELECT * FROM mon_devices WHERE id=$dev_id");
	$device_array = mysql_fetch_array($device_results);

	$title = "Monitors for Device '" . $device_array["name"] . "'";

	$custom_add_link = "$SCRIPT_NAME?action=add&dev_id=$dev_id";

} else {

	$title = "Device Monitors";

}

js_confirm_dialog("del", "Are you sure you want to delete monitor ", " and all associated items?", "$SCRIPT_NAME?action=dodelete&dev_id=$dev_id&mon_id=");
make_display_table($title,"Monitored Device","","Type of Monitoring","",
"Graph","");

if (!isset($dev_id)) {

$mon_results = do_query("
SELECT mon_monitors.id AS id,
mon_monitors.params AS params,
mon_devices.name AS dev_name,
mon_test.name AS test_name,
mon_monitors.graphed AS graphed,
mon_monitors.max_val AS max_val
FROM mon_monitors
LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
ORDER BY dev_name,id");

} else {

$mon_results = do_query("
SELECT
mon_monitors.id AS id,
mon_monitors.params AS params,
mon_devices.name AS dev_name,
mon_test.name AS test_name,
mon_monitors.graphed AS graphed,
mon_monitors.max_val AS max_val
FROM mon_monitors
LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
WHERE device_id=$dev_id
ORDER BY id");

}

$mon_total = mysql_num_rows($mon_results);

for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count) {
# For each device

$mon_row = mysql_fetch_array($mon_results);
$mon_id  = $mon_row[0];

if ($mon_row["graphed"] == 1)
	{
		$graph_text = "View Graph";
		$graph_link = "./enclose_graph.php?type=mon&id=$mon_id";
		} else {
		$graph_text = "Not Graphed";
		$graph_link = "";
	}



make_display_item(
$mon_row["dev_name"],"./mon_events.php?mon_id=$mon_id",
get_short_monitor_name($mon_row["id"]),"",
$graph_text, $graph_link,
"Edit","$SCRIPT_NAME?action=edit&mon_id=$mon_id",
"Delete","javascript:del('" . get_monitor_name($mon_row["id"]) . "', '$mon_id')");

} # end devices

?>
</table>
<?
} # End if no action

if (($action == "edit") || ($action == "add")) {
# Display editing screen

make_edit_table("Edit Monitor");

if ($action == "edit") { 

$mon_results = do_query("       SELECT
                                mon_monitors.id                 AS id,
				mon_monitors.device_id          AS device_id,
				mon_monitors.mon_type           AS mon_type,
				mon_monitors.test_id            AS test_id,
				mon_monitors.params             AS params,
				mon_monitors.snmp_test          AS snmp_test,
				mon_monitors.snmp_index_type    AS snmp_index_type,
				mon_monitors.snmp_index_value   AS snmp_index_value,
				mon_monitors.snmp_data          AS snmp_data,
				mon_monitors.disk_index_type    AS disk_index_type,
				mon_monitors.disk_index_value   AS disk_index_value,
				mon_monitors.disk_data          AS disk_data,
				mon_monitors.graphed            AS graphed,
				mon_monitors.rrd_id             AS rrd_id,
				mon_monitors.max_val            AS max_val,
				mon_devices.id                  AS device_dev_id,
				mon_devices.name                AS device_name
                                FROM mon_monitors
                                LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
                                WHERE mon_monitors.id=$mon_id
                        ");
$mon_row = mysql_fetch_array($mon_results);

} else {

$mon_id = 0;

$mon_row["mon_type"] = 1;
$mon_row["test_id"] = 1;
$mon_row["snmp_test"] = 1;
$mon_row["snmp_index_type"] = 1;
$mon_row["snmp_data"] = 1;
$mon_row["disk_index_type"] = 1;
$mon_row["disk_data"] = 1;

}

if (isset($dev_id))
{
	$mon_row["device_id"] = $dev_id;
	$device_query_handle = do_query("SELECT name FROM mon_devices WHERE id=$dev_id");
	$device_query_row = mysql_fetch_array($device_query_handle);
	$mon_row["device_name"] = $device_query_row["name"];
	$dev_thingy = "&dev_id=$dev_id";
} else {
	$dev_thingy = "";
}

make_edit_group("General Parameters");

if (isset($edit_device))
{
        make_edit_select_from_table("Device:","dev_id","mon_devices",$mon_row["device_id"]);
} else {
        make_edit_label("<big><b>Device:</b><br>  " . $mon_row["device_name"] . "  [<a href='$SCRIPT_NAME?mon_id=$mon_id&action=$action" . $dev_thingy . "&type=$type&edit_device=1'>change</a>]</big>");
	make_edit_hidden("dev_id", $mon_row["device_id"]);
}

echo("
	<script type='text/javascript'>

	function redisplay(selectedIndex)
	{
                window.location = '$SCRIPT_NAME?mon_id=$mon_id&action=$action" . $dev_thingy . "&type=' + selectedIndex;
	}
	</script>
");

if (isset($type))
{
	switch ($type)
	{
	        case 0: $type = 4; break;
                case 1: $type = 3; break;
		case 2: $type = 1; break;
		case 3: $type = 2; break;
        }

	$mon_row["mon_type"] = $type;
}
make_edit_select_from_table("Monitoring Type:","mon_type", "mon_types",$mon_row["mon_type"], "", "onChange='redisplay(this.selectedIndex);'");

if ($mon_row["mon_type"] == 1)
{
        make_edit_group("Script Options");
        make_edit_select_from_table("Type of Test:","test_id","mon_test",$mon_row["test_id"]);
        make_edit_text("Command Parameters:","cmd_params",50,100,$mon_row["params"]);
} else {
	make_edit_hidden("test_id", $mon_row["test_id"]);
	make_edit_hidden("cmd_params", $mon_row["params"]);
}

if ($mon_row["mon_type"] == 2)
{
        make_edit_group("SNMP Options");
        make_edit_select_from_table("SNMP Test:","snmp_test","snmp_tests",$mon_row["snmp_test"]);
} else {
	make_edit_hidden("snmp_test", $mon_row["snmp_test"]);
}

if ($mon_row["mon_type"] == 3)
{
        make_edit_group("Network Interface Options");
        make_edit_select_from_table("Index Type:","snmp_index_type","snmp_index",$mon_row["snmp_index_type"]);
        make_edit_text("Index Value:","snmp_index_value","50","200",$mon_row["snmp_index_value"]);
        make_edit_select_from_table("Data Type:","snmp_data","snmp_data",$mon_row["snmp_data"]);
} else {
	make_edit_hidden("snmp_index_type", $mon_row["snmp_index_type"]);
	make_edit_hidden("snmp_index_value", $mon_row["snmp_index_value"]);
	make_edit_hidden("snmp_data", $mon_row["snmp_data"]);
}

if ($mon_row["mon_type"] == 4)
{
        make_edit_group("Disk Options");
        make_edit_select_from_table("Index Type:","disk_index_type","disk_index",$mon_row["disk_index_type"]);
        make_edit_text("Index Value:","disk_index_value","50","200",$mon_row["disk_index_value"]);
        make_edit_select_from_table("Data Type:","disk_data","disk_data",$mon_row["disk_data"]);
} else {
	make_edit_hidden("disk_index_type", $mon_row["disk_index_type"]);
	make_edit_hidden("disk_index_value", $mon_row["disk_index_value"]);
	make_edit_hidden("disk_data", $mon_row["disk_data"]);
}

make_edit_group("Graphing Options");
make_edit_checkbox("Graph this monitor","graphed",$mon_row["graphed"]);
make_edit_select_from_table("RRD Type:","rrd_id","graph_dst",$mon_row["rrd_id"]);
make_edit_text("Maximum Value:", "max_val", "10", "20", $mon_row["max_val"]);

make_edit_hidden("action","doedit");
make_edit_hidden("mon_id",$mon_id);

make_edit_submit_button();
make_edit_end();

} # End editing screen

end_page();

?>
