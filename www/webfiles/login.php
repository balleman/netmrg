<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Login Page                            #
#           login.php                                  #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");


$login_valid = false;
$login_error = "";

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "logout")
{
	unset($_SESSION["netmrg"]);
	unset($action);
} // end if the action is to logout

if (!empty($_REQUEST["user_name"])) {
	if (check_user_pass($_REQUEST["user_name"], $_REQUEST["password"]))
	{
		$login_valid = true;
		$_SESSION["netmrg"]["username"] = $_REQUEST["user_name"];
		$_SESSION["netmrg"]["password"] = $_REQUEST["password"];
		$_SESSION["netmrg"]["remoteip"] = $_SERVER["REMOTE_ADDR"];
	}
	else
	{
		$login_error = "Invalid Username or Password";
	} // end if the username & password is valid or not
}// end if there was a username

if ($login_valid == true) {
	if (get_permit() == 0)
	{
		view_redirect();
		exit;
	}

	if (!empty($_REQEST["action"]))
	{
		header("Location: ./index.php?action=$action");
	}
	else
	{
		view_redirect();
	}
}
else
{

	begin_page();

?>
	<br><br>
	<font color="#000080" size="3"><strong>User Login</strong></font>
	<br><br>
<?
if (!empty($login_error)) {
?>
	<div class="error">
	<? echo "$login_error\n"; ?>
	</div>
<?
} // end if there was a login error
?>
	<form action="./login.php" method="post">
	<table>
	<tr><td>User:</td><td><input type="text" name="user_name"></td></tr>
	<tr><td>Password:</td><td><input type="password" name="password"></td></tr>
	<tr><td></td><td align="right"><input type="submit" value="Login"></td></tr>
	</table>
	</form>

<?

	end_page();

}

?>
