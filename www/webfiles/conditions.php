<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Conditions Editing Page                    #
#           conditions.php                             #
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
		case "edit":		display_edit(); 	break;
		case "add":		display_edit(); 	break;
		case "doedit":		do_edit(); 		break;
		case "dodelete":	do_delete(); 		break;
	}
}
else
{
	do_display();
}

function do_display()
{

	// Display a list

	check_auth(1);
	begin_page();

	$GLOBALS['custom_add_link'] = "{$_SERVER['PHP_SELF']}?action=add&event_id={$_REQUEST['event_id']}";
	js_confirm_dialog("del", "Are you sure you want to delete condition ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&event_id={$_REQUEST['event_id']}&id=");
	make_display_table("Conditions", "Condition", "");
				

	$query = do_query("SELECT * FROM conditions WHERE event_id = {$_REQUEST['event_id']}");

	for ($i = 0; $i < mysql_num_rows($query); $i++)
	{
		$row = mysql_fetch_array($query);
		$condition_name = "test"; 
		make_display_item($condition_name, "", 
			formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . $condition_name . "','" . $row['id'] . "')"), "");
	}
	?>
	</table>
	<?php
	end_page();
}

function display_edit()
{
	check_auth(2);
	begin_page();

	if ($_REQUEST['action'] == "add")
	{
		$row['id'] 		= 0;
		$row['event_id']	= $_REQUEST['event_id'];
		$row['logic_condition'] = 0;
		$row['condition']	= 0;
		$row['value']		= 0;
	}
	else
	{
		$query = do_query("SELECT * FROM conditions WHERE id={$_REQUEST['id']}");
		$row   = mysql_fetch_array($query);
	}

	make_edit_table("Edit Condition");
	make_edit_select_from_array("Trigger Type:", "trigger_type", $GLOBALS['TRIGGER_TYPES'], $row['trigger_type']);
        make_edit_select_from_array("Situation:", "situation", $GLOBALS['SITUATIONS'], $row['situation']);
	make_edit_hidden("mon_id", $row['mon_id']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();
	end_page();

}

function do_edit()
{
	check_auth(2);

        if ($_REQUEST['id'] == 0)
	{
                $pre  = "INSERT INTO";
		$post = ", mon_id={$_REQUEST['mon_id']}";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE id = {$_REQUEST['id']}";
	}

        do_update("$pre events SET name = '{$_REQUEST['name']}', trigger_type={$_REQUEST['trigger_type']}, situation={$_REQUEST['situation']} $post");

        header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}");
}

function do_delete()
{
	check_auth(2);

        do_update("DELETE FROM events WHERE id = {$_REQUEST['id']}");
	
	header("Location: {$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}");
}
?>
