<?php
/********************************************
* NetMRG Integrator
*
* grpdev_list.php
* Lists groups and devices
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadAll"]);

// if no action, set a default one
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action

// set parent id
$_REQUEST["parent_id"] = (empty($_REQUEST["parent_id"])) ? 0 : $_REQUEST["parent_id"];

// what to do
switch ($_REQUEST["action"])
{
	default :
	case "list" :
		display();
		break;
} // end what to do



/***** FUNCTIONS *****/
function display()
{
	// Display a list
	begin_page("grpdev_list.php", "Groups");
	PrepGroupNavHistory("group", $_REQUEST["parent_id"]);
	DrawGroupNavHistory("group", $_REQUEST["parent_id"]);
	js_confirm_dialog("del_grp", "Are you sure you want to delete group ", " and all associated items?", "groups.php?action=delete&tripid={$_REQUEST['tripid']}&parent_id={$_REQUEST['parent_id']}&grp_id=");
	js_confirm_dialog("del_dev", "Are you sure you want to delete device ", " and all associated items?", "devices.php?action=delete&tripid={$_REQUEST['tripid']}&grp_id={$_REQUEST['parent_id']}&dev_id=");
	js_checkbox_utils("grp");
	js_checkbox_utils("dev");
	
	/*** GROUP LIST ***/
	$grp_results = db_query(
		"SELECT id, name, comment 
		FROM groups 
		WHERE parent_id = '{$_REQUEST['parent_id']}' 
		ORDER BY name ASC");
	
	echo '<form action="groups.php" method="post" name="grpform">'."\n";
	echo '<input type="hidden" name="tripid" value="'.$_REQUEST['tripid'].'">'."\n";
	echo '<input type="hidden" name="parent_id" value="'.$_REQUEST['parent_id'].'">'."\n";
	echo '<input type="hidden" name="action" value="">'."\n";
	make_display_table("Device Groups", "groups.php?action=add&parent_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => checkbox_toolbar("grp")),
		array("text" => "Name"),
		array("text" => "Comment")
	); // end make_display_table();
	
	// while results
	$count = 0;
	while ($grp_row = db_fetch_array($grp_results))
	{
		$grp_id  = $grp_row["id"];
		$grp_href = (db_fetch_cell("SELECT count(*) FROM groups WHERE parent_id = '{$_REQUEST['parent_id']}'") > 0) ? "grpdev_list.php?parent_id=$grp_id&tripid={$_REQUEST['tripid']}" : "#";
		
		make_display_item("editfield".($count%2),
			array("checkboxname" => "grp_id", "checkboxid" => $grp_row['id']),
			array("text" => $grp_row["name"], "href" => $grp_href),
			array("text" => $grp_row["comment"]),
			array("text" => formatted_link("View",
				"view.php?action=view&object_type=group&object_id={$grp_row['id']}") . "&nbsp;" .
				formatted_link("Edit", "groups.php?action=edit&grp_id=$grp_id&parent_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del_grp('" . addslashes($grp_row["name"]) . "', '" . $grp_row["id"] . "')"))
		); // end make_display_item();
		$count++;
	} // end while groups
	make_checkbox_command("grp", 4,
		array("text" => "Delete", "action" => "deletemulti", "prompt" => "Are you sure you want to delete the checked groups?")
	); // end make_checkbox_command
	make_status_line("group", $count);
?>
</table>
</form>
<br />
<?php
	/*** END GROUPS ***/
	
	/*** DEVICES ***/
	if (!empty($_REQUEST["parent_id"]))
	{
	$addlink = "devices.php?action=add&grp_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}";
	$grp_name = db_fetch_cell("SELECT name FROM groups WHERE id = '{$_REQUEST['parent_id']}'");
	$title = (empty($grp_name)) ? "Monitored Devices" : "Monitored Devices in Group '$grp_name'";
	
	$dev_results = db_query("
		SELECT devices.name AS name, devices.ip, devices.id, devices.snmp_version,
			count(snmp.ifIndex) AS interface_count, count(disk.disk_index) AS disk_count
		FROM dev_parents
		LEFT JOIN devices ON dev_parents.dev_id=devices.id
		LEFT JOIN snmp_interface_cache snmp ON devices.id=snmp.dev_id
		LEFT JOIN snmp_disk_cache disk ON devices.id=disk.dev_id
		WHERE grp_id = '{$_REQUEST['parent_id']}'
		GROUP BY devices.id
		ORDER BY name ASC");
	
	echo '<form action="devices.php" method="post" name="devform">'."\n";
	echo '<input type="hidden" name="tripid" value="'.$_REQUEST['tripid'].'">'."\n";
	echo '<input type="hidden" name="grp_id" value="'.$_REQUEST['parent_id'].'">'."\n";
	echo '<input type="hidden" name="action" value="">'."\n";
	make_display_table($title, $addlink,
		array("text" => checkbox_toolbar("dev")),
		array("text" => "Name"),
		array("text" => "SNMP Options")
	);
	
	$count = 0;
	while($dev_row = db_fetch_array($dev_results))
	{
		$dev_id  = $dev_row["id"];
		$links   =
		cond_formatted_link($dev_row["interface_count"] > 0, "View&nbsp;Interface&nbsp;Cache",
			"snmp_cache_view.php?dev_id=$dev_id&action=view&type=interface&tripid={$_REQUEST['tripid']}") . " " .
		cond_formatted_link($dev_row["snmp_version"] > 0, "Recache&nbsp;Interfaces",
			"recache.php?dev_id=$dev_id&type=interface&tripid={$_REQUEST['tripid']}") . " " .
		cond_formatted_link($dev_row["disk_count"] > 0, "View&nbsp;Disk&nbsp;Cache",
			"snmp_cache_view.php?dev_id=$dev_id&action=view&type=disk&tripid={$_REQUEST['tripid']}") . " " .
		cond_formatted_link($dev_row["snmp_version"] > 0, "Recache&nbsp;Disks",
			"recache.php?dev_id=$dev_id&type=disk&tripid={$_REQUEST['tripid']}");
		
		make_display_item("editfield".($count%2),
			array("checkboxname" => "dev_id", "checkboxid" => $dev_row['id']),
			array("text" => $dev_row["name"], "href" => "sub_devices.php?dev_id=$dev_id&tripid={$_REQUEST['tripid']}"),
			array("text" => $links),
			array("text" => formatted_link("View", "view.php?action=view&object_type=device&object_id=$dev_id") . "&nbsp;" .
				formatted_link("Duplicate", "devices.php?action=duplicate&dev_id=$dev_id&grp_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Edit", "devices.php?action=edit&dev_id=$dev_id&grp_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del_dev('" . addslashes($dev_row["name"]) . "', '" . $dev_row["id"] . "')"))
		); // end make_display_item();
		$count++;
	} // end while devices
	make_checkbox_command("dev", 5,
		array("text" => "Delete", "action" => "deletemulti", "prompt" => "Are you sure you want to delete the checked devices?")
	); // end make_checkbox_command
	make_status_line("device", $count);
?>
</table>
</form>
<br />
<?php
	} // end if no parents, do do groups
	/*** END DEVICES ***/
	
	end_page();
} // end display();

?>
