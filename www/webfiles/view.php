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
if (isset($_REQUEST["object_id"]) && isset($_REQUEST["object_type"]))
{
	view_check_auth($_REQUEST["object_id"], $_REQUEST["object_type"]);
}

$slideshow = false;

if (empty($_REQUEST["action"])) $_REQUEST["action"] = "";

switch ($_REQUEST["action"])
{
	case "view":
	case "list":		do_view();			break;
	case "edit":
	case "add":			display_edit();		break;
	case "slideshow":	do_slideshow();		break;
	case "doadd":		do_add();			break;
	case "doedit":		do_edit();			break;
	case "dodelete":	do_delete();		break;
	case "move":		do_move();			break;
	default:			display_error();
}

function display_error()
{
	begin_page("view.php", "View");
	echo("An error occurred.");
	end_page();
}

function display_edit()
{
	check_auth(2);
	begin_page("view.php", "Edit View Item");

	switch ($_REQUEST["action"])
	{
		case "add":
		$row["type"] = "graph";
		$row["graph_id"] = 0;
		$row["separator_text"] = "";
		$row['subdev_id'] = 0;
		break;

		case "edit":
		$q = db_query("SELECT * FROM view WHERE id={$_REQUEST['id']}");
		$row = db_fetch_array($q);
		break;
	}

	make_edit_table("Add Item");
	make_edit_select_from_array("Item Type:", "type", $GLOBALS["VIEW_ITEM_TYPES"], $row["type"]);
	make_edit_group("Custom Graph");
	make_edit_select_from_table("Custom Graph:", "graph_id_custom", "graphs", $row["graph_id"], "", array(), array(), "type='custom'");
	make_edit_group("Template Graph");
	make_edit_select_from_table("Template Graph:", "graph_id_template", "graphs", $row["graph_id"], "", array(), array(), "type='template'");
	make_edit_select_subdevice($row["subdev_id"]);
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
	end_page();
}

