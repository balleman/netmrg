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

// if we need to login
if (!empty($_REQUEST["user_name"]))
{
	if (check_user_pass($_REQUEST["user_name"], $_REQUEST["password"]))
	{
		$_SESSION["netmrgsess"]["username"] = $_REQUEST["user_name"];
		$_SESSION["netmrgsess"]["password"] = $_REQUEST["password"];
		$_SESSION["netmrgsess"]["remote_addr"] = $_SERVER["REMOTE_ADDR"];
		$_SESSION["netmrgsess"]["permit"] = get_permit();
		$_SESSION["netmrgsess"]["accessTime"] = time();
		view_redirect();
	}
	else
	{
		$login_error = "Invalid Username or Password";
	} // end if the username & password is valid or not
}// end if there was a username
?>


<?
begin_page("login.php", "Login");
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
<form action="./login.php" method="post">
<table>
	<tr><td>User:</td><td><input type="text" name="user_name"></td></tr>
	<tr><td>Password:</td><td><input type="password" name="password"></td></tr>
	<tr><td></td><td align="right"><input type="submit" value="Login"></td></tr>
</table>
</form>

<?php
end_page();
?>
