<?php
/********************************************
* NetMRG Integrator
*
* graphs.php
* Graphs Configuration Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

switch ($_REQUEST['action'])
{
	case 'doedit':			doedit();			break;
	case 'dodelete':		dodelete();			break;
	case 'duplicate':		duplicate();		break;
	case 'edit':			edit();				break;
	case 'add':				edit();				break;
	case 'applytemplate':	applytemplate();	break;
	case 'doapplytemplate': doapplytemplate();	break;
	default:				display();
}

end_page();

// end main, begin functions:

function doedit()
{
	check_auth(2);
	if (empty($_REQUEST["graph_id"]))
	{
		$command = "INSERT INTO";
		$where = ", type='{$_REQUEST['type']}'";
	}
	else
	{
		$command = "UPDATE";
		$where = "WHERE id={$_REQUEST['graph_id']}";
	}
	
	$options = "";

	if (isset($_REQUEST["options_nolegend"]))
		$options .= "nolegend,";

	if (isset($_REQUEST["options_logarithmic"]))
		$options .= "logarithmic,";

	$options = substr($options, 0, -1);
	
	db_update("$command graphs SET
			name='" . db_escape_string($_REQUEST['graph_name']) . "',
			title='" . db_escape_string($_REQUEST['graph_title']) . "',
			comment='" . db_escape_string($_REQUEST['graph_comment']) . "',
			width='{$_REQUEST['width']}\", height=\"{$_REQUEST['height']}',
			vert_label='" . db_escape_string($_REQUEST['vert_label']) . "',
			base='{$_REQUEST['base']}', 
			options='$options' 
			$where");

	header("Location: {$_SERVER['PHP_SELF']}?type={$_REQUEST['type']}");
	exit(0);

} // end doedit()

function dodelete()
{
	check_auth(2);
	delete_graph($_REQUEST["graph_id"]);

	header("Location: {$_SERVER['PHP_SELF']}?type={$_REQUEST['type']}");
	exit(0);
} // end dodelete()

function duplicate()
{
	check_auth(2);
	$graph_handle = db_query("SELECT * FROM graphs WHERE id={$_REQUEST["id"]}");
	$graph_row    = db_fetch_array($graph_handle);
	db_update("INSERT INTO graphs SET name='" . db_escape_string($graph_row['name']) . " (duplicate)', " .
	"title='" . db_escape_string($graph_row['title']). "', " .  
	"comment='" . db_escape_string($graph_row['comment']) . "', " .
	"width={$graph_row['width']}, height={$graph_row['height']}, " .
	"vert_label='" . db_escape_string($graph_row['vert_label']) . "', " .
	"base={$graph_row['base']}, options=\"{$graph_row['options']}\", " .
	"type='{$graph_row['type']}'");

	$new_id = db_insert_id();

	$ds_handle = db_query("SELECT * FROM graph_ds WHERE graph_id='{$_REQUEST['id']}'");
	for ($i = 0; $i < db_num_rows($ds_handle); $i++)
	{
		$ds_row = db_fetch_array($ds_handle);
		db_update("INSERT INTO graph_ds SET graph_id=$new_id, " .
		"mon_id={$ds_row['mon_id']}, " . 
		"color='" . db_escape_string($ds_row['color']) . "', " .
		"type={$ds_row['type']}, " .
		"label='" . db_escape_string($ds_row['label']) . "', " .
		"alignment={$ds_row['alignment']}, " .
		"stats='{$ds_row['stats']}', " .
		"position={$ds_row['position']}, " .
		"multiplier='" . db_escape_string($ds_row['multiplier']) . "', " . 
		"start_time='" . db_escape_string($ds_row['start_time']) . "', " . 
		"end_time='" . db_escape_string($ds_row['end_time']) . "'");
	} // end for each ds

	// need to duplicate highlight periods

	header("Location: {$_SERVER['PHP_SELF']}?type={$graph_row['type']}");
	exit(0);
} // end duplicate()

function applytemplate()
{
	check_auth(2);
	begin_page("graphs.php", "Apply Template");
	make_edit_table("Apply Template");
	make_edit_select_subdevice(-1);
	make_edit_hidden("action", "doapplytemplate");
	make_edit_hidden("id", $_REQUEST['id']);
	make_edit_submit_button();
	make_edit_end();
} // end applytemplate()

function doapplytemplate()
{
	check_auth(2);
	apply_template($_REQUEST['subdev_id'], $_REQUEST['id']);
	header("Location: {$_SERVER['PHP_SELF']}?type=template");
	exit(0);
} // end doapplytemplate()

function display()
{
	if (empty($_REQUEST['type']))
	{
		$_REQUEST['type'] = "custom";
	}
	begin_page("graphs.php", ucfirst($_REQUEST['type']) . " Graphs");
	js_confirm_dialog("del", "Are you sure you want to delete graph ", "?", "{$_SERVER['PHP_SELF']}?action=dodelete&type={$_REQUEST['type']}&graph_id=");
	make_display_table(ucfirst($_REQUEST['type']) . " Graphs", "graphs.php?action=add&type={$_REQUEST['type']}", 
		array("text" => "Name", "href" => "{$_SERVER['PHP_SELF']}?order_by=name"),
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
				formatted_link("Apply Template To...", "{$_SERVER['PHP_SELF']}?action=applytemplate&id=$graph_id");
		}
		else
		{
			$apply_template_link = "";
		}

		make_display_item("editfield".(($graph_count-1)%2),
			array("text" => $graph_row["name"], "href" => "graph_items.php?graph_id=$graph_id"),
			array("text" => formatted_link("View", "enclose_graph.php?type=custom&id=" . $graph_row["id"]) . "&nbsp;" .
				formatted_link("Duplicate", "{$_SERVER["PHP_SELF"]}?action=duplicate&id=" . $graph_row["id"]) . $apply_template_link),
			array("text" => formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&graph_id=$graph_id") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('" . addslashes($graph_row['name']) . "', '$graph_id')"))
		); // end make_display_item();
	} // end graphs

?></table><?php

} // end display()

function edit()
{
	// Display editing screen
	check_auth(2);
	begin_page("graphs.php", "Graphs");

	if ($_REQUEST["action"] == "edit")
	{
		$graph_results = db_query("SELECT * FROM graphs WHERE id={$_REQUEST["graph_id"]}");
		$graph_row = db_fetch_array($graph_results);
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
		$graph_row["options"] = "";
	}

	make_edit_table("Edit Graph");
	make_edit_text("Name:", "graph_name", "25", "100", $graph_row["name"]);
	make_edit_text("Title:", "graph_title", "25", "100", $graph_row["title"]);
	make_edit_text("Comment:", "graph_comment", "50", "200", $graph_row["comment"]);
	make_edit_text("Vertical Label:", "vert_label", "50", "100", $graph_row["vert_label"]);
	make_edit_text("Width:", "width", "4", "4", $graph_row["width"]);
	make_edit_text("Height:", "height", "4", "4", $graph_row["height"]);
	make_edit_group("Advanced");
	make_edit_text("Base Value:", "base", "4", "6", $graph_row["base"]);
	make_edit_checkbox("Hide Legend", "options_nolegend", isin($graph_row["options"], "nolegend"));
	make_edit_checkbox("Use Logarithmic Scaling", "options_logarithmic", isin($graph_row["options"], "logarithmic"));

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
