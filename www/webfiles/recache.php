<?php
/********************************************
* NetMRG Integrator
*
* recache.php
* SNMP recaching script
*
* see doc/LICENSE for copyright information
********************************************/

require_once("../include/config.php");

if (isset($_REQUEST['type']) && isset($_REQUEST['dev_id']))
{
	switch ($_REQUEST['type'])
	{
		case "interface":	do_interface_recache($_REQUEST['dev_id']); 	break;
		case "disk":		do_disk_recache($_REQUEST['dev_id']); 		break;
	}
}

function do_interface_recache($dev_id)
{
	system($GLOBALS['netmrg']['binary'] . " -qi $dev_id");
	header("Location: snmp_cache_view.php?action=view&type=interface&dev_id=$dev_id");
}

function do_disk_recache($dev_id)
{
	system($GLOBALS['netmrg']['binary'] . " -qd $dev_id");
	header("Location: snmp_cache_view.php?action=view&type=disk&dev_id=$dev_id");
}

?>
