<?php
/********************************************
* NetMRG Integrator
*
* auth.php
* Authentication and Permissions Module
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
* check_user($user)
*
* verifies a username (for external auth)
*   $user = username
*
*/
function check_user($user)
{
	$auth_valid = false;
	$auth_select = "SELECT 1 FROM user WHERE user='$user'";
	$auth_result = db_query($auth_select);
	if (db_num_rows($auth_result) > 0)
	{
		$auth_valid = true;
	}
	else
	{
		$auth_valid = false;
	} // end if we have a result or not

	return $auth_valid;
} // end check_user();


/**
* check_user_pass($user, $pass);
*
* verifies a username and password agains what's in the database
*   $user = username
*   $pass = password
*/
function check_user_pass($user, $pass)
{
	$auth_valid = false;
	$auth_select = "SELECT 1 FROM user WHERE user='$user' AND pass=MD5('$pass')";
	$auth_result = db_query($auth_select);
	if (db_num_rows($auth_result) > 0)
	{
		$auth_valid = true;
	}
	else
	{
		$auth_valid = false;
	} // end if we have a result or not

	return $auth_valid;
} // end check_user_pass()


/**
* IsLoggedIn();
*
* verifies a username and password in the session
* against what's in the database
* and that the user isn't spoofing their ip
* and that they haven't been logged in too long
*/
function IsLoggedIn()
{
	if ((
			($GLOBALS["netmrg"]["externalAuth"] 
			&& (check_user($_SESSION["netmrgsess"]["username"])
				|| check_user($GLOBALS["netmrg"]["defaultMapUser"])))
			||
			(!$GLOBALS["netmrg"]["externalAuth"]
			&& check_user_pass($_SESSION["netmrgsess"]["username"], $_SESSION["netmrgsess"]["password"]))
		)
		&& $_SESSION["netmrgsess"]["remote_addr"] == $_SERVER["REMOTE_ADDR"]
		&& time() - $_SESSION["netmrgsess"]["accessTime"] <= $GLOBALS["netmrg"]["authTimeout"])
	{
		return true;
	} // end if the username/password checks out and the ips match

	return false;
} // end IsLoggedIn();


/**
* get_full_name($user)
*
* gets $user's full name
*/
function get_full_name($user)
{
	$q = db_query("SELECT fullname FROM user WHERE user='$user'");
	$r = db_fetch_array($q);
	return $r["fullname"];
} // end get_full_name()


/**
* check_auth($level)
*
* checks the logged in user's auth level to be sure they have
* at least auth level $level.  If not, send them away
*/
function check_auth($level)
{
	// if they aren't logged in
	if (!IsLoggedIn())
	{
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		if ($GLOBALS["netmrg"]["externalAuth"])
		{
			header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");
			exit(0);
		} // end if externalauth
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=invalid");
		exit(0);
	} // end if they aren't logged in

	// if they don't have enough permissions
	else if ($_SESSION["netmrgsess"]["permit"] < $level)
	{
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit(0);
	} // end if they don't have enough permissions

} // end check_auth()


/**
* viewCheckAuth($object_id, $object_type)
*
* called from the 'view.php' page
* checks that the user is allowed to see this page
*/
function viewCheckAuth($object_id, $object_type)
{
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);
	
	// the groups this object_id is in
	$object_id_groups = GetGroups($object_type,$object_id);
	
	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups)
		&& $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"])
	{
		return false;
	} // end if allowed group id is not in this objects groups and we're SVO
	
	return true;
} // end viewCheckAuth()


/**
* viewCheckAuthRedirect($object_id, $object_type)
*
* called from the 'view.php' page
* checks that the user is allowed to see this page
* and redirects if they are not
*/
function viewCheckAuthRedirect($object_id, $object_type)
{
	if (!viewCheckAuth($object_id, $object_type))
	{
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	} // end if not authorized
} // end viewCheckAuthRedirect()


/**
* EncloseGraphCheckAuth()
*
* makes sure that the logged in user can view a graph
*
* type = template, custom, mon, tinymon
* id = id of item
*
*/
function EncloseGraphCheckAuth($type, $id)
{
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

	// the groups this object_id is in
	$object_id_groups = array();

	switch ($type)
	{
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetGroups("monitor",$id);
			break;

		case "template" :
			$object_id_groups = GetGroups("subdevice",$id);
			break;

		case "custom" :
			$object_id_groups = GetGroups("customgraph",$id);
			break;
	} // end switch graph type

	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups)
		&& $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"])
	{
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	}
} // end EncloseGraphCheckAuth();


