<?php
/********************************************
* NetMRG Integrator
*
* graph_items.php
* Custom Graphs Data Sources Page
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["ReadAll"]);

switch ($_REQUEST['action'])
{
	case 'doedit':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		doedit();
		break;

	case 'move_up':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		move("up");
		break;

	case 'move_down':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		move("down");
		break;

	case 'dodelete':
	case 'multidodelete':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		dodelete();
		break;

	case 'duplicate':
	case 'multiduplicate':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		duplicate();
		break;

	case 'add':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	case 'edit':
		edit();
		break;

	case 'gradient':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		gradient();
		break;

	default:
		display();
}

end_page();

function doedit()
{
	$stats = "";

	if (isset($_REQUEST["show_current"]))
		$stats .= "CURRENT,";

	if (isset($_REQUEST["show_average"]))
		$stats .= "AVERAGE,";

	if (isset($_REQUEST["show_maximum"]))
		$stats .= "MAXIMUM,";

	if (isset($_REQUEST["show_minimum"]))
		$stats .= "MINIMUM,";

	if (isset($_REQUEST["show_integer"]))
		$stats .= "INTEGER,";

	if (isset($_REQUEST["show_sums"]))
		$stats .= "SUMS,";
		
	if (isset($_REQUEST["multiply_sum"]))
		$stats .= "MULTSUM,";
		
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

	$graph_ds_query = "$pre graph_ds
		SET mon_id='{$_REQUEST['mon_id']}', color='{$_REQUEST['color']}', 
		 type='{$_REQUEST['type']}', graph_id='{$_REQUEST['graph_id']}', 
		 label='{$_REQUEST['label']}', alignment='{$_REQUEST['alignment']}', 
		 stats='$stats', position='{$_REQUEST['position']}', multiplier='{$_REQUEST['multiplier']}', 
		 start_time='{$_REQUEST['start_time']}', end_time='{$_REQUEST['end_time']}', cf='{$_REQUEST['cf']}'
		 $post";
	db_update($graph_ds_query);

	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // done adding/editing

function move($direction)
{
	if (isset($_REQUEST["graph_items"]))
	{
		if ($direction == "down")
			$_REQUEST['graph_items'] = array_reverse($_REQUEST['graph_items'], true);
		while (list($key,$value) = each($_REQUEST["graph_items"]))
		{
			move_graph_item($_REQUEST['graph_id'], $key, $direction);
		}
	}
	elseif (isset($_REQUEST["id"]))
	{
		move_graph_item($_REQUEST['graph_id'], $_REQUEST['id'], $direction);
	}
	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // end do move

function dodelete()
{
	if (isset($_REQUEST["graph_items"]))
	{
		while (list($key,$value) = each($_REQUEST["graph_items"]))
		{
			delete_ds($key);
		}
	}
	elseif (isset($_REQUEST["id"]))
	{
		delete_ds($_REQUEST['id']);
	}
	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);

} // done deleting

function gradient()
{
	if (isset($_REQUEST["graph_items"]))
	{
		// get bottom and top colors
		$count = 0;
		while (list($key,$value) = each($_REQUEST["graph_items"]))
		{
			$q = db_query("SELECT color FROM graph_ds WHERE id = $key");
			$r = db_fetch_array($q);
			if ($count == 0)
				$top = htmlcolor_to_rgb($r['color']);
			$bottom = htmlcolor_to_rgb($r['color']);
			$count++;
		}
		
		$rinc = intval(($top['r'] - $bottom['r']) / ($count - 1));
		$ginc = intval(($top['g'] - $bottom['g']) / ($count - 1));
		$binc = intval(($top['b'] - $bottom['b']) / ($count - 1));
			
		$rcur = $top['r'];
		$gcur = $top['g'];
		$bcur = $top['b'];
		
		// gradient the middle ones
		$i = 0;
		reset($_REQUEST['graph_items']);
		while (list($key,$value) = each($_REQUEST["graph_items"]))
		{
			if ( ($i != 0) && ($i != $count - 1) )
			{
				$rcur -= $rinc;
				$gcur -= $ginc;
				$bcur -= $binc;
				$newcolor = rgb_to_htmlcolor($rcur, $gcur, $bcur);
				db_query("UPDATE graph_ds SET color='$newcolor' WHERE id = $key");
			}
			$i++;
		}

	}
	
	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);
} // done deleting


function duplicate()
{
	if (isset($_REQUEST["graph_items"]))
	{
		while (list($key,$value) = each($_REQUEST["graph_items"]))
		{
			duplicate_graph_item($key);
		}
	}
	elseif (isset($_REQUEST["id"]))
	{
		duplicate_graph_item($_REQUEST['id']);
	}
	
	header("Location: {$_SERVER['PHP_SELF']}?graph_id={$_REQUEST['graph_id']}");
	exit(0);
	
} // done duplicating

function display()
{
	GLOBAL $RRDTOOL_ITEM_TYPES;

	// Change databases if necessary and then display list
	begin_page("graph_items.php", "Graph Items");
	js_checkbox_utils();

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
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
<?php
	make_edit_hidden("action", "");
	make_edit_hidden("type", $_REQUEST['type']);
	make_edit_hidden("graph_id", $_REQUEST['graph_id']);

	make_display_table("Graph Items", "{$_SERVER['PHP_SELF']}?action=add&graph_id={$_REQUEST['graph_id']}&edit_monitor=1&position=" . ($ds_total + 1),
		array("text" => checkbox_toolbar()),
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
			$move_up = image_link("arrow-up", "Move Up", "{$_SERVER['PHP_SELF']}?action=move_up&graph_id={$_REQUEST['graph_id']}&id=$id");
		}

		if ($ds_count == ($ds_total - 1))
		{
			$move_down = image_link_disabled("arrow-down", "Move Down");
		}
		else
		{
			$move_down = image_link("arrow-down", "Move Down", "{$_SERVER['PHP_SELF']}?action=move_down&graph_id={$_REQUEST['graph_id']}&id=$id");
		}
		
		if (($ds_row['type'] == 5) && ($ds_count == 0))
		{
			$item_type = "STACK (using as AREA)";
		}
		else
		{
			$item_type = $RRDTOOL_ITEM_TYPES[$ds_row["type"]];
		}

		make_display_item("editfield".($ds_count%2),
			array("checkboxname" => "graph_items", "checkboxid" => $id),
			array("text" => $ds_row["label"]),
			array("text" => color_block($ds_row["color"]) . "&nbsp;&nbsp;" . $item_type),
			array("text" => 
				formatted_link("View", "enclose_graph.php?type=custom_item&id=" . $ds_row["id"], "", "view") . 
				formatted_link("Duplicate", "{$_SERVER['PHP_SELF']}?action=duplicate&id=$id&graph_id={$_REQUEST['graph_id']}", "", "duplicate") . 
				"&nbsp;" . $move_up . "&nbsp;" . $move_down
				),
			array("text" => formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&id=$id&graph_id={$_REQUEST['graph_id']}", "", "edit") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($ds_row["label"]) . "', '" . $ds_row["id"] . "')", "", "delete"))
		); // end make_display_item();


	} // end for
	make_checkbox_command("", 5,
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked graphs?"),
		array("text" => "Duplicate", "action" => "multiduplicate"),
		array("text" => "Move Up", "action" => "move_up"),
		array("text" => "Move Down", "action" => "move_down"),
		array("text" => "Gradient", "action" => "gradient")
	); // end make_checkbox_command
	make_status_line("graph item", $ds_count);
?>
	</form>
	</table>
<?php

} // end display

function edit()
{

	begin_page("graph_items.php", "Add/Edit Graph Item");

	if ($_REQUEST["action"] == "add")
	{
		$_REQUEST["id"] = 0;
		$ds_row["type"] = 0;
		$ds_row["cf"] = 0;
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
	make_edit_select_from_array("Data Consolidation:", "cf", $GLOBALS['RRDTOOL_CFS'], $ds_row["cf"]);
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

	make_edit_text("Fixed Value or Value Multiplier:", "multiplier", "25", "100", $ds_row["multiplier"]);
	make_edit_group("Legend");
	make_edit_select_from_array("Alignment:", "alignment", $GLOBALS['ALIGN_ARRAY'], $ds_row["alignment"]);
	make_edit_checkbox("Show Current Value", "show_current", isin($ds_row["stats"], "CURRENT"));
	make_edit_checkbox("Show Average Value", "show_average", isin($ds_row["stats"], "AVERAGE"));
	make_edit_checkbox("Show Maximum Value", "show_maximum", isin($ds_row["stats"], "MAXIMUM"));
	make_edit_checkbox("Show Minimum Value", "show_minimum", isin($ds_row["stats"], "MINIMUM"));
	make_edit_checkbox("Show Only Integers", "show_integer", isin($ds_row["stats"], "INTEGER"));
	make_edit_checkbox("Show Sums", "show_sums", isin($ds_row["stats"], "SUMS"));
	make_edit_checkbox("Apply Multiplier to Sums", "multiply_sum", isin($ds_row['stats'], "MULTSUM"));
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

?>
