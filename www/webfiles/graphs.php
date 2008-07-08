<?php
/********************************************
* NetMRG Integrator
*
* graphs.php
* Graphs Configuration Page
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

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

switch ($_REQUEST['action'])
{
	case 'doedit':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		doedit();
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

	case 'applytemplates':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		applytemplates();
		break;

	case 'doapplytemplates':
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		doapplytemplates();
		break;

	default:
		display();
}

end_page();

// end main, begin functions:

function doedit()
{
	if (empty($_REQUEST["graph_id"]))
	{
		$command = "INSERT INTO";
		$where = ", type='{$_REQUEST['type']}'";
	}
	else
	{
		$command = "UPDATE";
		$where = "WHERE id='{$_REQUEST['graph_id']}'";
	}
	
	$options = "";

	if (isset($_REQUEST["options_nolegend"]) && $_REQUEST["options_nolegend"] == true)
		$options .= "nolegend,";

	if (isset($_REQUEST["options_logarithmic"]) && $_REQUEST["options_logarithmic"] == true)
		$options .= "logarithmic,";
		
	$_REQUEST["min"] = ($_REQUEST["min"] == "U" ? "NULL" : "'" . $_REQUEST['min'] . "'");
	$_REQUEST["max"] = ($_REQUEST["max"] == "U" ? "NULL" : "'" . $_REQUEST['max'] . "'");

	$options = substr($options, 0, -1);
	
	db_update("$command graphs SET
			name='" . $_REQUEST['graph_name'] . "',
			title='" . $_REQUEST['graph_title'] . "',
			comment='" . $_REQUEST['graph_comment'] . "',
			width='{$_REQUEST['width']}',
			height='{$_REQUEST['height']}',
			vert_label='" . $_REQUEST['vert_label'] . "',
			base='{$_REQUEST['base']}', 
			options='$options',
			max={$_REQUEST['max']},
			min={$_REQUEST['min']}
			$where");

	header("Location: {$_SERVER['PHP_SELF']}?type={$_REQUEST['type']}");
	exit(0);

} // end doedit()

function dodelete()
{
	if (isset($_REQUEST["graph"]))
	{
		while (list($key,$value) = each($_REQUEST["graph"]))
		{
			delete_graph($key);
		}
	}
	elseif (isset($_REQUEST["graph_id"]))
	{
		delete_graph($_REQUEST["graph_id"]);
	}

	header("Location: {$_SERVER['PHP_SELF']}?type={$_REQUEST['type']}");
	exit(0);
} // end dodelete()

function duplicate()
{
	if (isset($_REQUEST["graph"]))
	{
		while (list($key,$value) = each($_REQUEST["graph"]))
		{
			duplicate_graph($key);
		}
	}
	elseif (isset($_REQUEST["id"]))
	{
		duplicate_graph($_REQUEST["id"]);
	}

	header("Location: {$_SERVER['PHP_SELF']}?type={$_REQUEST['type']}");
	exit(0);
} // end duplicate()

function applytemplates()
{
	begin_page("graphs.php", "Apply Templates");
	js_checkbox_utils("edit");
	make_edit_table("Apply Templates");

	echo "<tr><td>";
	make_plain_display_table("",
		checkbox_toolbar("edit"), ""
	); // end make_display_table();

	$query = "SELECT * FROM graphs WHERE type='template' ORDER BY name";

	$graph_results = db_query($query);

	while ( $graph_row = db_fetch_array($graph_results) )
	{
		make_edit_checkbox($graph_row["name"],
			"graph[{$graph_row["id"]}]",
			$_REQUEST["graph"][$graph_row["id"]]);
	} // end graphs

	echo "</table>";
	echo "</td></tr>";

	$sub_dev = (!empty($_REQUEST["sub_dev_id"])) ? $_REQUEST["sub_dev_id"] : -1;
	if (($sub_dev == -1) || ($_REQUEST['edit_subdev'] == 1))
	{
		make_edit_select_subdevice($sub_dev, array(), 'multiple size="10"');
		if (strstr($_SERVER['HTTP_REFERER'], "graphs.php") && !($_REQUEST['edit_subdev'] == 1))
		{
			$return = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			foreach ($_SESSION["netmrgsess"]["grpnav"][$_REQUEST['tripid']] as $breadcrumb)
			{
				if ($breadcrumb['type'] == "device")
				{
					$return = "sub_devices.php?dev_id={$breadcrumb['id']}&tripid={$_REQUEST['tripid']}";
					break;
				}
				else
					$return = "groups.php";
			}
		}
		make_edit_hidden("return", $return);
	}
	else
	{
		$label = "<big><b>Sub Device:</b><br>  ";
		$label .= get_dev_sub_device_name($sub_dev);
		$label .= "  [<a href='{$_SERVER['PHP_SELF']}?action={$_REQUEST['action']}&sub_dev_id={$_REQUEST['sub_dev_id']}&edit_subdev=1&tripid={$_REQUEST['tripid']}'>change</a>]</big>";
		make_edit_label($label);
		make_edit_hidden("subdev_id[1]", $sub_dev);
		make_edit_hidden("return", $_SERVER["HTTP_REFERER"]);
	}

	make_edit_hidden("action", "doapplytemplates");
	make_edit_submit_button();
	make_edit_end();
} // end applytemplates()

function doapplytemplates()
{
	while (list($skey,$svalue) = each($_REQUEST["subdev_id"]))
	{
		while (list($gkey,$gvalue) = each($_REQUEST["graph"]))
		{
			apply_template($svalue, $gkey);
		}
		reset($_REQUEST["graph"]);
	}

	header("Location: {$_REQUEST['return']}");
	exit(0);
} // end doapplytemplate()

function display()
{
	if (empty($_REQUEST['type']))
	{
		$_REQUEST['type'] = "custom";
	}
	begin_page("graphs.php", ucfirst($_REQUEST['type']) . " Graphs");
	js_checkbox_utils();
	js_confirm_dialog("del", "Are you sure you want to delete graph ", "?", "{$_SERVER['PHP_SELF']}?action=dodelete&type={$_REQUEST['type']}&graph_id=");
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="type" value="<?php echo $_REQUEST['type']; ?>">
	<?php
	make_display_table(ucfirst($_REQUEST['type']) . " Graphs", "graphs.php?action=add&type={$_REQUEST['type']}", 
		array("text" => checkbox_toolbar()),
		array("text" => "Name"),
		array()
	); // end make_display_table();

	$query = "SELECT * FROM graphs WHERE type='{$_REQUEST['type']}'";

	if (isset($_REQUEST["order_by"]))
	{
		$query .= " ORDER BY {$_REQUEST['order_by']}";
	}
	else
	{
		$query .= " ORDER BY name";
	} // end if order_by

	$graph_results = db_query($query);
	$graph_total = db_num_rows($graph_results);

	for ($graph_count = 1; $graph_count <= $graph_total; ++$graph_count)
	{
		$graph_row = db_fetch_array($graph_results);
		$graph_id  = $graph_row["id"];
		if ($graph_row['type'] == "template")
		{
			$apply_template_link = "&nbsp;" . 
				formatted_link("Apply Template To...", "{$_SERVER['PHP_SELF']}?action=applytemplates&graph[$graph_id]=on", "", "applytemplate");
		}
		else
		{
			$apply_template_link = "";
		}

		make_display_item("editfield".(($graph_count-1)%2),
			array("checkboxname" => "graph", "checkboxid" => $graph_id),
			array("text" => $graph_row["name"], "href" => "graph_items.php?graph_id=$graph_id"),
			array("text" => formatted_link("View", "enclose_graph.php?type=custom&id=" . $graph_row["id"], "", "view") . "&nbsp;" .
				formatted_link("Duplicate", "{$_SERVER["PHP_SELF"]}?action=duplicate&type=" . $graph_row['type'] . "&id=" . $graph_row["id"], "", "duplicate") . $apply_template_link),
			array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&graph_id=$graph_id", "", "edit") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($graph_row['name']) . "', '$graph_id')", "", "delete"))
		); // end make_display_item();
	} // end graphs

	// FIXME: There should be a better way to do this
	$duplicate_array =
		array("text" => "Duplicate", "action" => "multiduplicate");
	$apply_template_array =
		array("text" => "Apply Templates", "action" => "applytemplates");
	$delete_array =
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked graphs?");

	if ($graph_row['type'] == "template")
	{
		make_checkbox_command("", 4,
			$duplicate_array,
			$apply_template_array,
			$delete_array
		); // end make_checkbox_command
	}
	else
	{
		make_checkbox_command("", 4,
			$duplicate_array,
			$delete_array
		); // end make_checkbox_command
	}

	make_status_line("{$_REQUEST["type"]} graph", $graph_total);
?>
</table>
</form>
<?php

} // end display()

function edit()
{
	// Display editing screen
	begin_page("graphs.php", "Graphs");

	if ($_REQUEST["action"] == "edit")
	{
		$graph_results = db_query("SELECT * FROM graphs WHERE id='{$_REQUEST["graph_id"]}'");
		$graph_row = db_fetch_array($graph_results);
		if (!isset($graph_row["min"])) { $graph_row["min"] = "U"; }
		if (!isset($graph_row["max"])) { $graph_row["max"] = "U"; }
	}
	else
	{
		$graph_row["name"] = "";
		$graph_row["title"] = "";
		$graph_row["comment"] = "";
		$graph_row["width"] = 575;
		$graph_row["height"] = 100;
		$graph_row["vert_label"] = "";
		$graph_row["base"] = 1000;
		$graph_row["min"] = "U";
		$graph_row["max"] = "U";
		$graph_row["options"] = "";
	}

	make_edit_table("Edit Graph");
	make_edit_text("Name:", "graph_name", "25", "100", $graph_row["name"]);
	make_edit_text("Title:", "graph_title", "25", "100", $graph_row["title"]);
	make_edit_text("Comment:", "graph_comment", "50", "200", $graph_row["comment"]);
	make_edit_text("Vertical Label:", "vert_label", "50", "100", $graph_row["vert_label"]);
	make_edit_text("Width:", "width", "4", "4", $graph_row["width"]);
	make_edit_text("Height:", "height", "4", "4", $graph_row["height"]);
	if (!empty($_REQUEST["showadvanced"]))
	{
		make_edit_group("Advanced");
		make_edit_text("Base Value:", "base", "4", "10", $graph_row["base"]);
		make_edit_text("Maximum Value:", "max", "6", "10", $graph_row["max"]);
		make_edit_text("Minimum Value:", "min", "6", "10", $graph_row["min"]);
		make_edit_checkbox("Hide Legend", "options_nolegend", isin($graph_row["options"], "nolegend"));
		make_edit_checkbox("Use Logarithmic Scaling", "options_logarithmic", isin($graph_row["options"], "logarithmic"));
	} // end if show advanced
	else
	{
		$graphlink = 'graphs.php?showadvanced=true';
		if (!empty($_SERVER["QUERY_STRING"]))
		{
			$graphlink .= '&'.$_SERVER["QUERY_STRING"];
		} // end if query string not empty
		make_edit_group('<a class="editheaderlink" href="'.$graphlink.'">[Show Advanced]</a>');
		make_edit_hidden("base", $graph_row["base"]);
		make_edit_hidden("max", $graph_row["max"]);
		make_edit_hidden("min", $graph_row["min"]);
		make_edit_hidden("options_nolegend", isin($graph_row["options"], "nolegend"));
		make_edit_hidden("options_logarithmic", isin($graph_row["options"], "logarithmic"));
	} // end else hide advanced

	if ($_REQUEST["action"] == "edit")
	{
		make_edit_hidden("graph_id", $_REQUEST["graph_id"]);
		make_edit_hidden("type", $graph_row['type']);
	}
	else
	{
		make_edit_hidden("type", $_REQUEST['type']);
	}
	
	make_edit_hidden("action","doedit");

	if (!empty($_REQUEST["return_type"]))
	{
		make_edit_hidden("return_type",$_REQUEST["return_type"]);
		make_edit_hidden("return_id",$_REQUEST["return_id"]);
	} // end if return_type

	make_edit_submit_button();
	make_edit_end();

} // end edit()

?>
