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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");			// Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");		// Always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  		// HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");						// HTTP/1.0

// Image Type
if (!isset($debug)) header("Content-type: image/png");			// PNG

if (empty($_REQUEST["hist"]))
{
	$_REQUEST["hist"] = 0;
}

if (empty($_REQUEST["togglelegend"]))
{
	$_REQUEST["togglelegend"] = 0;
}

$command = get_graph_command($_REQUEST["type"], $_REQUEST["id"], $_REQUEST["hist"], $_REQUEST["togglelegend"], $_REQUEST["type"] == "template");

if (!isset($debug))
{
	passthru($command);
}
else
{
	echo($command);
}

?>
