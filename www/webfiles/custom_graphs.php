<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Custom Graphs Configuration Page           #
#           custom_graphs.php                          #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);
if (empty($_REQUEST["action"])) { $_REQUEST["action"] = ""; }


if ($_REQUEST["action"] == "doadd")
{
	check_auth(2);
	if (empty($_REQUEST["show_legend"])) { $_REQUEST["show_legend"] = 0; }
	if (empty($_REQUEST["show_total_line"])) { $_REQUEST["show_total_line"] = 0; }
	if (empty($_REQUEST["show_total_stats"])) { $_REQUEST["show_total_stats"] = 0; }
	if (empty($_REQUEST["show_summed"])) { $_REQUEST["show_summed"] = 0; }
	if (empty($_REQUEST["disp_integer_only"])) { $_REQUEST["disp_integer_only"] = 0; }
	do_update("INSERT INTO graphs SET name=\"{$_REQUEST["graph_name"]}\", comment=\"{$_REQUEST["graph_comment"]}\", xsize={$_REQUEST["xsize"]}, ysize={$_REQUEST["ysize"]}, vert_label=\"{$_REQUEST["vert_label"]}\", show_legend={$_REQUEST["show_legend"]}, show_total_line={$_REQUEST["show_total_line"]}, show_total_stats={$_REQUEST["show_total_stats"]}, show_summed={$_REQUEST["show_summed"]}, max_custom=\"{$_REQUEST["max_custom"]}\", disp_integer_only={$_REQUEST["disp_integer_only"]}");

	header("Location: {$_SERVER["PHP_SELF"]}");
	exit(0);

} // done adding

if ($_REQUEST["action"] == "doedit")
{
	check_auth(2);
	if (empty($_REQUEST["show_legend"])) { $_REQUEST["show_legend"] = 0; }
	if (empty($_REQUEST["show_total_line"])) { $_REQUEST["show_total_line"] = 0; }
	if (empty($_REQUEST["show_total_stats"])) { $_REQUEST["show_total_stats"] = 0; }
	if (empty($_REQUEST["show_summed"])) { $_REQUEST["show_summed"] = 0; }
	if (empty($_REQUEST["disp_integer_only"])) { $_REQUEST["disp_integer_only"] = 0; }
	do_update("UPDATE graphs SET name=\"{$_REQUEST["graph_name"]}\", comment=\"{$_REQUEST["graph_comment"]}\", xsize={$_REQUEST["xsize"]}, ysize={$_REQUEST["ysize"]}, vert_label=\"{$_REQUEST["vert_label"]}\", show_legend={$_REQUEST["show_legend"]}, show_total_line={$_REQUEST["show_total_line"]}, show_total_stats={$_REQUEST["show_total_stats"]}, show_summed={$_REQUEST["show_summed"]}, max_custom=\"{$_REQUEST["max_custom"]}\", disp_integer_only={$_REQUEST["disp_integer_only"]} WHERE id='{$_REQUEST['graph_id']}'");

	if (isset($_REQUEST["return_type"])) {
		if ($_REQUEST["return_type"] == "traffic") {
			Header("Location: snmp_cache_view.php?dev_id={$_REQUEST['return_id']}");
		} // end if return type is traffic
	} else {
		header("Location: {$_SERVER['PHP_SELF']}");
		exit(0);
	} // end if return type


} // done editing

if ($_REQUEST["action"] == "dodelete")
{
	check_auth(2);
	delete_graph($_REQUEST["graph_id"]);
	
	header("Location: {$_SERVER['PHP_SELF']}");
} // done deleting

if ($_REQUEST["action"] == "duplicate")
{
	$graph_handle = do_query("SELECT * FROM graphs WHERE id={$_REQUEST["id"]}");
	$graph_row    = mysql_fetch_array($graph_handle);
	$new_handle   = do_update("INSERT INTO graphs SET name=\"" . $graph_row["name"] . ' (copy)",
		comment="' . $graph_row["comment"] . '",
		xsize=' . $graph_row["xsize"] . ', ysize=' . $graph_row["ysize"] .
		', vert_label="' . $graph_row["vert_label"] . '", show_legend=' .
	$graph_row["show_legend"] . ', show_total_line=' .
	$graph_row["show_total_line"] . ', show_total_stats=' .
	$graph_row["show_total_stats"] . ', show_summed=' .
	$graph_row["show_summed"] . ', disp_integer_only=' .
	$graph_row["disp_integer_only"]);
	$new_id = mysql_insert_id();

	$ds_handle = do_query("SELECT * FROM graph_ds WHERE graph_id='{$_REQUEST['id']}'");
	$ds_count = mysql_num_rows($ds_handle);
	for ($i = 0; $i < $ds_count; $i++)
	{
		$ds_row = mysql_fetch_array($ds_handle);
		do_update("INSERT INTO graph_ds SET " .
		'src_type=' . $ds_row["src_type"] . ',' .
		'src_id=' . $ds_row["src_id"] . ',' .
		'color="' . $ds_row["color"] . '",' .
		'type=' . $ds_row["type"] . ',' .
		'graph_id=' . $new_id . ',' .
		'label="' . $ds_row["label"] . '",' .
		'align=' . $ds_row["align"] . ',' .
		'show_stats=' . $ds_row["show_stats"] . ',' .
		'show_indicator=' . $ds_row["show_indicator"] . ',' .
		'hrule_value="' . $ds_row["hrule_value"] . '",' .
		'hrule_color="' . $ds_row["hrule_color"] . '",' .
		'hrule_label="' . $ds_row["hrule_label"] . '",' .
                'multiplier="'  . $ds_row["multiplier"]  . '"');
	} // end for

	header("Location: {$_SERVER["PHP_SELF"]}");
} // done duplicating.

