<?php
/********************************************
* NetMRG Integrator
*
* graph_items.php
* Custom Graphs Data Sources Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);


if (empty($_REQUEST["action"]))
	$_REQUEST["action"] = "";

if ($_REQUEST["action"] == "doedit")
{
	check_auth(2);
        
	$stats = "";

	if (isset($_REQUEST["show_current"]))
		$stats .= "CURRENT,";

	if (isset($_REQUEST["show_average"]))
		$stats .= "AVERAGE,";

	if (isset($_REQUEST["show_maximum"]))
		$stats .= "MAXIMUM,";

	if (isset($_REQUEST["show_integer"]))
		$stats .= "INTEGER,";

	if (isset($_REQUEST["show_sums"]))
		$stats .= "SUMS,";
		
	$stats = substr($stats, 0, -1);

	if ($_REQUEST["id"] == 0)
	{
		$pre  = "INSERT INTO";
		$post = "";
	}
	else
	{
		$pre  = "UPDATE";
		$post = "WHERE id = {$_REQUEST['id']}";
	}

	$_REQUEST['color'] = db_escape_string($_REQUEST['color']);
	$_REQUEST['label'] = db_escape_string($_REQUEST['label']);
	$_REQUEST['multiplier'] = db_escape_string($_REQUEST['multiplier']);
	$_REQUEST['start_time'] = db_escape_string($_REQUEST['start_time']);
	$_REQUEST['end_time'] = db_escape_string($_REQUEST['end_time']);
	
	$graph_ds_query = "$pre graph_ds
		SET mon_id='{$_REQUEST['mon_id']}', color='{$_REQUEST['color']}', 
		 type='{$_REQUEST['type']}', graph_id='{$_REQUEST['graph_id']}', 
		 label='{$_REQUEST['label']}', alignment='{$_REQUEST['alignment']}', 
		 stats='$stats', position='{$_REQUEST['position']}', multiplier='{$_REQUEST['multiplier']}', 
		 start_time='{$_REQUEST['start_time']}', end_time='{$_REQUEST['end_time']}' 
		 $post";
	db_update($graph_ds_query);

	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // done adding/editing

if ($_REQUEST["action"] == "move")
{
	check_auth(2);

	// do moving
	$query = db_query("
		SELECT id, position
		FROM graph_ds
		WHERE graph_id={$_REQUEST['graph_id']}
		ORDER BY position
		");
	for ($ds_count = 0; $ds_count < db_num_rows($query); $ds_count++)
	{
		$row = db_fetch_array($query);

		if ($_REQUEST["direction"] == "up")
		{
			if (($_REQUEST["id"] - 1) == $ds_count)
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE graph_ds SET position = {$next_row['position']} WHERE id = {$row['id']}");
				db_update("UPDATE graph_ds SET position = {$row['position']} WHERE id = {$next_row['id']}");
				break;
			}
		}
		else
		{
                	if ($_REQUEST["id"] == $ds_count)
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE graph_ds SET position = {$next_row['position']} WHERE id = {$row['id']}");
				db_update("UPDATE graph_ds SET position = {$row['position']} WHERE id = {$next_row['id']}");
				break;
			}
		}
	}

	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // end do move

if ($_REQUEST["action"] == "dodelete")
{
	check_auth(2);
	delete_ds($_REQUEST['id']);
	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // done deleting

if (empty($_REQUEST["action"]))
{
	GLOBAL $RRDTOOL_ITEM_TYPES;

	// Change databases if necessary and then display list
	begin_page("graph_items.php", "Graph Items");

	js_confirm_dialog("del", "Are you sure you want to delete graph item ", "?", "{$_SERVER['PHP_SELF']}?action=dodelete&graph_id={$_REQUEST['graph_id']}&id=");

	$ds_results = db_query("
		SELECT
		graph_ds.label		AS label,
		graph_ds.id			AS id,
		graph_ds.position	AS pos,
		graph_ds.type		AS type,
		graph_ds.color		AS color
		FROM graph_ds
		WHERE graph_ds.graph_id={$_REQUEST['graph_id']}
		ORDER BY position, id");
	
	$ds_total = db_num_rows($ds_results);
	
?>
	<img align="center" src="get_graph.php?type=custom&id=<?php echo $_REQUEST["graph_id"]; ?>"><br><br>
<?php
	make_display_table("Graph Items", "{$_SERVER['PHP_SELF']}?action=add&graph_id={$_REQUEST['graph_id']}&edit_monitor=1&position=" . ($ds_total + 1),
		array("text" => "Label"),
		array("text" => "Type"),
		array()
	); // end make_display_table();

	for ($ds_count = 0; $ds_count < $ds_total; $ds_count++)
	{
		// For each graph item

		$ds_row = db_fetch_array($ds_results);
		$id  = $ds_row["id"];

		if ($ds_count == 0)
		{
			$move_up = image_link_disabled("arrow-up", "Move Up");
		}
		else
		{
			$move_up = image_link("arrow-up", "Move Up", "{$_SERVER['PHP_SELF']}?action=move&direction=up&graph_id={$_REQUEST['graph_id']}&id=$ds_count");
		}

		if ($ds_count == ($ds_total - 1))
		{
			$move_down = image_link_disabled("arrow-down", "Move Down");
		}
		else
		{
			$move_down = image_link("arrow-down", "Move Down", "{$_SERVER['PHP_SELF']}?action=move&direction=down&graph_id={$_REQUEST['graph_id']}&id=$ds_count");
		}

		make_display_item("editfield".($ds_count%2),
			array("text" => $ds_row["label"]),
			array("text" => color_block($ds_row["color"]) . "&nbsp;&nbsp;" . $RRDTOOL_ITEM_TYPES[$ds_row["type"]]),
			array("text" => formatted_link("View", "enclose_graph.php?type=custom_ds&id=" . $ds_row["id"]) . "&nbsp;" .
				$move_up . "&nbsp;" . $move_down),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id=$id&graph_id={$_REQUEST['graph_id']}") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($ds_row["label"]) . "', '" . $ds_row["id"] . "')"))
		); // end make_display_item();


	} // end for

?></table><?php

} // end no action

if (($_REQUEST["action"] == "edit") || ($_REQUEST["action"] == "add"))
{

	check_auth(2);
	begin_page("graph_items.php", "Add/Edit Graph Item");

	if ($_REQUEST["action"] == "add")
	{
		$_REQUEST["id"] = 0;
		$ds_row["type"] = 0;
		$ds_row["color"] = "#0000AA";
		$ds_row["alignment"] = 0;
		$ds_row["multiplier"] = 1;
		$ds_row["label"] = "";
		$ds_row["mon_id"] = -1;
		$ds_row["id"] = 0;
		$ds_row["position"] = $_REQUEST["position"];
		$ds_row["stats"] = "CURRENT,AVERAGE,MAXIMUM";
		$ds_row["start_time"] = "";
		$ds_row["end_time"] = "";
        }
	else
	{
		$ds_results = db_query("SELECT * FROM graph_ds WHERE id={$_REQUEST['id']}");
		$ds_row = db_fetch_array($ds_results);
	}

	$ds_row["graph_id"] = $_REQUEST["graph_id"];

	if (empty($_REQUEST["edit_monitor"]))
	{
		$_REQUEST["edit_monitor"] = 0;
	}

	js_color_dialog();
	make_edit_table("Edit Graph Item");
	make_edit_text("Item Label:","label","50","100",$ds_row["label"]);
	make_edit_select_from_array("Item Type:", "type", $GLOBALS['RRDTOOL_ITEM_TYPES'], $ds_row["type"]);
	make_edit_color("Item Color:", "color", $ds_row["color"]);

	make_edit_group("Data");
	if ($_REQUEST["edit_monitor"] == 1)
	{
		make_edit_select_monitor($ds_row["mon_id"], $GLOBALS['SPECIAL_MONITORS']);
	}
	else
	{
		$label = "<big><b>Monitor:</b><br>  ";
		if ($ds_row["mon_id"] > 0)
		{
			$label .= get_monitor_name($ds_row["mon_id"]);
		}
		else
		{
			$label .= $GLOBALS['SPECIAL_MONITORS'][intval($ds_row["mon_id"])];
		}
		$label .= "  [<a href='{$_SERVER['PHP_SELF']}?id={$_REQUEST['id']}&action={$_REQUEST['action']}&graph_id={$_REQUEST['graph_id']}&edit_monitor=1'>change</a>]</big>";
		make_edit_label($label);
		make_edit_hidden("mon_id", $ds_row["mon_id"]);
	}

	make_edit_text("Fixed Value or Value Multiplier:", "multiplier", "20", "20", $ds_row["multiplier"]);
	make_edit_group("Legend");
	make_edit_select_from_array("Alignment:", "alignment", $GLOBALS['ALIGN_ARRAY'], $ds_row["alignment"]);
	make_edit_checkbox("Show Current Value", "show_current", isin($ds_row["stats"], "CURRENT"));
	make_edit_checkbox("Show Average Value", "show_average", isin($ds_row["stats"], "AVERAGE"));
	make_edit_checkbox("Show Maximum Value", "show_maximum", isin($ds_row["stats"], "MAXIMUM"));
	make_edit_checkbox("Show Only Integers", "show_integer", isin($ds_row["stats"], "INTEGER"));
	make_edit_checkbox("Show Sums",		 "show_sums",    isin($ds_row["stats"], "SUMS"));
	if (!empty($_REQUEST["showadvanced"]))
	{
		make_edit_group("Advanced");
		make_edit_text("Start Time", "start_time", "20", "20", $ds_row["start_time"]);
		make_edit_text("End Time", "end_time", "20", "20", $ds_row["end_time"]);
	} // end if we want advanced options shown
	else
	{
		$graphlink = 'graph_items.php?showadvanced=true';
		if (!empty($_SERVER["QUERY_STRING"]))
		{
			$graphlink .= '&'.$_SERVER["QUERY_STRING"];
		} // end if query string not empty
		make_edit_group('<a class="editheaderlink" href="'.$graphlink.'">[Show Advanced]</a>');
		make_edit_hidden("start_time", $ds_row["start_time"]);
		make_edit_hidden("end_time", $ds_row["end_time"]);
	} // end if no advanced options

	make_edit_hidden("action", "doedit");
	make_edit_hidden("graph_id", $ds_row["graph_id"]);
	make_edit_hidden("id", $ds_row["id"]);
	make_edit_hidden("position", $ds_row["position"]);
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

end_page();

?>
