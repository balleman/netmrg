<?php
/********************************************
* NetMRG Integrator
*
* recache.php
* SNMP recaching script
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/

require_once("../include/config.php");

check_auth($GLOBALS['PERMIT']["ReadWrite"]);

if (isset($_REQUEST['type']) && isset($_REQUEST['dev_id']))
{
	$_REQUEST['dev_id'] = intval($_REQUEST['dev_id']);
	switch ($_REQUEST['type'])
	{
		case "interface":	do_interface_recache($_REQUEST['dev_id']); 	break;
		case "disk":		do_disk_recache($_REQUEST['dev_id']); 		break;
		case "properties":	do_properties_recache($_REQUEST['dev_id']); break;
	}
}

function do_interface_recache($dev_id)
{
	system($GLOBALS['netmrg']['binary'] . " -qi $dev_id");
	header("Location: snmp_cache_view.php?action=view&type=interface&dev_id=$dev_id&tripid={$_REQUEST['tripid']}");
}

function do_disk_recache($dev_id)
{
	system($GLOBALS['netmrg']['binary'] . " -qd $dev_id");
	header("Location: snmp_cache_view.php?action=view&type=disk&dev_id=$dev_id&tripid={$_REQUEST['tripid']}");
}

function do_properties_recache($dev_id)
{
	system($GLOBALS['netmrg']['binary'] . " -qP $dev_id");
	header("Location: grpdev_list.php?parent_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}");
}


?>
