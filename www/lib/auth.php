<?php
/********************************************
* NetMRG Integrator
*
* auth.php
* Authentication and Permissions Module
*
* see doc/LICENSE for copyright information
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
			($GLOBALS["netmrg"]["externalAuth"] && check_user($_SESSION["netmrgsess"]["username"]))
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
* view_check_auth()
*
* called from the 'view.php' page
* checks that the user is allowed to see this page
*/
function view_check_auth($object_id, $object_type)
{
	check_auth(0);
	
	// the groups this object_id is in
	$object_id_groups = array();
	
	// check what groups this object_id is in
	switch ($object_type)
	{
		case "group" :
			array_push($object_id_groups, $object_id);
			break;
		
		case "device" :
			$object_id_groups = GetDeviceGroups($object_id);
			break;
		
		case "subdevice" :
			$object_id_groups = GetSubdeviceGroups($object_id);
			break;
	
	} // end switch object type
	
	if (!in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) 
		&& $_SESSION["netmrgsess"]["permit"] == 0)
	{
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
		exit;
	}
} // end view_check_auth()


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
	check_auth(0);
	
	// the groups this object_id is in
	$object_id_groups = array();
	
	switch ($type)
	{
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetMonitorGroups($id);
			break;
		
		case "template" :
			$object_id_groups = GetSubdeviceGroups($id);
			break;
		
		case "custom" :
			$object_id_groups = GetCustomGraphGroups($id);
			break;
	} // end switch graph type
	
	if (count($object_id_groups) == 1
		&& !in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) 
		&& $_SESSION["netmrgsess"]["permit"] == 0)
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
	check_auth(0);
	
	// the groups this object_id is in
	$object_id_groups = array();
	
	switch ($type)
	{
		case "mon" :
		case "tinymon" :
			$object_id_groups = GetMonitorGroups($id);
			break;
		
		case "template" :
			$object_id_groups = GetSubdeviceGroups($id);
			break;
		
		case "custom" :
			$object_id_groups = GetCustomGraphGroups($id);
			break;
	} // end switch graph type
	
	if (count($object_id_groups) == 1
		&& !in_array($_SESSION["netmrgsess"]["group_id"], $object_id_groups) 
		&& $_SESSION["netmrgsess"]["permit"] == 0)
	{
		readfile($GLOBALS["netmrg"]["fileroot"]."/www/img/access_denied.png");
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
* get_permit()
*
* gets the logged in user's permission level
*/
function get_permit()
{
	if (IsLoggedIn())
	{
		$sql = "SELECT permit FROM user WHERE user='".$_SESSION["netmrgsess"]["username"]."'";
		$handle = db_query($sql);
		$row = db_fetch_array($handle);
		return $row["permit"];
	} // end if there is somebody logged in, get their permissions

	return false;
} // end get_permit()

/**
* get_group_id()
*
* gets the group id of the logged in user
*/
function get_group_id()
{
	if (IsLoggedIn())
	{
		$sql = "SELECT group_id FROM user WHERE user='" . $_SESSION["netmrgsess"]["username"] . "'";
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
	$sql = "SELECT * FROM user WHERE user='".$_SESSION["netmrgsess"]["username"]."'";
	$handle = db_query($sql);
	$row = db_fetch_array($handle);
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
