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
view_check_auth($_REQUEST["pos_id"], $_REQUEST["pos_id_type"]);

if (!empty($_REQUEST["full_edit"]))
{
	$full_edit = $_REQUEST["full_edit"];
}
else
{
	$full_edit = 0;
} // end if full_edit was set or not

if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "doadd")
	{
		do_update("INSERT INTO view SET
			pos_id=" . $_REQUEST["pos_id"] . ",
			pos_id_type=" . $_REQUEST["pos_id_type"] . ",
			graph_id=" . $_REQUEST["graph_id"] . ",
			graph_id_type=\"custom\",
			pos={$_REQUEST['pos']}");
		$_REQUEST["action"] = "";
		$full_edit = 1;

	}
	elseif ($_REQUEST["action"] == "dodelete")
	{
		$q = do_query("SELECT pos FROM view
			WHERE pos_id=" . $_REQUEST["pos_id"] . "
			AND pos_id_type=" . $_REQUEST["pos_id_type"] . "
			AND graph_id=" . $_REQUEST["graph_id"]);
		
		$r = mysql_fetch_array($q);
		
		$pos = $r["pos"];

		do_update("DELETE FROM view
			WHERE pos_id=" . $_REQUEST["pos_id"] . "
			AND pos_id_type=" . $_REQUEST["pos_id_type"] . "
			AND graph_id=" . $_REQUEST["graph_id"]);

		do_update("UPDATE view SET pos = pos - 1
			WHERE pos_id=" . $_REQUEST["pos_id"] . "
			AND pos_id_type=" . $_REQUEST["pos_id_type"] . "
			AND pos > " . $pos);


		$_REQUEST["action"] = "";
		$full_edit = 1;

	}
	elseif ($_REQUEST["action"] == "move")
	{
		$query = do_query("
			SELECT graph_id, pos
			FROM view
			WHERE pos_id={$_REQUEST['pos_id']}
			AND pos_id_type={$_REQUEST['pos_id_type']}
			ORDER BY pos");

		for ($i = 0; $i < mysql_num_rows($query); $i++)
		{
			$row = mysql_fetch_array($query);

			if ($_REQUEST['direction'] == "up")
			{
				if (($_REQUEST['id'] - 1) == $i)
				{
					$next_row = mysql_fetch_array($query);
					do_update("UPDATE view SET pos = {$next_row['pos']} WHERE pos_id = {$_REQUEST['pos_id']} AND pos_id_type = {$_REQUEST['pos_id_type']} AND graph_id = {$row['graph_id']}");
					do_update("UPDATE view SET pos = {$row['pos']} WHERE pos_id = {$_REQUEST['pos_id']} AND pos_id_type = {$_REQUEST['pos_id_type']} AND graph_id = {$next_row['graph_id']}");
					break;
				}
			}
			else
			{
				if ($_REQUEST['id'] == $i)
				{
					$next_row = mysql_fetch_array($query);
					do_update("UPDATE view SET pos = {$next_row['pos']} WHERE pos_id = {$_REQUEST['pos_id']} AND pos_id_type = {$_REQUEST['pos_id_type']} AND graph_id = {$row['graph_id']}");
					do_update("UPDATE view SET pos = {$row['pos']} WHERE pos_id = {$_REQUEST['pos_id']} AND pos_id_type = {$_REQUEST['pos_id_type']} AND graph_id = {$next_row['graph_id']}");
					break;
				}
			}
		}

		header("Location: {$_SERVER['PHP_SELF']}?pos_id={$_REQUEST['pos_id']}&pos_id_type={$_REQUEST['pos_id_type']}&full_edit=1");
		exit(0);

	} // end if we have an action to perform

} // end if an action was defined

begin_page("view.php", "View", 1);

$view_select = "
    SELECT * FROM view
    LEFT JOIN graphs ON view.graph_id=graphs.id
    WHERE pos_id_type=".$_REQUEST["pos_id_type"]."
    AND pos_id=".$_REQUEST["pos_id"]."
    ORDER BY pos";
$view_result = do_query($view_select);
$num = mysql_num_rows($view_result);

if (empty($_REQUEST["action"]))
{
	if ($full_edit != 1)
	{

		print("<div align=\"center\">");

		for ($i = 0; $i < $num; $i++)
		{
			$row = mysql_fetch_array($view_result);

			if ($row["graph_id_type"] == 0)
			{
				print("<a href=\"enclose_graph.php?type=" . $row["graph_id_type"] . "&id=" . $row["graph_id"] . "\">" .
					"<img border=\"0\" src=\"get_graph.php?type=" . $row["graph_id_type"] . "&id=" . $row["graph_id"] . "\"></a><br>");
			}

			if ($row["graph_id_type"] == 10)
			{
				print("<table width='100%' bgcolor='" . $row["separator_color"] . "'><tr><td><b><font color='AAAAAA'>" . $row["separator_text"] . "</font></b></td></tr></table>");
			}

		}

		print("</div>");

		if (get_permit() > 1)
		{
			print(formatted_link("Edit", "view.php?pos_id_type={$_REQUEST['pos_id_type']}&pos_id={$_REQUEST['pos_id']}&full_edit=1"));

		}

	}
	else
	{
		js_confirm_dialog("del", "Do you want to remove ", " from this view?", "{$_SERVER['PHP_SELF']}?action=dodelete&pos_id_type={$_REQUEST['pos_id_type']}&pos_id={$_REQUEST['pos_id']}&graph_id=");

		$custom_add_link = "view.php?pos_id_type=".$_REQUEST["pos_id_type"]."&pos_id=".$_REQUEST["pos_id"]."&pos=" . ($num + 1) . "&action=add";
		make_display_table("Edit View","Graph","");

		for ($i = 0; $i < $num; $i++)
		{
			if ($i == 0)
			{
				$move_up = formatted_link_disabled("Move Up");
			}
			else
			{
				$move_up = formatted_link("Move Up", "{$_SERVER['PHP_SELF']}?action=move&direction=up&pos_id={$_REQUEST['pos_id']}&pos_id_type={$_REQUEST['pos_id_type']}&id=$i");
			}

			if ($i == ($num - 1))
			{
				$move_down = formatted_link_disabled("Move Down");
			}
			else
			{
				$move_down = formatted_link("Move Down", "{$_SERVER['PHP_SELF']}?action=move&direction=down&pos_id={$_REQUEST['pos_id']}&pos_id_type={$_REQUEST['pos_id_type']}&id=$i");
			}

			$row = mysql_fetch_array($view_result);
			make_display_item($row["name"],"",
				$move_up . "&nbsp;" .
				$move_down . "&nbsp;" .
				formatted_link("Delete","javascript:del('{$row['name']}', '{$row['graph_id']}')") . "&nbsp;" .
				formatted_link("Edit Graph", "graph_items.php?graph_id={$row['graph_id']}"), "");
		}

		print("</table>");
		print(formatted_link("Done Editing", "view.php?pos_id_type={$_REQUEST['pos_id_type']}&pos_id={$_REQUEST['pos_id']}&full_edit=0"));

	}
}


if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "add")
	{
		make_edit_table("Add Graph to View");
		make_edit_select_from_table("Graph:","graph_id","graphs",0);
		make_edit_hidden("pos_id",$_REQUEST["pos_id"]);
		make_edit_hidden("pos_id_type",$_REQUEST["pos_id_type"]);
		make_edit_hidden("action","doadd");
		make_edit_hidden("pos", $_REQUEST['pos']);
		make_edit_submit_button();
		make_edit_end();
	}
} // end if action is defined


end_page();

?>
