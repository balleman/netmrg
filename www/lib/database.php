<?php
/********************************************
* NetMRG Integrator
*
* database.php
* Database Abstraction/Integration Module
*
* see doc/LICENSE for copyright information
********************************************/


/**
* db_connect()
*
* connect to the database
*/
function db_connect()
{
	$conn = mysql_connect($GLOBALS["netmrg"]["dbhost"], $GLOBALS["netmrg"]["dbuser"], $GLOBALS["netmrg"]["dbpass"]) or 
		die("<b>DB_ERROR:</b>: Cannot connect to the database server.");
	mysql_select_db($GLOBALS["netmrg"]["dbname"], $conn) or 
		die("<b>DB_ERROR:</b> Cannot connect to the database.");
	return $conn;
} // end db_connect();


// Obtain data from a table
function db_query($query_string)
{
	$query_result = mysql_query($query_string, $GLOBALS["netmrg"]["dbconn"]) or
		die("<b>DB_ERROR:</b> Couldn't execute query:<br>\n$query_string<br>\n".mysql_error());

	return $query_result;
} // end db_query


// Update/Insert data in table
function db_update($query_string)
{
	$query_result = mysql_query($query_string, $GLOBALS["netmrg"]["dbconn"]) or
		die("<b>DB_ERROR:</b> Couldn't execute query:<br>\n$query_string<br>\n".mysql_error());

	return $query_result;

} // end db_update

// fetch data from a query
function db_fetch_array($q_handle)
{
	return mysql_fetch_array($q_handle);
} // end db_fetch_array()

// number of rows returned in a query
function db_num_rows($q_handle)
{
	return mysql_num_rows($q_handle);
} // end db_num_rows()

// escape data in query
function db_escape_string($string)
{
	return mysql_escape_string($string);
} // end db_escape()

// last insert id
function db_insert_id()
{
	return mysql_insert_id($GLOBALS["netmrg"]["dbconn"]);
} // end db_insert_id()



?>
