<?php
/********************************************
* NetMRG Integrator
*
* users.php
* User Access Administration
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
check_auth($GLOBALS['PERMIT']["Admin"]);

if (empty($_REQUEST["action"])) $_REQUEST["action"] = "";

switch ($_REQUEST["action"])
{

	case "edit":
	case "add":
		display_edit();
		break;

	case "doedit":
	case "doadd":
		do_edit();
		break;

	case "dodelete":
		do_delete();
		break;

	case "deletemulti":
		do_deletemulti();
		break;

	default:
		display_page();

}


function do_edit()
{
	if (!empty($_REQUEST["pass"]))
	{
		if ($_REQUEST["pass"] != $_REQUEST["vpass"])
		{
			begin_page("users.php", "User Management - Error");
			echo "<div>Error: your passwords don't match; please go back and try again</div>";
			end_page();
			exit(0);
		} // end if pass doesn't match vpass
	} // end if pass

	if ($_REQUEST["user_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id='{$_REQUEST['user_id']}'";
	}
	
	$pass_cmd = "";
	if (!empty($_REQUEST["pass"]))
	{
		$pass_cmd = "pass = md5('{$_REQUEST['pass']}'), ";
	} // end if new password to set
	
	if (empty($_REQUEST["group_id"]))
	{
		$_REQUEST["group_id"] = 0;
	} // end if no group id set

	if (empty($_REQUEST["disabled"]))
	{
		$_REQUEST["disabled"] = 0;
	} // end if no disabled value set
	
	db_update("$db_cmd user SET user='{$_REQUEST['user']}',
		fullname='{$_REQUEST['fullname']}', $pass_cmd
		permit='{$_REQUEST['permit']}', group_id='{$_REQUEST['group_id']}', disabled='{$_REQUEST['disabled']}' $db_end");
		
	header("Location: {$_SERVER['PHP_SELF']}");
}

function do_delete()
{
	db_update("DELETE FROM user WHERE id='{$_REQUEST['user_id']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
}

function do_deletemulti()
{
	if (isset($_REQUEST["user"]))
	{
		while (list($key,$value) = each($_REQUEST["user"]))
		{
			db_update("DELETE FROM user WHERE id='$key'");
		}
	}
	header("Location: {$_SERVER['PHP_SELF']}");
}

function display_edit()
{

	begin_page("users.php", "User Management", 0, 'onLoad="enableGroup(document.editform.permit.value)"');
	echo '
<script language="JavaScript">
<!--
function enableGroup(val) {
if (val == 0) { // Single View Only
document.editform.group_id.disabled=false;
} else {
document.editform.group_id.disabled=true;
document.editform.group_id.value=0; // Root Group
}
}
-->
</script>
';
	
	if ($action == "add")
	{
		$user_id = 0;
	}
	else
	{
		$user_id = $_REQUEST["user_id"];
	} // end if add or not

	$user_results = db_query("SELECT * FROM user WHERE id='$user_id'");
	$user_row = db_fetch_array($user_results);
	
	make_edit_table("Edit User");
	make_edit_text("User ID:", "user", "25", "50", $user_row["user"]);
	make_edit_text("Full Name", "fullname", "25", "75", $user_row["fullname"]);
	if (!$GLOBALS["netmrg"]["externalAuth"])
	{
		make_edit_password("Password:", "pass", "25", "50", "");
		make_edit_password("Verify Password:", "vpass", "25", "50", "");
	} // end if not using external auth, show password form
	make_edit_select_from_array("Permit Type:", "permit", $GLOBALS['PERMIT_TYPES'], $user_row["permit"], 'onChange="enableGroup(this.value)"');
	make_edit_select_from_table("Group:", "group_id", "groups", $user_row["group_id"], "", array(0 => "-Root-"));
	make_edit_checkbox("Disabled", "disabled", $user_row["disabled"]);
	make_edit_hidden("action", "doedit");
	make_edit_hidden("user_id", $user_id);
	make_edit_submit_button();
	make_edit_end();
	end_page();
}

function display_page()
{
	begin_page("users.php", "User Management");
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<?php
	make_edit_hidden("action", "");
	make_display_table("Users", "", 
		array("text" => checkbox_toolbar()),
		array("text" => "User ID"),
		array("text" => "Name"),
		array("text" => "Permissions")
	); // end make_display_table();

	$user_results = db_query("SELECT * FROM user ORDER BY user.user");

	$user_total = db_num_rows($user_results);

	js_confirm_dialog("del", "Are you sure you want to delete user ", " ?", "{$_SERVER['PHP_SELF']}?action=dodelete&user_id=");

	// For each user
	for ($user_count = 1; $user_count <= $user_total; ++$user_count)
	{
		$user_row = db_fetch_array($user_results);
		$user_id  = $user_row["id"];

		make_display_item("editfield".(($user_count-1)%2),
			array("checkboxname" => "user", "checkboxid" => $user_id),
			array("text" => $user_row["user"]),
			array("text" => $user_row["fullname"]),
			array("text" => (get_permit($user_row["user"])==$GLOBALS['PERMIT']["Disabled"]) ? 'Disabled' : $GLOBALS['PERMIT_TYPES'][$user_row['permit']]),
			array("text" => formatted_link("Prefs", "user_prefs.php?uid=$user_id") . "&nbsp;" .
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&user_id=$user_id", "", "edit") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('".addslashes($user_row['user'])."', '{$user_row['id']}')", "", "delete")
				)
		); // end make_display_item();

	} // end users

	make_checkbox_command("", 5,
		array("text" => "Delete", "action" => "deletemulti", "prompt" => "Are you sure you want to delete the checked users?")
	); // end make_checkbox_command
	make_status_line("user", $user_count - 1);
	?>
	</table>
	</form>
	<?php
	end_page();
}

?>
