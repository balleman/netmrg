<?php
/********************************************
* NetMRG Integrator
*
* conditions.php
* Conditions Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case "edit":
			display_edit();
		 	break;
		case "add":
			display_edit();
		 	break;
		case "doedit":
			do_edit();
	 		break;
		case "dodelete":
			do_delete();
			break;
	} // end switch action
}
else
{
	do_display();
} // end if action set


function do_display()
{

	// Display a list

	check_auth(1);
	begin_page("conditions.php", "Conditions");
	DrawGroupNavHistory("event", $_REQUEST["event_id"]);

	$query = db_query("SELECT * FROM conditions WHERE event_id = {$_REQUEST['event_id']} ORDER BY id");
	$rows = db_num_rows($query);
	if ($rows == 0)
	{
		$nologic = "&nologic=1";
	}
	else
	{
		$nologic = "";
	}

	js_confirm_dialog("del", "Are you sure you want to delete condition ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&event_id={$_REQUEST['event_id']}&id=");
	make_display_table("Conditions", "{$_SERVER['PHP_SELF']}?action=add&event_id={$_REQUEST['event_id']}$nologic", 
		array("text" => "Condition")
	); // end make_display_table();

	for ($i = 0; $i < $rows; $i++)
	{
		$row = db_fetch_array($query);
		$condition_name = $GLOBALS['VALUE_TYPES'][$row['value_type']] . "&nbsp;" . $GLOBALS['CONDITIONS'][$row['condition']] . "&nbsp;" . $row['value'];
		if ($i != 0)
		{
			$condition_name = $GLOBALS['LOGIC_CONDITIONS'][$row['logic_condition']] . "&nbsp;" . $condition_name;
			$nologic = "";
		}
		else
		{
			$nologic = "&nologic=1";
		}
		make_display_item("editfield".($i%2),
			array("text" => $condition_name),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id={$row['id']}$nologic") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . $condition_name . "','" . $row['id'] . "')"))
		); // end make_display_item();
	}
	?>
	</table>
	<?php
	end_page();
} // end do_display();


function display_edit()
{
	check_auth(2);
	begin_page("conditions.php", "Edit Conditions");

	if ($_REQUEST['action'] == "add")
	{
		$row['id'] 		= 0;
		$row['event_id']	= $_REQUEST['event_id'];
		$row['logic_condition'] = 0;
		$row['condition']	= 1;
		$row['value']		= 0;
		$row['value_type']	= 0;
	}
	else
	{
		$query = db_query("SELECT * FROM conditions WHERE id={$_REQUEST['id']}");
		$row   = db_fetch_array($query);
	}

	make_edit_table("Edit Condition");
	if (!isset($_REQUEST['nologic']))
	{
		make_edit_select_from_array("Logical Operation:", "logic_condition", $GLOBALS['LOGIC_CONDITIONS'], $row['logic_condition']);
	}
	else
	{
		make_edit_hidden("logic_condition", "0");
	}
        make_edit_select_from_array("Value Type:", "value_type", $GLOBALS['VALUE_TYPES'], $row['value_type']);
	make_edit_select_from_array("Condition:", "condition", $GLOBALS['CONDITIONS'], $row['condition']);
	make_edit_text("Value:", "value", 5, 10, $row['value']);
	make_edit_hidden("event_id", $row['event_id']);
	make_edit_hidden("id", $row['id']);
	make_edit_hidden("action", "doedit");
	make_edit_submit_button();
	make_edit_end();
	end_page();

} // end display_edit();


function do_edit()
{
	check_auth(2);

        if ($_REQUEST['id'] == 0)
	{
                $pre  = "INSERT INTO";
		$post = ", event_id={$_REQUEST['event_id']}";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE id = {$_REQUEST['id']}";
	}

        db_update("$pre conditions SET logic_condition={$_REQUEST['logic_condition']}, value_type={$_REQUEST['value_type']}, condition={$_REQUEST['condition']}, value={$_REQUEST['value']} $post");

        header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}");
} // end do_edit();


function do_delete()
{
	check_auth(2);

        db_update("DELETE FROM conditions WHERE id = {$_REQUEST['id']}");

	header("Location: {$_SERVER['PHP_SELF']}?event_id={$_REQUEST['event_id']}");
} // end do_delete();


?>
