<?php
/********************************************
* NetMRG Integrator
*
* snmp_cache_view.php
* SNMP Cache Viewer
*
* see doc/LICENSE for copyright information
********************************************/

require_once("../include/config.php");

switch ($_REQUEST['action'])
{
	case "view":          view_cache(); break;
	case "graph":         make_graph(); break;
	case "graphmultiint":
		if (isset($_REQUEST["iface"]))
		{
			while (list($key,$value) = each($_REQUEST["iface"]))
			{
				make_interface_graph($_REQUEST["dev_id"], $key);
			} // end while each interface
		} // end for each interface
		// redirect to keep us from doing this again
		header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&type=interface&action=view");
		exit(0);
		break;
}

function view_cache()
{
	switch($_REQUEST['type'])
	{
		case "interface":	view_interface_cache(); break;
		case "disk":		view_disk_cache();	break;
	}
}

function make_graph()
{
	switch($_REQUEST['type'])
	{
		case "interface":
			make_interface_graph($_REQUEST["dev_id"], $_REQUEST["index"]);
			// redirect
			header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&type=interface&action=view");
			exit(0);
			break;
		case "disk":		make_disk_graph(); break;
	}
}

function make_interface_graph($dev_id, $index)
{
	check_auth(2);

	// get snmp index data
	$q_snmp = db_query("SELECT * FROM snmp_interface_cache WHERE dev_id='$dev_id' AND ifIndex='$index'");
	$r_snmp = db_fetch_array($q_snmp);

	if (isset($r_snmp["ifName"]) && !empty($r_snmp["ifName"]))
	{
		$index_type = "ifName";
	}
	elseif (isset($r_snmp["ifDescr"]) && !empty($r_snmp["ifDescr"]))
	{
		$index_type = "ifDescr";
	}
	elseif (isset($r_snmp["ifIP"]) && !empty($r_snmp["ifIP"]))
	{
		$index_type = "ifIP";
	}
	else
	{
		$index_type = "ifIndex";
	}

	$index_value = $r_snmp[$index_type];

	// create the subdevice
	db_update("INSERT INTO sub_devices SET dev_id='$dev_id', type=2, name='$index_value'");
	$sd_id = db_insert_id();
	db_update("INSERT INTO sub_dev_variables SET sub_dev_id=$sd_id, name='$index_type', value='$index_value'");
	db_update("INSERT INTO sub_dev_variables SET sub_dev_id=$sd_id, name='ifIndex', value='$index', type='dynamic'");

	// add monitors and associate template
	apply_template($sd_id, $GLOBALS["netmrg"]["traffictemplateid"]);

}

function make_disk_graph()
{
	check_auth(2);

	// get snmp index data
	$q_snmp = db_query("SELECT * FROM snmp_disk_cache WHERE dev_id={$_REQUEST['dev_id']} AND disk_index='{$_REQUEST['index']}'");
	$r_snmp = db_fetch_array($q_snmp);

	if (isset($r_snmp["disk_path"]) && !empty($r_snmp["disk_path"]))
	{
		$index_type = "dskPath";
		$index_value = $r_snmp["disk_path"];
	}
	elseif (isset($r_snmp["disk_device"]) && !empty($r_snmp["disk_device"]))
	{
		$index_type = "dskDevice";
		$index_value = $r_snmp["disk_device"];
	}
	else
	{
		$index_type = "dskIndex";
		$index_value = "dsk_index";
	}

	// create the subdevice
	db_update("INSERT INTO sub_devices SET dev_id={$_REQUEST['dev_id']}, type=3, name='$index_value'");
	$sd_id = db_insert_id();
	db_update("INSERT INTO sub_dev_variables SET sub_dev_id=$sd_id, name='$index_type', value='$index_value'");
	db_update("INSERT INTO sub_dev_variables SET sub_dev_id=$sd_id, name='dskIndex', value='{$_REQUEST['index']}', type='dynamic'");

	// add monitors and associate template
	apply_template($sd_id, $GLOBALS["netmrg"]["disktemplateid"]);

	// redirect
	header("Location: snmp_cache_view.php?dev_id={$_REQUEST['dev_id']}&type=disk&action=view");
	exit(0);

}

