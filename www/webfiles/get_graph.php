<?php
/********************************************
* NetMRG Integrator
*
* get_graph.php
* Graph Creation Module
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

if (isset($_REQUEST["debug"]))
{
	include("../lib/phptimer.php");
	$timer = new PHP_timer;
	$timer->start();
	$timer->addmarker("before headers");
} // end if debug

/***** Expiration Headers *****/
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// Always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

if (isset($_REQUEST["debug"]))
{
	$timer->addmarker("before auth");
}
else
{
	// Image Type
	header("Content-type: image/png");
}

// we need to auth different ways depending on type of graph
switch($_REQUEST["type"])
{
	case "template" :
		GraphCheckAuth($_REQUEST["type"], $_REQUEST["subdev_id"]);
		break;
	
	case "template_item" :
		GraphCheckAuth($_REQUEST["type"], $_REQUEST["subdev_id"]);
		break;
		
	case "custom_item" :
		$q = db_query("SELECT graph_id FROM graph_ds WHERE id='{$_REQUEST['id']}'");
		$r = db_fetch_array($q);
		GraphCheckAuth($_REQUEST["type"], $r['graph_id']);
		break;
	
	default :
		GraphCheckAuth($_REQUEST["type"], $_REQUEST["id"]);
		break;
} // end switch type for auth

if (empty($_REQUEST["hist"]))
{
	$_REQUEST["hist"] = 0;
}

if (isset($_REQUEST["debug"]))
{
	$timer->addmarker("after auth");
	$timer->addmarker("before get command");
}

// figure out what our command for generating the graph will be
$command = get_graph_command($_REQUEST["type"], $_REQUEST["id"], $_REQUEST["hist"], $_REQUEST["type"] == "template");

if (isset($_REQUEST["debug"]))
{
	$timer->addmarker("after get command");
	$timer->addmarker("before exec command");
	echo(rrdtool_syntax_highlight($command));
	exec($command);
	$timer->addmarker("after exec command");
	echo("<br><br>");
	$timer->stop();
	$timer->debug();
	$timer->showtime();
}
else
{
	passthru($command);
}

?>
