<?

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

require_once("../include/config.php");
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root(). "lib/database.php");


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
	if (mysql_num_rows($auth_result) > 0)
	{ 
		$auth_valid = true;
	} 
	else 
	{ 
		$auth_valid = false;
	}

	return $auth_valid;
} // end check_user_pass()


function get_full_name($user)
{
	$q = do_query("SELECT fullname FROM user WHERE user='$user'");
	$r = mysql_fetch_array($q);
	return $r["fullname"];
} // end get_full_name()


function check_auth($level)
{
	$valid_user = false;

	if (!empty($_SESSION["netmrg"]["username"]) && !empty($_SESSION["netmrg"]["password"])) {
		$valid_user = check_user_pass($_SESSION["netmrg"]["username"], $_SESSION["netmrg"]["password"]);
 	} // end if a username and password has been set, try to auth

	if ($valid_user == false) {
		# Hacker Alert!
		setcookie("redir", $_SERVER["REQUEST_URI"]);
		header("Location: ./login.php?action=invalid");
		exit;

	} else if (get_permit() < $level) {
		#setcookie("redir",$REQUEST_URI);
		header("Location: ./login.php?action=denied");
		exit;

	}
} // end check_auth()


function view_check_auth()
{
	global $pos_id, $pos_id_type;
	check_auth(0);
	$handle = do_query("SELECT * FROM user WHERE user='".$_SESSION["netmrg"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrg"]["password"]."',pass)");
	$row = mysql_fetch_array($handle);
	if (!((($row["view_id"] == $pos_id) && ($row["view_type"] == $pos_id_type)) || (get_permit() > 0)))
	{
		$_SESSION["netmrg"]["redir"] = $_SERVER["REQUEST_URI"];
		header("Location: ./login.php?action=denied");
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
	if (!empty($_SESSION["netmrg"]["username"]) && !empty($_SESSION["netmrg"]["password"])) {
		$sql = "SELECT * FROM user WHERE user='".$_SESSION["netmrg"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrg"]["password"]."',pass)";
		$handle = do_query($sql);
		$row = mysql_fetch_array($handle);
		return $row["permit"];
	} // end if there is somebody logged in, get their permissions
	return false;
} // end get_permit()

function view_redirect()
{
	$sql = "SELECT * FROM user WHERE user='".$_SESSION["netmrg"]["username"]."' AND pass=ENCRYPT('".$_SESSION["netmrg"]["password"]."',pass)";
	$handle = do_query($sql);
	$row = mysql_fetch_array($handle);
	if (empty($_SESSION["netmrg"]["redir"]) || (get_permit() == 0))
	{
		header("Location: ./view.php?pos_id_type=" . $row["view_type"] . "&pos_id=" . $row["view_id"]);
	}
	else
	{
		$redir = $_SESSION["netmrg"]["redir"];
		unset($_SESSION["netmrg"]["redir"]);
		header("Location: $redir");
	}
} // end view_redirect()

?>
