<?php
/********************************************
* NetMRG Integrator
*
* users.php
* User Access Administration
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(3);

if (!empty($_REQUEST["action"]))
{
	$action = $_REQUEST["action"];
}
else
{
	$action = "";
	unset($action);
} // end if action set or not


if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd"))
{

if (!empty($action) && ($action == "doedit" || $action == "doadd"))
{

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

	if (!empty($_REQUEST["pass"]))
	{
		$pass_cmd = "pass = ENCRYPT('{$_REQUEST['pass']}',md5('{$_REQUEST['pass']}')), ";
	}
	else
	{
		$pass_cmd = "";
	}

	db_update("$db_cmd user SET user='{$_REQUEST['user']}',
		fullname='{$_REQUEST['fullname']}', $pass_cmd
		permit='{$_REQUEST['permit']}', group_id='{$_REQUEST['group_id']}' $db_end");
		
	header("Location: {$_SERVER['PHP_SELF']}");
	exit(0);
} // done editing

if (!empty($action) && $action == "dodelete")
{
	db_update("DELETE FROM user WHERE id='{$_REQUEST['user_id']}'");
	header("Location: {$_SERVER['PHP_SELF']}");
	exit(0);

} // done deleting


// Display a list

begin_page("users.php", "User Management");
make_display_table("Users", "", 
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
		array("text" => $user_row["user"]),
		array("text" => $user_row["fullname"]),
		array("text" => $GLOBALS['PERMIT_TYPES'][$user_row['permit']]),
		array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&user_id=$user_id") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('".addslashes($user_row['user'])."', '{$user_row['id']}')"))
	); // end make_display_item();

} // end users

?>
</table>
<?php
} // End if no action

if (!empty($action) && ($action == "edit" || $action == "add"))
{
// Display editing screen

	begin_page("users.php", "User Management");

	if ($action == "add")
	{
		$user_id = 0;
	}
	else
	{
		$user_id = $_REQUEST["user_id"];
	} // end if add or not

	$user_results = db_query("SELECT * FROM user WHERE id=$user_id");
	$user_row = db_fetch_array($user_results);

	make_edit_table("Edit User");
	make_edit_text("User ID:", "user", "25", "50", $user_row["user"]);
	make_edit_text("Full Name", "fullname", "25", "75", $user_row["fullname"]);
	make_edit_password("Password:", "pass", "25", "50", "");
	make_edit_select_from_array("Permit Type:", "permit", $GLOBALS['PERMIT_TYPES'], $user_row["permit"]);
	make_edit_select_from_table("Group:", "group_id", "groups", $user_row["group_id"], "", array(0 => "-Root-"));
	make_edit_hidden("action", "doedit");
	make_edit_hidden("user_id", $user_id);
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
