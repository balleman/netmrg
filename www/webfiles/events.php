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

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case "edit":	display_edit(); break;
		case "add":	display_edit(); break;
	}
}
else
{
	do_display();
}

function do_display()
{

	// Display a list

	$title = "Events for " . get_monitor_name($_REQUEST['mon_id']);

	$GLOBALS['custom_add_link'] = "{$_SERVER['PHP_SELF']}?action=add&mon_id={$_REQUEST['mon_id']}";

	begin_page();
	js_confirm_dialog("del", "Are you sure you want to delete event ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&mon_id={$_REQUEST['mon_id']}&event_id=");
	make_display_table("Events",
				"Name", "",
				"Condition", "",
				"Trigger Options", "",
				"Situation", "",
				"Status", "");
	?>
	</table>
	<?php
	end_page();
}

function display_edit()
{
	begin_page();

	if ($_REQUEST['action'] == "add")
	{
		$row['id'] 		= 0;
		$row['mon_id'] 		= $_REQUEST['mon_id'];
		$row['name']		= "";
		$row['trigger_type']	= 2;
		$row['situation']	= 1;
	}
	else
	{
		$query = do_query("SELECT * FROM events WHERE id={$_REQUEST['id']}");
		$row   = mysql_fetch_array($query);
	}

	make_edit_table("Edit Event");
        make_edit_text("Name:", "name", "25", "100", $row['name']);
	make_edit_select_from_array("Trigger Type:", "trigger_type", $GLOBALS['TRIGGER_TYPES'], $row['trigger_type']);
        make_edit_select_from_array("Situation:", "situation", $GLOBALS['SITUATIONS'], $row['situation']);
	make_edit_hidden("mon_id", $row['mon_id']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();
	end_page();

}

?>
