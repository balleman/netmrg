<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Events Editing Page                        #
#           events.php                                 #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = '';
}

if ((empty($_REQUEST['action'])) || ($_REQUEST['action'] == "doedit") || ($_REQUEST['action'] == "dodelete") || ($_REQUEST['action'] == "doadd"))
{

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

if ($_REQUEST['action'] == "doadd")
{
	check_auth(2);
	do_update("INSERT INTO events SET monitors_id=$mon_id, result=$result, condition=$mon_conditions, options=$mon_options, situation=$mon_situations, display_name=\"$display_name\"");
} // done adding

if ($_REQUEST['action'] == "doedit")
{
	check_auth(2);
	do_update("UPDATE events SET monitors_id=$mon_id, result=$result, condition=$mon_conditions, options=$mon_options, situation=$mon_situations, display_name=\"$display_name\" WHERE id=$event_id");
} // done editing

if ($_REQUEST['action'] == "dodelete")
{
	check_auth(2);
	delete_event($event_id);
} // done deleting


// Display a list

$title = "Events for " . get_monitor_name($_REQUEST['mon_id']);

$custom_add_link = "{$_SERVER['PHP_SELF']}?action=add&mon_id={$_REQUEST['mon_id']}";

begin_page();
js_confirm_dialog("del", "Are you sure you want to delete event ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&mon_id=$mon_id&event_id=");
//make_display_table("Events","Display Name","","Result to Trigger","","Trigger Condition","","When to Trigger","","Situation","");
make_display_table("Events",
			"Name", "",
			"Condition", "",
			"Trigger Options", "",
			"Situation", "",
			"Status", "");
?>
</table>
<?php
} // End if no action

end_page();

?>
