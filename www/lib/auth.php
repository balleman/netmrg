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

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root(). "lib/database.php");

function check_user_pass($user, $pass)
{
	$res = 0;
	$sql = "SELECT * FROM user WHERE user='$user' AND pass=ENCRYPT('$pass','$pass')";
	$handle = do_query($sql);
	$num = mysql_num_rows($handle);
	if ($num == 1)
	{ 
		$res = 1; 
	} 
	else 
	{ 
		$res = 0;
	}
	return $res;

} # end check_user_pass

function get_full_name($user)
{
	$q = do_query("SELECT fullname FROM user WHERE user='$user'");
	$r = mysql_fetch_array($q);
	return $r["fullname"];
}

function check_auth($level) {

global $user_name, $password, $REQUEST_URI;
$res = check_user_pass($user_name, $password);
if ($res != 1) {
	# Hacker Alert!
	setcookie("redir",$REQUEST_URI);
	header("Location: ./login.php?action=invalid");
	exit;
	} else {
	if (get_permit() < $level) {
		#setcookie("redir",$REQUEST_URI);
		header("Location: ./login.php?action=denied");
		exit;
		}
	}

} #end check_auth

function view_check_auth() {
global $user_name, $password, $pos_id, $pos_id_type, $REQUEST_URI;
check_auth(0);
$handle = do_query("SELECT * FROM user WHERE user='$user_name' AND pass=ENCRYPT('$password','$password')");
$row = mysql_fetch_array($handle);
if (!((($row["view_id"] == $pos_id) && ($row["view_type"] == $pos_id_type)) || (get_permit() > 0)))
	{
	setcookie("redir",$REQUEST_URI);
	header("Location: ./login.php?action=denied");
	exit;
	}

} # end view_check_auth

function cache_auth() {
global $user_name, $password;
setcookie("user_name", $user_name);
setcookie("password", $password);
} # end save_auth

function get_permit() {
global $user_name, $password;
$sql = "SELECT * FROM user WHERE user='$user_name' AND pass=ENCRYPT('$password','$password')";
$handle = do_query($sql);
$row = mysql_fetch_array($handle);
return $row["permit"];
}

function view_redirect() {
global $user_name, $password, $redir;
$sql = "SELECT * FROM user WHERE user='$user_name' AND pass=ENCRYPT('$password','$password')";
$handle = do_query($sql);
$row = mysql_fetch_array($handle);
if (($redir == "") || (get_permit() == 0))
	{ header("Location: ./view.php?pos_id_type=" . $row["view_type"] . "&pos_id=" . $row["view_id"]); }
	else
	{ header("Location: $redir"); 
	  setcookie("redir", "", time() - 3600); }
}

?>