if (empty($_REQUEST["action"]))
{
	begin_page();
	make_display_table("Graphs","Name","{$_SERVER['PHP_SELF']}?order_by=name","Comment","{$_SERVER["PHP_SELF"]}?order_by=comment","","");

	$query = "SELECT * FROM graphs";

	if (isset($_REQUEST["order_by"]))
	{
		$query .= " ORDER BY {$_REQUEST['order_by']}";
	}
	else
	{
		$query .= " ORDER BY name";
	} // end if order_by

	$graph_results = do_query($query);
	$graph_total = mysql_num_rows($graph_results);

	for ($graph_count = 1; $graph_count <= $graph_total; ++$graph_count)
	{
		$graph_row = mysql_fetch_array($graph_results);
		$graph_id  = $graph_row["id"];
		$temp_comment = str_replace("%n","<br>",$graph_row["comment"]);

		make_display_item($graph_row["name"],"./graph_items.php?graph_id=$graph_id",
		  $temp_comment,"",
		  "[<a href=\"./enclose_graph.php?type=custom&id=" . $graph_row["id"]. "\">View</a>]&nbsp;[<a href=\"{$_SERVER["PHP_SELF"]}?action=duplicate&id=" . $graph_row["id"] . "\">Duplicate</a>]","",
		  formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&graph_id=$graph_id") . "&nbsp;" .
		  formatted_link("Delete", "{$_SERVER["PHP_SELF"]}?action=delete&graph_id=$graph_id"), "");
	} // end graphs

?></table><?php

} // end no action

if ($_REQUEST["action"] == "add")
{
	// Display editing screen
	check_auth(2);
	begin_page();
	make_edit_table("Add Graph");
	make_edit_text("Name:","graph_name","25","100","");
	make_edit_text("Comment:","graph_comment","50","200","");
	make_edit_text("X Size","xsize","4","4","400");
	make_edit_text("Y Size","ysize","4","4","100");
	make_edit_text("Vertical Label","vert_label","50","100","");
	make_edit_checkbox("Show Legend (by default)","show_legend",1);
	make_edit_checkbox("Show Total Line","show_total_line",1);
	make_edit_checkbox("Show Total Stats","show_total_stats",1);
	make_edit_checkbox("Only Integers in Legend", "disp_integer_only", 0);
	make_edit_group("Maximum Indicators");
	make_edit_checkbox("Show Summed Data Source Maximum Indicators","show_summed",0);
	make_edit_text("Custom Maximum Value","max_custom",10,10,"");
	make_edit_hidden("action","doadd");
	make_edit_submit_button();
	make_edit_end();

} // End editing screen

if ($_REQUEST["action"] == "edit")
{
	// Display editing screen
	check_auth(2);
	begin_page();
	$graph_results = do_query("SELECT * FROM graphs WHERE id={$_REQUEST["graph_id"]}");
	$graph_row = mysql_fetch_array($graph_results);
	$graph_name = $graph_row["name"];
	$graph_comment = $graph_row["comment"];

	make_edit_table("Edit Graph");
	make_edit_text("Name:","graph_name","25","100",$graph_name);
	make_edit_text("Comment:","graph_comment","50","200",$graph_comment);
	make_edit_text("X Size","xsize","4","4",$graph_row["xsize"]);
	make_edit_text("Y Size","ysize","4","4",$graph_row["ysize"]);
	make_edit_text("Vertical Label","vert_label","50","100",$graph_row["vert_label"]);
	make_edit_checkbox("Show Legend (by default)","show_legend",$graph_row["show_legend"]);
	make_edit_checkbox("Show Total Line","show_total_line",$graph_row["show_total_line"]);
	make_edit_checkbox("Show Total Stats","show_total_stats",$graph_row["show_total_stats"]);
	make_edit_checkbox("Only Integers in Legend", "disp_integer_only", $graph_row["disp_integer_only"]);
	make_edit_group("Maximum Indicators");
	make_edit_checkbox("Show Summed Data Source Maximum Indicators","show_summed",$graph_row["show_summed"]);
	make_edit_text("Custom Maximum Value","max_custom",10,10,$graph_row["max_custom"]);
	make_edit_hidden("graph_id",$_REQUEST["graph_id"]);
	make_edit_hidden("action","doedit");

	if (!empty($_REQUEST["return_type"]))
	{
		make_edit_hidden("return_type",$_REQUEST["return_type"]);
		make_edit_hidden("return_id",$_REQUEST["return_id"]);
	} // end if return_type

	make_edit_submit_button();
	make_edit_end();

} // End editing screen

if ($_REQUEST["action"] == "delete")
{
	// Display delete confirmation
	check_auth(2);
	begin_page();
	?>

	<font size="4" color="#800000">Confirm Delete</font><br><br>

	Are you sure you want to delete this graph and all of its data sources?

	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
	<input type="submit" value="Yes">
	<input type="hidden" name="graph_id" value="<?php print($_REQUEST["graph_id"]); ?>">
	<input type="hidden" name="action" value="dodelete">
	</form>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
	<input type="submit" value="No">
	</form>

	<?php

} // end delete confirmation

end_page();

?>
