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
check_auth(1);

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
	// TODO: fix deletion
	js_confirm_dialog("del", "Are you sure you want to delete ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=delete&item_id=");
	// TODO: handle group/device navigation better
	DrawGroupNavHistory("group", $_REQUEST["parent_id"]);
	
	/*** GROUP LIST ***/
	$grp_results = db_query(
		"SELECT id, name, comment 
		FROM groups 
		WHERE parent_id = '{$_REQUEST['parent_id']}' 
		ORDER BY name ASC");
	
	if (db_num_rows($grp_results) > 0)
	{
		make_display_table("Device Groups", "",
			array("text" => "Name"),
			array("text" => "Comment")
		); // end make_display_table();
		
		// while results
		$count = 0;
		while ($grp_row = db_fetch_array($grp_results))
		{
			$grp_id  = $grp_row["id"];
			$grp_href = (db_fetch_cell("SELECT count(*) FROM groups WHERE parent_id = '{$_REQUEST['parent_id']}'") > 0) ? "grpdev_list.php?parent_id={$_REQUEST['parent_id']}" : "#";
			
			make_display_item("editfield".($count%2),
				array("text" => $grp_row["name"], "href" => $grp_href),
				array("text" => $grp_row["comment"]),
				array("text" => formatted_link("View",
					"view.php?action=view&object_type=group&object_id={$grp_row['id']}") . "&nbsp;" .
					formatted_link("Edit", "groups.php?action=edit&grp_id=$grp_id") . "&nbsp;" .
					formatted_link("Delete", "javascript:del('" . addslashes($grp_row["name"]) . "', '" . $grp_row["id"] . "')"))
			); // end make_display_item();
			$count++;
		} // end while groups
?>
</table>
<br />
<?php
	} // end if groups found
	/*** END GROUPS ***/
	
	/*** DEVICES ***/
	$addlink = "devices.php?action=add&grp_id={$_REQUEST['parent_id']}";
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
	
	if (db_num_rows($dev_results) > 0)
	{	
		make_display_table($title, $addlink,
			array("text" => "Name"),
			array("text" => "SNMP Options")
		);
		
		$count = 0;
		while($dev_row = db_fetch_array($dev_results))
		{
			$dev_id  = $dev_row["id"];
			$links   =
			cond_formatted_link($dev_row["interface_count"] > 0, "View&nbsp;Interface&nbsp;Cache",
				"snmp_cache_view.php?dev_id=$dev_id&action=view&type=interface") . " " .
			cond_formatted_link($dev_row["snmp_version"] > 0, "Recache&nbsp;Interfaces",
				"recache.php?dev_id=$dev_id&type=interface") . " " .
			cond_formatted_link($dev_row["disk_count"] > 0, "View&nbsp;Disk&nbsp;Cache",
				"snmp_cache_view.php?dev_id=$dev_id&action=view&type=disk") . " " .
			cond_formatted_link($dev_row["snmp_version"] > 0, "Recache&nbsp;Disks",
				"recache.php?dev_id=$dev_id&type=disk");
			
			make_display_item("editfield".($count%2),
				array("text" => $dev_row["name"], "href" => "sub_devices.php?dev_id=$dev_id"),
				array("text" => $links),
				array("text" => formatted_link("View", "view.php?action=view&object_type=device&object_id=$dev_id") . "&nbsp;" .
					formatted_link("Duplicate", "devices.php?action=duplicate&dev_id=$dev_id&grp_id={$_REQUEST['parent_id']}") . "&nbsp;" .
					formatted_link("Edit", "devices.php?action=edit&dev_id=$dev_id&grp_id={$_REQUEST['parent_id']}") . "&nbsp;" .
					formatted_link("Delete", "javascript:del('" . addslashes($dev_row["name"]) . "', '" . $dev_row["id"] . "')"))
			); // end make_display_item();
			$count++;
		} // end while devices
?>
</table>
<br />
<?php
	} // end if some rows
	/*** END DEVICES ***/
	
	end_page();
} // end display();

?>