function do_add()
{
	check_auth(2);
	$_REQUEST['graph_id'] = (($_REQUEST['type'] == 'graph') ? $_REQUEST['graph_id_custom'] : $_REQUEST['graph_id_template']);

	db_update("INSERT INTO view SET
		object_id={$_REQUEST['object_id']},
		object_type='{$_REQUEST['object_type']}',
		graph_id={$_REQUEST['graph_id']},
		type='{$_REQUEST['type']}',
		separator_text='{$_REQUEST['separator_text']}',
		pos={$_REQUEST['pos']},
		subdev_id={$_REQUEST['subdev_id']}");

	header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&action=list");
	exit(0);
}

function do_edit()
{
	check_auth(2);

	$_REQUEST['graph_id'] = (($_REQUEST['type'] == 'graph') ? $_REQUEST['graph_id_custom'] : $_REQUEST['graph_id_template']);

	db_update("UPDATE view SET
		graph_id={$_REQUEST['graph_id']},
		type='{$_REQUEST['type']}',
		separator_text='{$_REQUEST['separator_text']}',
		subdev_id={$_REQUEST['subdev_id']}
		WHERE id={$_REQUEST['id']}");

	header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&action=list");
	exit(0);
}

function do_delete()
{
	check_auth(2);

	$q = db_query("SELECT pos FROM view WHERE id=" . $_REQUEST["id"]);
	$r = db_fetch_array($q);

	$pos = $r["pos"];

	db_update("DELETE FROM view WHERE id=" . $_REQUEST['id']);

	db_update("UPDATE view SET pos = pos - 1
		WHERE object_id=" . $_REQUEST["object_id"] . "
		AND object_type='" . $_REQUEST["object_type"] . "'
		AND pos > " . $pos);

	header("Location: {$_SERVER['PHP_SELF']}?object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}&action=list");
	exit(0);
}

function do_move()
{
	check_auth(2);

	$query = db_query("
		SELECT 	id, pos
		FROM 	view
		WHERE 	object_id={$_REQUEST['object_id']}
		AND 	object_type='{$_REQUEST['object_type']}'
		ORDER BY pos");

	for ($i = 0; $i < db_num_rows($query); $i++)
	{
		$row = db_fetch_array($query);

		if ($_REQUEST['direction'] == "up")
		{
			if (($_REQUEST['id'] - 1) == $i)
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE view SET pos = {$next_row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND id = {$row['id']}");
				db_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND id = {$next_row['id']}");
				break;
			}
		}
		else
		{
			if ($_REQUEST['id'] == $i)
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE view SET pos = {$next_row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND id = {$row['id']}");
				db_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = {$_REQUEST['object_id']} AND object_type = '{$_REQUEST['object_type']}' AND id = {$next_row['id']}");
				break;
			}
		}
	}

	header("Location: {$_SERVER['PHP_SELF']}?object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&action=list");
	exit(0);
}

function ss_random_all()
{
	$myq = db_query("SELECT object_type, object_id FROM view LEFT JOIN devices ON view.object_id=devices.id
						WHERE object_type='device' AND devices.disabled=0 GROUP BY object_type, object_id
						ORDER BY RAND() ");
	while ($myr = db_fetch_array($myq))
	{
		array_push($_SESSION["netmrgsess"]["slideshow"]["views"], array( "object_type" => $myr['object_type'] , "object_id" => $myr['object_id'] ));
	}
}

function ss_descendants($group)
{
	$q = db_query("SELECT id FROM groups WHERE parent_id = $group ORDER BY name");
	while ($r = db_fetch_array($q))
	{
		ss_descendants($r['id']);
	}

	$q = db_query("	SELECT dev.id AS id, count(view.id) AS view_items
					FROM dev_parents dp
					LEFT JOIN devices dev ON dp.dev_id=dev.id
					LEFT JOIN view ON dev.id=view.object_id
					WHERE grp_id = $group
					AND object_type = 'device'
					AND dev.disabled = 0
					GROUP BY dev.id
					ORDER BY dev.name");
	while ($r = db_fetch_array($q))
	{
		if ($r['view_items'] > 0)
			array_push($_SESSION["netmrgsess"]["slideshow"]["views"], array( "object_type" => "device" , "object_id" => $r['id']));
	}
}

function do_slideshow()
{
	if (isset($_REQUEST["type"]))
	{
		// we're just starting a slideshow, not in the middle of one.

		$_SESSION["netmrgsess"]["slideshow"]["views"] = array();
		$_SESSION["netmrgsess"]["slideshow"]["current"] = 0;
		$_SESSION["netmrgsess"]["slideshow"]["type"] = $_REQUEST['type'];

		switch ($_REQUEST['type'])
		{
			case 0:		ss_random_all();						break;
			case 1:		ss_descendants($_REQUEST['group_id']);	break;
		}

		header("Location: {$_SERVER['PHP_SELF']}?action=slideshow");
		exit(0);
	}

	if (count($_SESSION["netmrgsess"]["slideshow"]["views"]) == 0)
	{
		begin_page("view.php", "Slide Show");
		echo("This slide show is empty.");
		end_page();
		exit(0);
	}

	if (isset($_REQUEST['jump']))
	{
		$_SESSION["netmrgsess"]["slideshow"]["current"] = $_REQUEST['jump'];
	}

	$GLOBALS["slideshow"] = true;
	$_REQUEST['action'] = "view";
	$view = $_SESSION["netmrgsess"]["slideshow"]["views"][$_SESSION["netmrgsess"]["slideshow"]["current"]];
	$_REQUEST['object_type'] = $view['object_type'];
	$_REQUEST['object_id'] = $view['object_id'];
	$GLOBALS["slide_show_formatted_link"] = cond_formatted_link($_SESSION["netmrgsess"]["slideshow"]["current"] > 0, "Previous Slide", "{$_SERVER['PHP_SELF']}?action=slideshow&jump=" . ($_SESSION["netmrgsess"]["slideshow"]["current"] - 1));
	$_SESSION["netmrgsess"]["slideshow"]["current"]++;

	if ( count($_SESSION["netmrgsess"]["slideshow"]["views"]) == $_SESSION["netmrgsess"]["slideshow"]["current"])
	{
		$_SESSION["netmrgsess"]["slideshow"]["current"] = 0;
		$GLOBALS["slide_show_formatted_link"] .= formatted_link("Restart Slideshow", "{$_SERVER['PHP_SELF']}?action=slideshow");
	}
	else
	{
		$GLOBALS["slide_show_formatted_link"] .= formatted_link("Next Slide", "{$_SERVER['PHP_SELF']}?action=slideshow");
	}

	?>
	<script language="javascript">
	<!--
	function myHeight()
	{
		return document.body.offsetHeight;
	}

	function nextPage()
	{
		window.location = "<?php echo("{$_SERVER['PHP_SELF']}?action=slideshow"); ?>";
	}

	function myScroll()
	{
		if (running)
		{
			documentYposition += scrollAmount;
			window.scroll(0,documentYposition);
			if (documentYposition > documentLength)
				nextPage();
		}
		setTimeout('myScroll()',scrollInterval);
	}

	function start()
	{
		documentLength = myHeight();
		myScroll();
	}

	function toggle()
	{
		running = !running;
	}


	var documentLength;
	var scrollAmount = 100;
	var scrollInterval = 1000;
	var documentYposition = 0;
	var running = true;

	-->
	</script>
	<?php

	do_view();

}

function do_view()
{
	if ($GLOBALS["slideshow"]
		&& GetUserPref(GetUserID(), "SlideShow", "AutoScroll") !== ""
		&& GetUserPref(GetUserID(), "SlideShow", "AutoScroll"))
	{
		begin_page("view.php", "View", 1, "onLoad=start() onClick=toggle()");
	}
	else
	{
		begin_page("view.php", "View", 1);
	}

	$view_select =
		"SELECT		view.id, pos, graphs.name, graphs.title, graph_id, separator_text, subdev_id, pos, view.type AS type
		FROM		view
		LEFT JOIN 	graphs ON view.graph_id=graphs.id
		WHERE 		object_type='{$_REQUEST['object_type']}'
		AND 		object_id={$_REQUEST['object_id']}
		ORDER BY 	pos";

	$view_result = db_query($view_select);
	$num = db_num_rows($view_result);

	if ($_REQUEST["action"] == "view")
	{
		echo '<!-- graphs start -->'."\n";
		echo "<div align=\"center\">";

		while ($row = db_fetch_array($view_result))
		{
			switch ($row['type'])
			{
				case "graph":
					echo '<a href="enclose_graph.php?type=custom&id='.$row["graph_id"].'">'."\n";
					echo '	<img src="get_graph.php?type=custom&id='.$row["graph_id"].'" border="0">'."\n";
					echo "</a><br />\n";
					break;

				case "template":
					echo '<a href="enclose_graph.php?type=template&id='.$row["graph_id"].'&subdev_id='.$row["subdev_id"].'">'."\n";
					echo '	<img src="get_graph.php?type=template&id='.$row["graph_id"].'&subdev_id='.$row["subdev_id"].'" border="0">'."\n";
					echo "</a><br />\n";
					break;

				case "separator":
					echo '<table width="100%" ><tr><td class="viewseparator">' . $row["separator_text"] . '</td></tr></table>'."\n";
					break;
			} // end switch row type
		} // end while each row

		echo "</div>\n";
		echo '<!-- graphs end -->'."\n";

		if ($_SESSION["netmrgsess"]["permit"] > 1)
		{
			print(formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=list&object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}"));
		}

		if ($GLOBALS["slideshow"])
		{
			print($GLOBALS["slide_show_formatted_link"]);
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
				$move_up = image_link_disabled("arrow-up", "Move Up");
			}
			else
			{
				$move_up = image_link("arrow-up", "Move Up", "{$_SERVER['PHP_SELF']}?action=move&direction=up&object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&id=$i");
			}

			if ($i == ($num - 1))
			{
				$move_down = image_link_disabled("arrow-down", "Move Down");
			}
			else
			{
				$move_down = image_link("arrow-down", "Move Down", "{$_SERVER['PHP_SELF']}?action=move&direction=down&object_id={$_REQUEST['object_id']}&object_type={$_REQUEST['object_type']}&id=$i");
			}

			$row = db_fetch_array($view_result);

			switch ($row['type'])
			{
				case 'graph':
				$name = $row['title'];
				$extra_options = formatted_link("Edit Graph", "graph_items.php?graph_id={$row['graph_id']}");
				break;

				case 'template':
				$name = expand_parameters($row['title'], $row['subdev_id']);
				$extra_options = formatted_link("Edit Template", "graph_items.php?graph_id={$row['graph_id']}");
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
					formatted_link("Delete","javascript:del('".addslashes($name)."', '{$row['id']}')") . "&nbsp;" .
					$extra_options)
			); // end make_display_item();
		}

		print("</table>");
		print(formatted_link("Done Editing", "{$_SERVER['PHP_SELF']}?action=view&object_type={$_REQUEST['object_type']}&object_id={$_REQUEST['object_id']}"));

	}

	end_page();
}

?>
