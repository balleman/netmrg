<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Monitored Devices Editing Page             #
#           mon_devices.php                            #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
require_once(netmrg_root() . "lib/processing.php");
check_auth(1);

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd") || ($action =="doaddtogrp")) {

if (($action == "doedit") || ($action == "doadd")) {
check_auth(2);
if ($action == "doedit") {
	if ($dev_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$dev_id";
	}
	if (!isset($snmp_recache)) { $snmp_recache = 0; }
	if (!isset($disabled)) { $disabled = 0; }
	if (!isset($snmp_check_ifnumber)) { $snmp_check_ifnumber = 0; }
        if (!isset($snmp_enabled)) { $snmp_enabled = 0; }
	do_update("$db_cmd mon_devices SET 
	                name=\"$dev_name\",
                        ip=\"$dev_ip\",
                        snmp_read_community=\"$snmp_read_community\", 
			dev_type=$dev_type, 
			snmp_recache=$snmp_recache, 
			disabled=$disabled, 
			snmp_check_ifnumber=$snmp_check_ifnumber,
			snmp_enabled=$snmp_enabled
			$db_end");

	if ($dev_id == 0)
	{
		do_update("INSERT INTO dev_parents SET grp_id=$grp_id, dev_id=" . mysql_insert_id());
	}

} # done editing
}

if ($action == "doaddtogrp")
{
	check_auth(2);
	do_update("INSERT INTO dev_parents SET grp_id=$grp_id, dev_id=$dev_id");
}

if ($action == "dodelete")
{
	check_auth(2);
	delete_device($dev_id);
} # done deleting


# Display a list

if (isset($grp_id)) {

$group_results = do_query("SELECT * FROM mon_groups WHERE id=$grp_id");
$group_array = mysql_fetch_array($group_results);

$title = "Monitored Devices in Group '" . $group_array["name"] . "'";

$custom_add_link = "$SCRIPT_NAME?action=add&grp_id=$grp_id";

} else {

$title = "Monitored Devices";

}
begin_page();
js_confirm_dialog("del", "Are you sure you want to delete device ", " and all associated items?", "$SCRIPT_NAME?action=dodelete&grp_id=$grp_id&dev_id=");
make_display_table($title,
				   "Name", "$SCRIPT_NAME?orderby=name",
				   "SNMP Options","");

if (!isset($orderby)) { $orderby = "name"; };

if (!(isset($grp_id))) {

$dev_results = do_query("
SELECT mon_devices.name AS name, mon_devices.ip, mon_devices.id
FROM mon_devices
ORDER BY $orderby");

} else {

$dev_results = do_query("
SELECT mon_devices.name AS name, mon_devices.ip, mon_devices.id 
FROM dev_parents
LEFT JOIN mon_devices ON dev_parents.dev_id=mon_devices.id 
WHERE grp_id=$grp_id
ORDER BY $orderby");

}

$dev_total = mysql_num_rows($dev_results);

for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count) {
# For each device

$dev_row = mysql_fetch_array($dev_results);
$dev_id  = $dev_row["id"];

make_display_item(	$dev_row["name"],"./sub_devices.php?dev_id=$dev_id",
			formatted_link("View Interface Cache", "snmp_cache_view.php?dev_id=$dev_id") . "&nbsp;" .
			formatted_link("Recache Interfaces", "recache.php?dev_id=$dev_id") . "&nbsp;" .
			formatted_link("Recache Disks", "recache.php?dev_id=$dev_id&type=disk"), "",
			formatted_link("Edit", "$SCRIPT_NAME?action=edit&dev_id=$dev_id") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . $dev_row["name"] . "', '" . $dev_row["id"] . "')"), "");

} # end devices

?>
</table>
<?
} # End if no action

if ($action == "add")
{
	check_auth(2);
	begin_page();

	echo("<big><b>
	<a href='$SCRIPT_NAME?grp_id=$grp_id&action=addnew'>Create a new device</a><br><br>
	<a href='$SCRIPT_NAME?grp_id=$grp_id&action=addtogrp>Add an existing device to this group</a>
	</b></big>");

	end_page();
}

if ($action == "addtogrp")
{

	check_auth(2);
	begin_page();
	make_edit_table("Add Existing Device to a Group");
	make_edit_select_from_table("Device:","dev_id","mon_devices",-1);
	make_edit_hidden("action","doaddtogrp");
	make_edit_hidden("grp_id",$grp_id);
	make_edit_submit_button();
	make_edit_end();
	end_page();
}

if (($action == "edit") || ($action == "addnew")) {
# Display editing screen
check_auth(2);
begin_page();
if ($action == "addnew") 
{ 
	$dev_id = 0; 
} 

$dev_results = do_query("SELECT * FROM mon_devices WHERE id=$dev_id");
$dev_row = mysql_fetch_array($dev_results);
$dev_name = $dev_row["name"];
$dev_ip = $dev_row["ip"];
if (!isset($grp_id))
{
	$grp_id = $dev_row["group_id"];
}
if ($action == "addnew")
{
	$dev_row["check_if_number"] = 1;
}
make_edit_table("Edit Device");
make_edit_group("General");
make_edit_text("Name:","dev_name","25","100",$dev_name);
make_edit_text("IP or Host Name:","dev_ip","25","100",$dev_ip);
make_edit_select_from_table("Device Type:","dev_type","mon_device_types",$dev_row["dev_type"]);
make_edit_checkbox("Disabled (do not monitor this device)","disabled",$dev_row["disabled"]);
make_edit_group("SNMP");
make_edit_checkbox("This device uses SNMP", "snmp_enabled", $dev_row["snmp_enabled"]);
make_edit_text("SNMP Read Community:","snmp_read_community",50,200,$dev_row["snmp_read_community"]);
make_edit_checkbox("Do not cache interface mappings","snmp_recache",$dev_row["snmp_recache"]);
make_edit_checkbox("Clear interface cache when interface count changes","snmp_check_ifnumber",$dev_row["snmp_check_ifnumber"]);
make_edit_hidden("dev_id",$dev_id);
make_edit_hidden("action","doedit");
make_edit_submit_button();
make_edit_end();

} # End editing screen

end_page();

?>
