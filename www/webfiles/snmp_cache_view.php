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
	case "view":	view_cache(); break;
	case "graph":	make_graph(); break;
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

	if (isset($_REQUEST["graph"]))
	{
		check_auth(2);

		// get some data to play with
		$q_dev = do_query("SELECT * FROM mon_devices WHERE id={$_REQUEST['dev_id']}");
		$r_dev = mysql_fetch_array($q_dev);

		$q_snmp = do_query("SELECT * FROM snmp_cache WHERE dev_id={$_REQUEST['dev_id']} AND if_index='{$_REQUEST['index']}'");
		$r_snmp = mysql_fetch_array($q_snmp);

		// add two monitors, if ifName defined, use it, otherwise use ifIndex
		if ($r_snmp["if_name"] != "")
		{
			$snmp_val = $r_snmp["if_name"];
			$snmp_type = 3;
		}
		else
		{
			$snmp_val = $_REQUEST["index"];
			$snmp_type = 2;
		} // end if ifname empty
		do_update("INSERT INTO mon_monitors SET device_id='{$_REQUEST['dev_id']}',
			test_id='0', params='', rrd_id='2', graphed='1', snmp_test='0',
			snmp_index_type='$snmp_type', snmp_index_value='$snmp_val',
			snmp_data='3', disk_index_type='0', disk_index_value='',
			disk_data='0', mon_type='3'");
		$mon_id_1 = mysql_insert_id();
		do_update("INSERT INTO mon_monitors SET device_id='{$_REQUEST['dev_id']}',
			test_id='0', params='', rrd_id='2', graphed='1', snmp_test='0',
			snmp_index_type='$snmp_type', snmp_index_value='$snmp_val',
			snmp_data='8', disk_index_type='0', disk_index_value='',
			disk_data='0', mon_type='3'");
		$mon_id_2 = mysql_insert_id();


		// add graph
		do_update("INSERT INTO graphs SET name=\"" . $r_dev["name"] . " - " . $r_snmp["if_alias"]  . "\",
			comment=\"Interface: " . $r_snmp["if_name"] . "\"," . ' xsize=400, ysize=100' .
			', vert_label="' . 'Bytes Per Second", show_legend=1');
		$graph_id = mysql_insert_id();

		// add graph DS's
		do_update("INSERT INTO graph_ds SET " .
			'src_type=' . "0" . ',' .
			'src_id=' . $mon_id_1 . ',' .
			'color="' . '#00EE00' . '",' .
			'type=' . "4" . ',' .
			'graph_id=' . $graph_id . ',' .
			'label="' . 'Inbound' . '"');

		do_update("INSERT INTO graph_ds SET " .
			'src_type=' . "0" . ',' .
			'src_id=' . $mon_id_2 . ',' .
			'color="' . '#0000EE' . '",' .
			'type=' . "2" . ',' .
			'graph_id=' . $graph_id . ',' .
			'label="' . 'Outbound' . '"');

		// add graph to view for the device
		do_update("INSERT INTO view SET pos_id = {$_REQUEST['dev_id']}, pos_id_type = 1, graph_id = $graph_id, graph_id_type = \"custom\", pos = {$_REQUEST['index']}");

		// Redirect to Graph Edit page (it will redirect back when done)
		Header("Location: custom_graphs.php?action=edit&graph_id=$graph_id&return_type=traffic&return_id={$_REQUEST['dev_id']}");
		exit(0);

	} // end if graph
}

function view_disk_cache()
{
	check_auth(1);
	$query = "SELECT * FROM snmp_disk_cache WHERE dev_id={$_REQUEST['dev_id']} ORDER BY disk_index";
	$dev_name = get_device_name($_REQUEST['dev_id']);
	
        begin_page("snmp_cache_view.php", "$dev_name - Disk Cache");
	
	make_plain_display_table("$dev_name - Disk Cache",
		"Index", "",
		"Device", "",
		"Path", "");
	
	$handle = do_query($query);
	
	for ($i = 0; $i < mysql_num_rows($handle); $i++)
	{                   
		$row = mysql_fetch_array($handle);
                make_display_item(
			$row['disk_index'], "",
			$row['disk_device'], "",
			$row['disk_path'], "");
	}
	
	echo("</table>");
	end_page();
}

function view_interface_cache()
{
	check_auth(1);
	$query = "SELECT * FROM snmp_interface_cache";
	$sort_href = "{$_SERVER['PHP_SELF']}?action=view&type=interface&";
	if (isset($_REQUEST["dev_id"]))
	{
		$query .= " WHERE dev_id={$_REQUEST['dev_id']}";
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

	make_plain_display_table("$dev_name - Interface Cache",
		"Index",	"$sort_href=ifIndex",
		"Status",	"",
		"Name",		"$sort_href=ifName",
		"Description",	"$sort_href=ifDescr",
		"Alias",	"$sort_href=ifAlias",
		"IP Address",	"$sort_href=ifIP",
		"MAC Address",	"$sort_href=ifMAC",
		"Commands","");

	$handle = do_query($query);
	for ($i = 0; $i < mysql_num_rows($handle); $i++)
	{
		$row = mysql_fetch_array($handle);
		if (isset($row['ifAdminStatus']))
		{
			$status  = $GLOBALS['INTERFACE_STATUS'][$row['ifAdminStatus']] . "/";
		}
		if (isset($row['ifOperStatus']))
		{
			$status .= $GLOBALS['INTERFACE_STATUS'][$row['ifOperStatus']] . " ";
		}
		if (isset($row['ifType']))
		{
			if isset($GLOBALS['INTERFACE_TYPE'][$row['ifType']])
			{
				$status .= $GLOBALS['INTERFACE_TYPE'][$row['ifType']];
			}
		}
		make_display_item(
			$row["ifIndex"],	"",
			$status, 		"",
			$row["ifName"],		"",
			$row["ifDescr"],	"",
			$row["ifAlias"],	"",
			$row["ifIP"],		"",
			$row["ifMAC"],		"",
			formatted_link("Graph Traffic","snmp_cache_view.php?graph=1&dev_id=" . $row["dev_id"] . "&index=" . $row["ifIndex"]), "");
	} // end for each row
	echo("</table>");
	end_page();
}
?>
