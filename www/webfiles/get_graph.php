<?php
/********************************************
* NetMRG Integrator
*
* get_graph.php
* Graph Creation Module
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");

if (isset($_REQUEST["debug"]))
{
	include("../lib/phptimer.php");
	$timer = new PHP_timer;
	$timer->start();
} // end if debug

/***** Expiration Headers *****/

// debug
if (isset($_REQUEST["debug"])) { $timer->addmarker("before headers"); }
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// Always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
header("Pragma: no-cache");

// Image Type
if (!isset($_REQUEST['debug'])) header("Content-type: image/png");

// debug
if (isset($_REQUEST["debug"])) { $timer->addmarker("before auth"); }
// we need to auth different ways depending on type of graph
switch($_REQUEST["type"])
{
	case "template" :
		GraphCheckAuth($_REQUEST["type"], $_REQUEST["subdev_id"]);
		break;
	
	default :
		GraphCheckAuth($_REQUEST["type"], $_REQUEST["id"]);
		break;
} // end switch type for auth
// debug
if (isset($_REQUEST["debug"])) { $timer->addmarker("after auth"); }

if (empty($_REQUEST["hist"]))
{
	$_REQUEST["hist"] = 0;
}

// debug
if (isset($_REQUEST["debug"])) { $timer->addmarker("before get command"); }
// figure out what our command for generating the graph will be
$command = get_graph_command($_REQUEST["type"], $_REQUEST["id"], $_REQUEST["hist"], $_REQUEST["type"] == "template");
// debug
if (isset($_REQUEST["debug"])) { $timer->addmarker("after get command"); }

if (isset($_REQUEST["debug"])) { $timer->addmarker("before exec command"); }
if (isset($_REQUEST['debug']))
{
	echo($command);
	exec($command);
}
else
{
	passthru($command);
}
if (isset($_REQUEST["debug"])) { $timer->addmarker("after exec command"); }

if (isset($_REQUEST["debug"]))
{
	$timer->stop();
	$timer->debug();
	$timer->showtime();
} // end if debug

?>