<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Graph Creation Module                      #
#           get_graph.php                              #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");

// Expiration Headers
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");			// Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");		// always modified
header("Cache-Control: no-cache, must-revalidate");			// HTTP/1.1
header("Pragma: no-cache");						// HTTP/1.0

// Image Type
header("Content-type: image/png");					// PNG

if (empty($_REQUEST["hist"]))
{
        $_REQUEST["hist"] = 0;
}

if (empty($_REQUEST["togglelegend"]))
{
        $_REQUEST["togglelegend"] = 0;
}


PassThru(get_graph_command($_REQUEST["type"], $_REQUEST["id"], $_REQUEST["hist"], $_REQUEST["togglelegend"]));

?>
