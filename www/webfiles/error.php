<?php
/********************************************
* NetMRG Integrator
*
* error.php
* Site Error Page
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/


require_once("../include/config.php");

if (!$GLOBALS["netmrg"]["externalAuth"] && !IsLoggedIn())
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");
	exit();
} // end if not externalauth and not logged in, goto login

$errorstring = "";

// if we have an action for this error page, output the error message
if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "denied")
	{
		$errorstring = "Your last action was DENIED";
	}

	else if ($_REQUEST["action"] == "invalid")
	{
		$errorstring = "Your last action was INVALID";
	} # end if action was denied or invalid
}
else
{
	view_redirect();
}


// display the page
	begin_page("error.php", "Error");
?>
	<div class="error">
	<?php echo "$errorstring\n"; ?>
	</div>
<?php
	end_page();

?>
