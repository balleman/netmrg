<?php
/********************************************
* NetMRG Integrator
*
* login.php
* Site Login Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");


$login_error = "";

// if we've already seen this page, go away
if (IsLoggedIn())
{
	view_redirect();
} // end if we've alread seen this page

/***** EXTERNAL AUTH *****/
// if external auth
if ($GLOBALS["netmrg"]["externalAuth"]
	&& !empty($_SERVER["PHP_AUTH_USER"])
	&& check_user($_SERVER["PHP_AUTH_USER"]))
{
	$_SESSION["netmrgsess"]["prettyname"] = $_SERVER["PHP_AUTH_USER"];
	$_SESSION["netmrgsess"]["username"] = $_SERVER["PHP_AUTH_USER"];
	$_SESSION["netmrgsess"]["password"] = "";
	$_SESSION["netmrgsess"]["accessTime"] = time();
	$_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
	$_SESSION["netmrgsess"]["permit"] = get_permit($_SESSION["netmrgsess"]["username"]);
	$_SESSION["netmrgsess"]["group_id"] = get_group_id();

	view_redirect();
} // end if external auth and usernames match
// if external auth and default user exists
else if ($GLOBALS["netmrg"]["externalAuth"]
	&& !empty($_SERVER["PHP_AUTH_USER"])
	&& check_user($GLOBALS["netmrg"]["defaultMapUser"]))
{
	$_SESSION["netmrgsess"]["prettyname"] = $_SERVER["PHP_AUTH_USER"];
	$_SESSION["netmrgsess"]["username"] = $GLOBALS["netmrg"]["defaultMapUser"];
	$_SESSION["netmrgsess"]["password"] = "";
	$_SESSION["netmrgsess"]["accessTime"] = time();
	$_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
	$_SESSION["netmrgsess"]["permit"] = get_permit($GLOBALS["netmrg"]["defaultMapUser"]);
	$_SESSION["netmrgsess"]["group_id"] = get_group_id($GLOBALS["netmrg"]["defaultMapUser"]);

	view_redirect();
} // end if external auth and usernames match
else if ($GLOBALS["netmrg"]["externalAuth"]
	&& !empty($_SERVER["PHP_AUTH_USER"])
	&& !check_user($_SERVER["PHP_AUTH_USER"]))
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
	exit(0);
} // end if external auth and usernames don't match


// if we need to login
if (!empty($_REQUEST["user_name"]))
{
	if (!$GLOBALS["netmrg"]["externalAuth"]
		&& check_user_pass($_REQUEST["user_name"], $_REQUEST["password"]))
	{
		$_SESSION["netmrgsess"]["prettyname"] = $_REQUEST["user_name"];
		$_SESSION["netmrgsess"]["username"] = $_REQUEST["user_name"];
		$_SESSION["netmrgsess"]["password"] = $_REQUEST["password"];
		$_SESSION["netmrgsess"]["accessTime"] = time();
		$_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
		$_SESSION["netmrgsess"]["permit"] = get_permit($_SESSION["netmrgsess"]["username"]);
		$_SESSION["netmrgsess"]["group_id"] = get_group_id();
		view_redirect();
	} // end if normal auth

	else
	{
		$login_error = "Invalid Username or Password";
	} // end if the username & password is valid or not
}// end if there was a username

begin_page("login.php", "Login", 0, '', array("login_focus.js"));
?>
<br><br>
<font color="#000080" size="3"><strong>User Login</strong></font>
<br><br>

<?php
if (!empty($login_error))
{
?>
	<div class="error">
	<?php echo "$login_error\n"; ?>
	</div>
<?php
} // end if there was a login error
?>
<form action="./login.php" method="post" name="lif">
<table>
	<tr><td>User:</td><td><input type="text" name="user_name"></td></tr>
	<tr><td>Password:</td><td><input type="password" name="password"></td></tr>
	<tr><td></td><td align="right"><input type="submit" value="Login"></td></tr>
</table>
</form>

<?php
end_page();
?>
