<?php
/********************************************
* NetMRG Integrator
*
* database.php
* Database Abstraction/Integration Module
*
* see doc/LICENSE for copyright information
********************************************/


// Obtain data from a table
function do_query($query_string)
{
	mysql_connect($GLOBALS["netmrg"]["dbhost"], $GLOBALS["netmrg"]["dbreaduser"], $GLOBALS["netmrg"]["dbreadpass"]) or 
		die("<b>DB_ERROR:</b>: Cannot connect to the database server.");
	mysql_select_db($GLOBALS["netmrg"]["dbname"]) or 
		die("<b>DB_ERROR:</b> Cannot connect to the database.");
	$query_result = mysql_query($query_string) or
		die("<b>DB_ERROR:</b> Couldn't execute query:<br>\n$query_string<br>\n".mysql_error());

	return $query_result;
} // end do_query


// Update data in table
function do_update($query_string)
{
	mysql_connect($GLOBALS["netmrg"]["dbhost"], $GLOBALS["netmrg"]["dbwriteuser"], $GLOBALS["netmrg"]["dbwritepass"]) or 
		die("<b>DB_ERROR:</b>: Cannot connect to the database server.");
	mysql_select_db($GLOBALS["netmrg"]["dbname"]) or 
		die("<b>DB_ERROR:</b> Cannot connect to the database.");
	$query_result = mysql_query($query_string) or
		die("<b>DB_ERROR:</b> Couldn't execute query:<br>\n$query_string<br>\n".mysql_error());

	return $query_result;

} // end do_update


?>
