<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Authentication and Permissions Module      #
#           auth.php                                   #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@netmrg.net - www.netmrg.net                #
#                                                      #
########################################################


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
	$auth_select = "SELECT 1 FROM user WHERE user='$user' AND pass=ENCRYPT('$pass', pass)";
	$auth_result = do_query($auth_select);
	if (mysql_num_rows($auth_result) > 0) { 
		$auth_valid = true;
	} else 	{ 
		$auth_valid = false;
	} # end if we have a result or not

	return $auth_valid;
} // end check_user_pass()


/**
* IsLoggedIn();
*
* verifies a username and password in the session 
* against what's in the database
* and that the user isn't spoofing their ip
*/
function IsLoggedIn()
{
	if (check_user_pass($_SESSION["netmrgsess"]["username"], $_SESSION["netmrgsess"]["password"]) && 
		$_SESSION["netmrgsess"]["remote_addr"] == $_SERVER["REMOTE_ADDR"]) {
		return true;
	} # end if the username/password checks out and the ips match
	return false;
} // end IsLoggedIn();


function get_full_name($user)
{
	$q = do_query("SELECT fullname FROM user WHERE user='$user'");
	$r = mysql_fetch_array($q);
	return $r["fullname"];
} // end get_full_name()


function check_auth($level)
{
	$valid_user = check_user_pass($_SESSION["netmrgsess"]["username"], $_SESSION["netmrgsess"]["password"]);

	if ($valid_user == false) {
		# Hacker Alert!
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/login.php?action=invalid");
		exit;

	} else if (get_permit() < $level) {
		header("Location: {$GLOBALS['netmrg']['webroot']}/login.php?action=denied");
		exit;

	} # end if this is a valid user
} // end check_auth()


function view_check_auth()
{
	global $pos_id, $pos_id_type;
	check_auth(0);
	$handle = do_query("SELECT * FROM user WHERE user='".$_SESSION["netmrgsess"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrgsess"]["password"]."',pass)");
	$row = mysql_fetch_array($handle);
	if (!(($row["view_id"] == $pos_id && $row["view_type"] == $pos_id_type) || get_permit() > 0))
	{
		$_SESSION["netmrgsess"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: {$GLOBALS['netmrg']['webroot']}/login.php?action=denied");
		exit;
	}
} // end view_check_auth()


/**
* DEPRICATED
*/
function cache_auth()
{
} // end save_auth()


function get_permit()
{
	if (!empty($_SESSION["netmrgsess"]["username"]) && !empty($_SESSION["netmrgsess"]["password"])) {
		$sql = "SELECT * FROM user WHERE user='".$_SESSION["netmrgsess"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrgsess"]["password"]."',pass)";
		$handle = do_query($sql);
		$row = mysql_fetch_array($handle);
		return $row["permit"];
	} // end if there is somebody logged in, get their permissions

	return false;
} // end get_permit()


function view_redirect()
{
	$sql = "SELECT * FROM user WHERE user='".$_SESSION["netmrgsess"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrgsess"]["password"]."',pass)";
	$handle = do_query($sql);
	$row = mysql_fetch_array($handle);
	if (empty($_SESSION["netmrgsess"]["redir"]) || (get_permit() == 0)) {
		header("Location: {$GLOBALS['netmrg']['webroot']}/view.php?pos_id_type={$row['view_type']}&pos_id={$row['view_id']}");
	} else {
		$redir = $_SESSION["netmrgsess"]["redir"];
		unset($_SESSION["netmrgsess"]["redir"]);
		header("Location: $redir");
	} // end if we don't have a redir page or we do
} // end view_redirect()

?>
