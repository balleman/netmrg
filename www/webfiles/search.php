<?php
/********************************************
* NetMRG Integrator
*
* $Id$
* search.php
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["SingleViewOnly"]);

// check default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["query"] = "list";
} // end if no action

// check query
if (empty($_REQUEST["query"]))
{
	$_REQUEST["query"] = "";
} // end if no request

// check what to do
switch ($_REQUEST['action'])
{
	case "search":
	default:
		display($_REQUEST["query"]);
		break;
} // end switch action



/***** FUNCTIONS *****/

/**
* display($query)
*
* displays the search results
*/
function display($query)
{
	begin_page("search.php", "Search ($query)");
	
	echo "You search for:<br />\n";
	echo "<pre>\n$query\n</pre>\n";
	echo "Unfortunately, we don't have the search function witten yet\n";
	
	end_page();
} // end display();


?>
