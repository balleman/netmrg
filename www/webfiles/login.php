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

// if external auth
if ($GLOBALS["netmrg"]["externalAuth"]
	&& !empty($_SERVER["PHP_AUTH_USER"])
	&& check_user(db_escape_string($_SERVER["PHP_AUTH_USER"])))
{
	$_SESSION["netmrgsess"]["username"] = db_escape_string($_SERVER["PHP_AUTH_USER"]);
	$_SESSION["netmrgsess"]["password"] = "";
	$_SESSION["netmrgsess"]["accessTime"] = time();
	$_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
	$_SESSION["netmrgsess"]["permit"] = get_permit($_SESSION["netmrgsess"]["username"]);
	$_SESSION["netmrgsess"]["group_id"] = get_group_id();

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
	$_REQUEST["user_name"] = db_escape_string($_REQUEST["user_name"]);
	$_REQUEST["password"] = db_escape_string($_REQUEST["password"]);
	if (!$GLOBALS["netmrg"]["externalAuth"]
		&& check_user_pass($_REQUEST["user_name"], $_REQUEST["password"]))
	{
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
?>

<script language="javascript">
<!--
function focusme()
{
        document.lif.user_name.focus();
}
-->
</script>

<?php
begin_page("login.php", "Login", 0, "onLoad=focusme()");
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
