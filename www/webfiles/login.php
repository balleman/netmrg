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

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");

if ($action == "logout")
{
	setcookie("user_name", "", time() - 3600);
	setcookie("password", "", time() - 3600);
	setcookie("redir", "", time() - 3600);
	$user_name = "";
	$password = "";
	unset($action);

}

if ((isset($user_name)) && (check_user_pass($user_name, $password)))
{
	cache_auth();

	if (get_permit() == 0)
	{
		view_redirect();
		exit;
	}

	if ($action != "")
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
	<br>
	<br>
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
