<?php
/********************************************
* NetMRG Integrator
*
* view.php
* A Page of Graphs associated with a
*   group or device.
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
//this line will need redone...
//view_check_auth($_REQUEST["pos_id"], $_REQUEST["pos_id_type"]);


if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "doadd")
	{
		do_update("INSERT INTO view SET
			object_id={$_REQUEST['object_id']},
			object_type='{$_REQUEST['object_type']}',
			graph_id={$_REQUEST['graph_id']},
			type='{$_REQUEST['type']}',
			separator_text='{$_REQUEST['separator_text']}',
			pos={$_REQUEST['pos']}");

		header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&edit=1");
	}
	elseif ($_REQUEST["action"] == "doedit")
	{
		do_update("UPDATE view SET 
			graph_id={$_REQUEST['graph_id']},
			type='{$_REQUEST['type']}',
			separator_text='{$_REQUEST['separator_text']}'
			WHERE id={$_REQUEST['id']}");
			
		header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&edit=1");
	}
	elseif ($_REQUEST["action"] == "dodelete")
	{
		$q = do_query("SELECT pos FROM view WHERE id=" . $_REQUEST["id"]);
		$r = mysql_fetch_array($q);

		$pos = $r["pos"];

		do_update("DELETE FROM view WHERE id=" . $_REQUEST['id']);

		do_update("UPDATE view SET pos = pos - 1
			WHERE object_id=" . $_REQUEST["object_id"] . "
			AND object_type='" . $_REQUEST["object_type"] . "'
			AND pos > " . $pos);

		header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&edit=1");
	}
	elseif ($_REQUEST["action"] == "move")
	{
		$query = do_query("
			SELECT 	graph_id, pos
			FROM 	view
			WHERE 	object_id={$_REQUEST['object_id']}
			AND 	object_type='{$_REQUEST['object_type']}'
			ORDER BY pos");

		for ($i = 0; $i < mysql_num_rows($query); $i++)
		{
			$row = mysql_fetch_array($query);

			if ($_REQUEST['direction'] == "up")
			{
				if (($_REQUEST['id'] - 1) == $i)
				{
					$next_row = mysql_fetch_array($query);
					do_update("UPDATE view SET pos = {$next_row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND graph_id = {$row['graph_id']}");
					do_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND graph_id = {$next_row['graph_id']}");
					break;
				}
			}
			else
			{
				if ($_REQUEST['id'] == $i)
				{
					$next_row = mysql_fetch_array($query);
					do_update("UPDATE view SET pos = {$next_row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND graph_id = {$row['graph_id']}");
					do_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND graph_id = {$next_row['graph_id']}");
					break;
				}
			}
		}

		header("Location: {$_SERVER['PHP_SELF']}?object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&edit=1");
		exit(0);

	} // end if we have an action to perform

} // end if an action was defined

begin_page("view.php", "View", 1);

if (empty($_REQUEST["action"]))
{
	$view_select =
		"SELECT		view.id, pos, graphs.name, graph_id, separator_text, subdev_id, pos, type
		 FROM		view
	 	 LEFT JOIN 	graphs ON view.graph_id=graphs.id
	 	 WHERE 		object_type='{$_REQUEST['object_type']}'
	  	 AND 		object_id={$_REQUEST['object_id']}
	 	 ORDER BY 	pos";

	$view_result = do_query($view_select);
	$num = mysql_num_rows($view_result);

	if (!isset($_REQUEST['edit']) || ($_REQUEST['edit'] == 0))
	{

		print("<div align=\"center\">");

		for ($i = 0; $i < $num; $i++)
		{
			$row = mysql_fetch_array($view_result);

			switch ($row['type'])
			{
				case "graph":
				print("<a href=\"enclose_graph.php?type=custom&id=" . $row["graph_id"] . "\">" .
					"<img border=\"0\" src=\"get_graph.php?type=custom&id=" . $row["graph_id"] . "\"></a><br>");
				break;

				case "separator":
				print("<table width='100%' bgcolor='#0011AA'><tr><td><b><font color='AAAAAA'>" . $row["separator_text"] . "</font></b></td></tr></table>");
				break;
			}

		}

		print("</div>");

		if (get_permit() > 1)
		{
			print(formatted_link("Edit", "{$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&edit=1"));

		}

	}
	else
	{
		js_confirm_dialog("del", "Do you want to remove ", " from this view?", "{$_SERVER['PHP_SELF']}?action=dodelete&object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&id=");

		make_display_table("Edit View", 
			"{$_SERVER['PHP_SELF']}?object_type=".$_REQUEST["object_type"]."&object_id=".$_REQUEST["object_id"]."&pos=" . ($num + 1) . "&action=add",
			array("text" => "Item"),
			array("text" => "Type")
		); // end make_display_table();

		for ($i = 0; $i < $num; $i++)
		{
			if ($i == 0)
			{
				$move_up = formatted_link_disabled("Move Up");
			}
			else
			{
				$move_up = formatted_link("Move Up", "{$_SERVER['PHP_SELF']}?action=move&direction=up&object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&id=$i");
			}

			if ($i == ($num - 1))
			{
				$move_down = formatted_link_disabled("Move Down");
			}
			else
			{
				$move_down = formatted_link("Move Down", "{$_SERVER['PHP_SELF']}?action=move&direction=down&object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&id=$i");
			}

			$row = mysql_fetch_array($view_result);

			switch ($row['type'])
			{
				case 'graph':
				$name = $row['name'];
				$extra_options = formatted_link("Edit Graph", "graph_items.php?graph_id={$row['graph_id']}");
				break;

				case 'separator':
				$name = $row['separator_text'];
				$extra_options = "";
				break;

				default:
				$extra_options = "";
			}

			make_display_item("editfield".($i%2),
				array("text" => $name),
				array("text" => ucfirst($row["type"])),
				array("text" => $move_up . "&nbsp;" .
					$move_down . "&nbsp;" .
					formatted_link("Edit", "{$_SERVER['PHP_SELF']}?id={$row['id']}&action=edit") . "&nbsp;" .
					formatted_link("Delete","javascript:del('".addslashes($row['name'])."', '{$row['id']}')") . "&nbsp;" .
					$extra_options)
			); // end make_display_item();
		}

		print("</table>");
		print(formatted_link("Done Editing", "{$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}"));

	}
}


if (!empty($_REQUEST["action"]))
{
	if (($_REQUEST["action"] == "add") || ($_REQUEST["action"] == "edit"))
	{
		
		switch ($_REQUEST["action"])
		{
			case "add":
			$row["type"] = "graph";
			$row["graph_id"] = 0;
			$row["separator_text"] = "";
			break;

			case "edit":
			$q = do_query("SELECT * FROM view WHERE id={$_REQUEST['id']}");
			$row = mysql_fetch_array($q);
			break;
		}

		make_edit_table("Add Item");
		make_edit_select_from_array("Item Type:", "type", $VIEW_ITEM_TYPES, $row["type"]);
		make_edit_group("Custom Graph");
		make_edit_select_from_table("Graph:", "graph_id", "graphs", $row["graph_id"]);
		make_edit_group("Template Graph");
		make_edit_group("Separator");
		make_edit_text("Separator Text:", "separator_text", "40", "100", $row["separator_text"]);
		switch ($_REQUEST["action"])
		{
			case "add":
			make_edit_hidden("object_id", $_REQUEST["object_id"]);
			make_edit_hidden("object_type", $_REQUEST["object_type"]);
			make_edit_hidden("action", "doadd");
			make_edit_hidden("pos", $_REQUEST['pos']);
			break;
			
			case "edit":
			make_edit_hidden("action", "doedit");
			make_edit_hidden("id", $_REQUEST['id']);
			make_edit_hidden("object_id", $row["object_id"]);
			make_edit_hidden("object_type", $row["object_type"]);
			//print_r($row);
			break;
		}
		make_edit_submit_button();
		make_edit_end();
	}
} // end if action is defined


end_page();

?>
