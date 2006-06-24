<?php
/********************************************
* NetMRG Integrator
*
* ajax.php
* AJAX Interface Module
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");

function redraw_subdevice($type, $id, $rp = null)
{
	$resp = ($rp !== null ? $rp : new netmrgXajaxResponse());
	$resp->addClearSelect("subdevice");
	$dev_id = null;
	$sub_dev_id = null;
	if ($type == "device")
	{
		$dev_id = $id;
		$resp->addClearSelect("monitor");
	}
	else
	{
		$dev_id = db_fetch_cell("SELECT dev_id FROM sub_devices WHERE id = '$id'");
		$sub_dev_id = $id;
	}
	$q = db_query("SELECT id, name FROM sub_devices WHERE dev_id='$dev_id' ORDER BY name");
	
	$first = true;
	$i = 0;
	$selected = -1;
	while ($row = db_fetch_array($q))
	{
		if ($first && ($sub_dev_id === null))
		{
			redraw_monitor("subdevice", $row['id'], $resp);
			$first = false;
		}
		$resp->addCreateOption("subdevice", $row['name'], $row['id']);
		if ($row['id'] == $sub_dev_id)
			$selected = $i;
		$i++;
	}
	if ($sub_dev_id !== null)
		$resp->addAssign("subdevice", "selectedIndex", $selected);	
	return $resp;
}

function redraw_monitor($type, $id, $rp = null)
{
	$resp = ($rp !== null ? $rp : new netmrgXajaxResponse());
	$mon_id = null;
	$sub_dev_id = null;
	if ($type == "subdevice")
	{
		$sub_dev_id = $id;
		$resp->addClearSelect("monitor");
	}
	else
	{
		$sub_dev_id = db_fetch_cell("SELECT sub_dev_id FROM monitors WHERE id = '$id'");
		$mon_id = $id;
		redraw_subdevice("subdevice", $sub_dev_id, $resp);
	}
	$q = db_query("SELECT id FROM monitors WHERE sub_dev_id='$sub_dev_id'");
	$i = 0;
	$selected = -1;
	while ($row = db_fetch_array($q))
	{
		$resp->addCreateOption("monitor", get_short_monitor_name($row['id']), $row['id']);
		if ($row['id'] == $mon_id)
			$selected = $i;
		$i++;
	}
	if ($mon_id !== null)
		$resp->addAssign("monitor", "selectedIndex", $selected);
	return $resp;
}

$xajax->processRequests();


?>
