<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           User Access Administration                 #
#           users.php                                  #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(3);
begin_page();

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {

if (($action == "doedit") || ($action == "doadd")) {

	if ($user_id == 0) {
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$user_id";
	}

	if ($pass != "") {
		$pass_cmd = "pass=ENCRYPT('$pass','$pass'), ";
		} else {
		$pass_cmd = "";
		}

	do_update("$db_cmd user SET user=\"$user\", fullname=\"$fullname\", $pass_cmd permit=$permit, view_type=$view_type, view_id=$view_id $db_end");
} // done editing

if ($action == "dodelete")
{
        do_update("DELETE FROM user WHERE id=$user_id");
} // done deleting


// Display a list

make_display_table("Users","User ID","","Name", "", "Permissions","");

$user_results = do_query("
SELECT user.id, user.user, user.view_type, user.view_id, user.fullname, user.permit, permit_type.name FROM user
LEFT JOIN permit_type ON user.permit=permit_type.id ORDER BY user.user");

$user_total = mysql_num_rows($user_results);

for ($user_count = 1; $user_count <= $user_total; ++$user_count) 
{
        // For each user

        $user_row = mysql_fetch_array($user_results);
        $user_id  = $user_row["id"];

        make_display_item($user_row["user"],"",
		        $user_row["fullname"], "",
	        	$user_row["name"],"",
                        formatted_link("Edit", "$SCRIPT_NAME?action=edit&user_id=$user_id") . "&nbsp;" .
		        formatted_link("Delete", "$SCRIPT_NAME?action=delete&user_id=$user_id"), "");

} // end users

?>
</table>
<?
} // End if no action

if (($action == "edit") || ($action == "add"))
{
        // Display editing screen

        if ($action == "add") { $user_id = 0; }

        $user_results = do_query("SELECT * FROM user WHERE id=$user_id");
        $user_row = mysql_fetch_array($user_results);

        make_edit_table("Edit User");
        make_edit_text("User ID:","user","25","50",$user_row["user"]);
        make_edit_text("Full Name", "fullname", "25", "75", $user_row["fullname"]);
        make_edit_password("Password:", "pass", "25", "50", "");
        make_edit_select_from_table("Permit Type:","permit","permit_type",$user_row["permit"]);
        make_edit_text("View Type","view_type", "5", "5", $user_row["view_type"]);
        make_edit_text("View ID", "view_id", "5", "5", $user_row["view_id"]);
        make_edit_hidden("action","doedit");
        make_edit_hidden("user_id",$user_id);
        make_edit_submit_button();
        make_edit_end();


} // End editing screen

if ($action == "delete") {
# Display delete confirmation
?>
<font size="4" color="#800000">Confirm Delete</font><br><br>

Are you sure you want to delete this test?

<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="Yes">
<input type="hidden" name="user_id" value="<? print("$user_id"); ?>">
<input type="hidden" name="action" value="dodelete">
</form>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="submit" value="No">
</form>

<?

} # end delete confirmation

end_page();

?>