/**
* GraphCheckAuth()
*
* makes sure that the logged in user can view a graph
*
* type = template, custom, mon, tinymon
* id = id of item
*
*/
function GraphCheckAuth($type, $id)
{
	global $PERMIT;
	check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

	// the groups this object_id is in
	$object_id_groups = array();

	switch ($type)
	{
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetGroups("monitor",$id);
			break;

		case "template" :
		case "template_item" :
			$object_id_groups = GetGroups("subdevice",$id);
			break;

		case "custom" :
		case "custom_item" :
			$object_id_groups = GetGroups("customgraph",$id);
			break;
	} // end switch graph type

	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups)
		&& $_SESSION["netmrgsess"]["permit"] == $PERMIT["SingleViewOnly"])
	{
		readfile($GLOBALS["netmrg"]["fileroot"]."/webfiles/img/access_denied.png");
		exit;
	}
} // end GraphCheckAuth();


/**
* ResetAuth()
*
* reset authentication variables
*/
function ResetAuth()
{
	if (isset($_SESSION["netmrgsess"]))
	{
		unset($_SESSION["netmrgsess"]);
		$_SESSION["netmrgsess"] = array();
		$_SESSION["netmrgsess"]["username"] = "";
		$_SESSION["netmrgsess"]["password"] = "";
		$_SESSION["netmrgsess"]["remote_addr"] = "";
		$_SESSION["netmrgsess"]["permit"] = "";
		$_SESSION["netmrgsess"]["accessTime"] = "";
	} // end if isset netmrg session
} // end ResetAuth()


/**
* get_permit($user)
*
* gets the user's permission level
*/
function get_permit($user)
{
	if (IsLoggedIn())
	{
		global $PERMIT;
		if ($GLOBALS["netmrg"]["verhist"][$GLOBALS["netmrg"]["dbversion"]] >= $GLOBALS["netmrg"]["verhist"]["0.17"])
		{
			$sql = "SELECT IF(disabled=0, permit, '".$PERMIT["Disabled"]."') AS permit FROM user WHERE user='".$user."'";
		} // end if the disabled column works
		else
		{
			$sql = "SELECT permit FROM user WHERE user='".$user."'";
		} // end if no disabled column
		$handle = db_query($sql);
		$row = db_fetch_array($handle);
		return $row["permit"];
	} // end if there is somebody logged in, get their permissions

	return false;
} // end get_permit()

/**
* GetUserID()
*
* gets the user id of the logged in user
*/
function GetUserID()
{
	if (IsLoggedIn())
	{
		$sql = "SELECT id FROM user WHERE user='" . $_SESSION["netmrgsess"]["username"] . "'";
		$handle = db_query($sql);
		$row = db_fetch_array($handle);
		return empty($row["id"]) ? false : $row["id"];
	} // end IsLoggedIn
	
	return false;
} // end GetUserID()


/**
* get_group_id()
*
* gets the group id of the logged in user
* $user = the username of get info on
*/
function get_group_id($user = "")
{
	if (empty($user))
	{
		$user = $_SESSION["netmrgsess"]["username"];
	} // end if no user set
	if (IsLoggedIn())
	{
		$sql = "SELECT group_id FROM user WHERE user='$user'";
		$handle = db_query($sql);
		$row = db_fetch_array($handle);
		return $row["group_id"];
	} // end IsLoggedIn
	
	return false;
} // end get_group_id()


/**
* view_redirect()
*
* redirects the logged in user to the 'view' page
* if they only have 'single view' priviledges or they
* weren't on their way to somewhere else
*/
function view_redirect()
{
	if (empty($_SESSION["netmrgsess"]["redir"]) || ($_SESSION["netmrgsess"]["permit"] == 0))
	{
		header("Location: {$GLOBALS['netmrg']['webroot']}/device_tree.php");
		exit(0);
	}
	else
	{
		$redir = $_SESSION["netmrgsess"]["redir"];
		unset($_SESSION["netmrgsess"]["redir"]);
		header("Location: $redir");
		exit(0);
	} // end if we don't have a redir page or we do
} // end view_redirect()

?>