function view_disk_cache()
{
	check_auth(1);
	$query = "SELECT * FROM snmp_disk_cache WHERE dev_id={$_REQUEST['dev_id']} ORDER BY disk_index";
	$dev_name = get_device_name($_REQUEST['dev_id']);

	begin_page("snmp_cache_view.php", "$dev_name - Disk Cache");
	DrawGroupNavHistory("device", $_REQUEST["dev_id"]);

	make_plain_display_table("$dev_name - Disk Cache",
		"Index", "",
		"Device", "",
		"Path", "",
		"", "");

	$handle = db_query($query);

	for ($i = 0; $i < db_num_rows($handle); $i++)
	{
		$row = db_fetch_array($handle);
				$links = "";
		$s_query = db_query("SELECT sub.id AS id FROM sub_devices sub, sub_dev_variables var
					WHERE sub.dev_id={$_REQUEST['dev_id']}
					AND sub.id=var.sub_dev_id
					AND var.name='dskIndex'
					AND var.value={$row['disk_index']}");
		$s_row = db_fetch_array($s_query);
		if (isset($s_row['id']))
		{
			$links .= formatted_link("View", "view.php?object_type=subdevice&object_id={$s_row['id']}");
			$links .= "&nbsp;";
			$links .= formatted_link("Monitors", "monitors.php?sub_dev_id={$s_row['id']}");
			$links .= "&nbsp;";
			$links .= formatted_link_disabled("Monitor/Graph");
		}
		else
		{
			$links .= formatted_link_disabled("View");
			$links .= "&nbsp";
			$links .= formatted_link_disabled("Monitors");
			$links .= "&nbsp;";
			$links .= formatted_link("Monitor/Graph", "snmp_cache_view.php?action=graph&type=disk&dev_id=" . $row["dev_id"] . "&index=" . $row["disk_index"]);
		}

		make_display_item("editfield".($i%2),
			array("text" => $row['disk_index']),
			array("text" => $row['disk_device']),
			array("text" => $row['disk_path']),
			array("text" => $links)
		); // end make_display_item();
	}
	
	echo("</table>");
	end_page();
}

function view_interface_cache()
{
	check_auth(1);
	$query = "SELECT * FROM snmp_interface_cache snmp ";
	$sort_href = "{$_SERVER['PHP_SELF']}?action=view&type=interface&";
	if (isset($_REQUEST["dev_id"]))
	{
		$query .= " WHERE snmp.dev_id={$_REQUEST['dev_id']} ";
		$sort_href .= "dev_id={$_REQUEST['dev_id']}&";
	}
	$sort_href .= "order_by";
	if (isset($_REQUEST['order_by']))
	{
		$query .= " ORDER BY {$_REQUEST['order_by']}";
	}
	else
	{
		$query .= " ORDER BY ifIndex";
	}

	$dev_name = get_device_name($_REQUEST['dev_id']);

	begin_page("snmp_cache_view.php", "$dev_name - Interface Cache");
	DrawGroupNavHistory("device", $_REQUEST["dev_id"]);

?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="dev_id" value="<?php echo $_REQUEST["dev_id"]; ?>">
<?php
	make_plain_display_table("$dev_name - Interface Cache",
		"", "",
		"Index",        "$sort_href=ifIndex",
		"Status",       "",
		"Name",         "$sort_href=ifName",
		"Description",  "$sort_href=ifDescr",
		"Alias",        "$sort_href=ifAlias",
		"IP Address",   "$sort_href=ifIP",
		"MAC Address",  "$sort_href=ifMAC",
		"","");

	$handle = db_query($query);
	for ($i = 0; $i < db_num_rows($handle); $i++)
	{
		$row = db_fetch_array($handle);
		$status = "";
		if (isset($row['ifAdminStatus']))
		{
			$status .= $GLOBALS['INTERFACE_STATUS'][$row['ifAdminStatus']] . "/";
		}
		if (isset($row['ifOperStatus']))
		{
			$status .= $GLOBALS['INTERFACE_STATUS'][$row['ifOperStatus']] . "&nbsp;";
		}
		if (isset($row['ifType']))
		{
			if (isset($GLOBALS['INTERFACE_TYPE'][$row['ifType']]))
			{
				$status .= space_to_nbsp($GLOBALS['INTERFACE_TYPE'][$row['ifType']]);
			}
		}
		$links = "";
		$s_query = db_query("SELECT sub.id AS id FROM sub_devices sub, sub_dev_variables var
					WHERE sub.dev_id={$_REQUEST['dev_id']}
					AND sub.id=var.sub_dev_id
					AND var.name='ifIndex'
					AND var.value={$row['ifIndex']}");
		$s_row = db_fetch_array($s_query);
		if (isset($s_row['id']))
		{
			$links .= formatted_link("View", "view.php?object_type=subdevice&object_id={$s_row['id']}");
			$links .= "&nbsp;";
			$links .= formatted_link("Monitors", "monitors.php?sub_dev_id={$s_row['id']}");
			$links .= "&nbsp;";
			$links .= formatted_link_disabled("Monitor/Graph");
		}
		else
		{
			$links .= formatted_link_disabled("View");
			$links .= "&nbsp";
			$links .= formatted_link_disabled("Monitors");
			$links .= "&nbsp;";
			$links .= formatted_link("Monitor/Graph", "snmp_cache_view.php?action=graph&type=interface&dev_id=" . $row["dev_id"] . "&index=" . $row["ifIndex"]);
		}

		make_display_item("editfield".($i%2),
			array("checkboxname" => "iface", "checkboxid" => $row["ifIndex"], "checkdisabled" => isset($s_row['id'])),
			array("text" => $row["ifIndex"]),
			array("text" => $status),
			array("text" => $row["ifName"]),
			array("text" => $row["ifDescr"]),
			array("text" => $row["ifAlias"]),
			array("text" => $row["ifIP"]),
			array("text" => $row["ifMAC"]),
			array("text" => $links)
		); // end make_display_item();
	} // end for each row
	echo('<tr><td colspan="9" class="editheader" nowrap="nowrap"><a class="editheaderlink" onclick="document.form.action.value=\'graphmultiint\';document.form.submit();" href="#">Monitor/Graph All Checked</a></td></tr>'."\n");
	echo("</table>\n");
	echo("</form>\n");
	end_page();
}
?>
