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

// Expiration Headers

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
if (!isset($debug)) header("Content-type: image/png");

if (empty($_REQUEST["hist"]))
{
	$_REQUEST["hist"] = 0;
}

$command = get_graph_command($_REQUEST["type"], $_REQUEST["id"], $_REQUEST["hist"], $_REQUEST["type"] == "template");

if (!isset($debug))
{
	passthru($command);
}
else
{
	echo($command);
}

?>
