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


$login_valid = false;
$login_error = "";

if (IsLoggedIn()) {
	view_redirect();
} # end if we've alread seen this page

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "logout")
{
	$_SESSION["netmrg"]["username"] = "";
	$_SESSION["netmrg"]["password"] = "";
	$_SESSION["netmrg"]["remote_addr"] = "";
} // end if the action is to logout

if (!empty($_REQUEST["user_name"])) {
	if (check_user_pass($_REQUEST["user_name"], $_REQUEST["password"]))
	{
		$login_valid = true;
		$_SESSION["netmrg"]["username"] = $_REQUEST["user_name"];
		$_SESSION["netmrg"]["password"] = $_REQUEST["password"];
		$_SESSION["netmrg"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
	}
	else
	{
		$login_error = "Invalid Username or Password";
	} // end if the username & password is valid or not
}// end if there was a username

if ($login_valid == true) {
	if (get_permit() == 0) {
		view_redirect();
	} # end if we are permitted

	if (!empty($_REQEST["action"])) {
		if ($_REQUEST["action"] == "denied") {
			$login_error = "Your last action was DENIED";
		} else if ($_REQUEST["action"] == "invalid") {
			$login_error = "Your last action was INVALID";
		} # end if action was denied or invalid
	} else {
		view_redirect();
	}
}
else
{

	begin_page("login.php");

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
