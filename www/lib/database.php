<?php
/********************************************
* NetMRG Integrator
*
* database.php
* Database Abstraction/Integration Module
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


/**
* db_connect()
*
* connect to the database
*/
function db_connect()
{
	$GLOBALS['querycount'] = 0;
	$dbhost = "";
	if ($GLOBALS["netmrg"]["dbsock"] != "")
	{
		$dbhost = $GLOBALS["netmrg"]["dbhost"] . ":" . $GLOBALS["netmrg"]["dbsock"];
	}
	elseif ($GLOBALS["netmrg"]["dbport"] > 0)
	{
		$dbhost = $GLOBALS["netmrg"]["dbhost"] . ":" . $GLOBALS["netmrg"]["dbport"];
	}
	else
	{
		$dbhost = $GLOBALS["netmrg"]["dbhost"];
	}

	$conn = mysql_connect($dbhost, $GLOBALS["netmrg"]["dbuser"], $GLOBALS["netmrg"]["dbpass"]) or
		die("<b>DB_ERROR:</b>: Cannot connect to the database server.");
	mysql_select_db($GLOBALS["netmrg"]["dbname"], $conn) or
		die("<b>DB_ERROR:</b> Cannot connect to the database.");
	return $conn;
} // end db_connect();


// Obtain data from a table
function db_query($query_string, $unsafe = false)
{
	$GLOBALS['querycount']++;
	$query_result = mysql_query($query_string, $GLOBALS["netmrg"]["dbconn"]);
	// if there was an error, handle it
	if (mysql_errno($GLOBALS["netmrg"]["dbconn"]) && !$unsafe)
	{
		if ($GLOBALS["netmrg"]["dbdebug"])
		{ 	 
			die("<b>DB_ERROR:</b> Couldn't execute query:<br>\n<pre>$query_string</pre><br>\n<pre>".mysql_error()."</pre><br>\n\n"); 	 
		} // end if we're debuging things 	 
		else 	 
		{ 	 
			die("<b>DB_ERROR:</b> Sorry, a database error occured.  We cannot continue.  Please contact the administrator and let them know what you were doing when the problem occured<br><br>\n\n"); 	 
		} // end else present a nice error code 	 
	} // end if there was an error

	return $query_result;
} // end db_query


// Update/Insert data in table
function db_update($query_string)
{
	db_query($query_string);
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


/**
 * Quotes a string for use in a query
 * 
 * @param string|array $value
 * @return string
 */
function db_quote($value)
{
	$return_val = '';
	
	// strip slashes if magic quotes is on
	if (get_magic_quotes_gpc())
	{
		$value = stripslashes($value);
	} // end if magic quotes
	
	// quote if not a number or a numeric string
	if (!is_array($value))
	{
		$return_val = '\'' . mysql_real_escape_string($value) . '\'';
	} // end if value not an array
	else
	{
		foreach ($value as $key => $local_value)
		{
			$value[$key] = '\'' . mysql_real_escape_string($local_value) . '\'';
		} // end foreach array value
		$return_val = implode(',', $value);
	} // end if value is an array
	
	return $return_val;
} // end function db_quote()


/**
* db_fetch_cell($sql)
* 
*  run a 'select' sql query and return the first column of the first row found
*
*  @arg $sql - the sql query to execute
*  @returns - (bool) the output of the sql query as a single variable
*/
function db_fetch_cell($sql)
{
	$row = array();
	$res = db_query($sql);
	
	if ($res)
	{
		$rows = mysql_numrows($res);
		
		if ($rows > 0)
		{
			return(mysql_result($res,0,0));
		} // end if rows
	} // end if result
	
	return false;
} // end db_fetch_cell();


/**
* db_fetch_row($sql)
* 
* run a 'select' sql query and return the first row found
*
* @arg $sql - the sql query to execute
* @returns - the first row of the result as a hash
*/
function db_fetch_row($sql)
{
	$row = array();
	$res = db_query($sql);
	
	if ($query)
	{
		$rows = mysql_numrows($res);
		
		if ($rows > 0)
		{
			return(mysql_fetch_assoc($res));
		} // end if rows
	} // end if result
	
	return false;
} // end db_fetch_row();


/**
* db_fetch_assoc($sql)
* 
* run a 'select' sql query and return all rows found
* 
* @arg $sql - the sql query to execute
* @returns - the entire result set as a multi-dimensional hash
*/
function db_fetch_assoc($sql)
{
	$data = array();
	$res = db_query($sql);
	
	if ($res)
	{
		$rows = mysql_numrows($res);
		
		if ($rows > 0)
		{
			while($row = mysql_fetch_assoc($res))
			{
				array_push($data, $row);
			}
			return($data);
		}
	} // end if result
	
	return false;
} // end db_fetch_assoc();


/**
* db_data_seek($q_handle, $rownum);
*
* seeks to specified row number
*/
function db_data_seek($q_handle, $rownum)
{
	if (!mysql_data_seek($q_handle, $rownum))
	{
		if ($GLOBALS["netmrg"]["dbdebug"])
		{
			die("<b>DB_ERROR:</b> Attempt to seek past end of data set to row <b>$rownum</b><br>\n\n");
		} // end if we're debuging things
		else
		{
			die("<b>DB_ERROR:</b> Sorry, a database error occured.  We cannot continue.  Please contact the administrator and let them know what you were doing when the problem occured<br><br>\n\n");
		} // end else present a nice error code
	} // end if the seek failed
} // end db_data_seek();

?>
