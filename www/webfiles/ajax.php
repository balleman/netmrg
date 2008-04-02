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
		if ($id < 0)
		{
			// MORE "Internal" hacks
			$dev_id = -1;
			$sub_dev_id = $id;
		}
		else
		{
			$dev_id = db_fetch_cell("SELECT dev_id FROM sub_devices WHERE id = '$id'");
			$sub_dev_id = $id;
		}
	}

	if ($dev_id == -1)
	{
		// Internal Device
		if ($sub_dev_id === null)
		{
			redraw_monitor("subdevice", -1, $resp);
		}
		$resp->addCreateOption("subdevice", "-Internal-", -1);
		$resp->addAssign("subdevice", "selectedIndex", 0);
	}
	else
	{
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
	}
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
		if ($id < 0)
		{
			// more "Internal" hacks.  yay.
			$sub_dev_id = -1;
			$mon_id = $id;
			redraw_subdevice("subdevice", $sub_dev_id, $resp);
		}
		else
		{
			$sub_dev_id = db_fetch_cell("SELECT sub_dev_id FROM monitors WHERE id = '$id'");
			$mon_id = $id;
			redraw_subdevice("subdevice", $sub_dev_id, $resp);
		}
	}

	if ($sub_dev_id == -1)
	{
		// "Internal" subdevice
		$resp->addCreateOption("monitor", "-Fixed Value-", -1);
		$resp->addCreateOption("monitor", "-Sum of all graph items-", -2);
		if ($mon_id == -2)
		{
			$resp->addAssign("monitor", "selectedIndex", 1);
		}
		else
		{
			$resp->addAssign("monitor", "selectedIndex", 0);
		}
	}
	else
	{
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
	}
	return $resp;
}

$xajax->processRequests();


?>
