<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Custom Graphs Data Sources Page            #
#           graph_items.php                            #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
require_once(netmrg_root() . "lib/processing.php");
check_auth(1);

if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "";
}

if ($_REQUEST["action"] == "doedit")
{
	check_auth(2);
	if (empty($_REQUEST["show_stats"])) { $_REQUEST["show_stats"] = 0; }
	if (empty($_REQUEST["show_indicator"])) { $_REQUEST["show_indicator"] = 0; }
	if (empty($_REQUEST["show_inverted"])) { $_REQUEST["show_inverted"] = 0; }
	if (empty($_REQUEST["use_alt"])) { $_REQUEST["use_alt"] = 0; }
	if ($_REQUEST["id"] == 0)
	{
		create_graph_item($_REQUEST["mon_id"], $_REQUEST["color"], $_REQUEST["type"], $_REQUEST["label"], $_REQUEST["align"], $_REQUEST["graph_id"], $_REQUEST["show_stats"], $_REQUEST["show_indicator"], $_REQUEST["hrule_value"], $_REQUEST["hrule_color"], $_REQUEST["hrule_label"], $_REQUEST["show_inverted"], $_REQUEST["alt_graph_id"], $_REQUEST["use_alt"], $_REQUEST["multiplier"]);
	}
	else
	{
		update_graph_item($_REQUEST["id"], $_REQUEST["mon_id"], $_REQUEST["color"], $_REQUEST["type"], $_REQUEST["label"], $_REQUEST["align"], $_REQUEST["graph_id"], $_REQUEST["show_stats"], $_REQUEST["show_indicator"], $_REQUEST["hrule_value"], $_REQUEST["hrule_color"], $_REQUEST["hrule_label"], $_REQUEST["show_inverted"], $_REQUEST["alt_graph_id"], $_REQUEST["use_alt"], $_REQUEST["multiplier"]);
	}

	header("Location: {$_SERVER["PHP_SELF"]}?graph_id={$_REQUEST["graph_id"]}");
	exit(0);

} // done adding/editing

if ($_REQUEST["action"] == "move")
{
	// do moving and stuff (... and things)
	do_update("UPDATE graph_ds SET position={$_REQUEST["val"]} WHERE id={$_REQUEST["id"]}");
	header("Location: {$_SERVER["PHP_SELF"]}?graph_id={$_REQUEST["graph_id"]}");
	exit(0);

} // end do move

if ($_REQUEST["action"] == "dodelete")
{
	check_auth(2);
	delete_ds($id);
	header("Location: {$_SERVER["PHP_SELF"]}?graph_id={$_REQUEST["graph_id"]}");
	exit(0);

} // done deleting

if (empty($_REQUEST["action"]))
{
	// Change databases if necessary and then display list
	begin_page();

	js_confirm_dialog("del", "Are you sure you want to delete graph item ", "?", "$SCRIPT_NAME?action=dodelete&graph_id={$_REQUEST["graph_id"]}&id=");

	$ds_results = do_query("
		SELECT
		graphs.name AS name,
		graph_ds.label AS label,
		graph_ds.id AS id,
		graph_ds.position AS pos
		FROM graph_ds
		LEFT JOIN graphs ON graph_ds.graph_id=graphs.id
		WHERE graph_ds.graph_id={$_REQUEST["graph_id"]}
		ORDER BY position, id");

	$ds_total = mysql_num_rows($ds_results);

        $custom_add_link = "$SCRIPT_NAME?action=add&graph_id={$_REQUEST["graph_id"]}&edit_monitor=1";

	?><img align="center" src="get_graph.php?type=custom&id=<? echo($_REQUEST["graph_id"]); ?>"><br><?
	make_display_table("Graph Data Sources","Graph","","Item Label","","","");

	for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count)
	{
		// For each graph item

		$ds_row = mysql_fetch_array($ds_results);
		$id  = $ds_row["id"];

		make_display_item($ds_row["name"],"",
			$ds_row["label"],"",
			formatted_link("View", "enclose_graph.php?type=custom_ds&id=" . $ds_row["id"]) . "&nbsp;" .
			formatted_link("Move Up", "{$_SERVER["PHP_SELF"]}?action=move&val=" . ($ds_row["pos"] - 1) . "&id=" . $ds_row["id"] . "&graph_id={$_REQUEST["graph_id"]}") . "&nbsp;" .
			formatted_link("Move Down", "{$_SERVER["PHP_SELF"]}?action=move&val=" . ($ds_row["pos"] + 1) . "&id=" . $ds_row["id"] . "&graph_id={$_REQUEST["graph_id"]}"), "",
			formatted_link("Edit", "{$_SERVER["PHP_SELF"]}?action=edit&id=$id&graph_id={$_REQUEST["graph_id"]}") . "&nbsp;" .
			formatted_link("Delete", "javascript:del('" . $ds_row["label"] . "', '" . $ds_row["id"] . "')"), "");


	} // end for

?></table><?

} // end no action

if (($_REQUEST["action"] == "edit") || ($_REQUEST["action"] == "add"))
{

        if ($_REQUEST["action"] == "add")
	{
	        $_REQUEST["id"] = 0;
        }
	
	if (empty($_REQUEST["edit_monitor"]))
	{
		$_REQUEST["edit_monitor"] = 0;
	}
	
	check_auth(2);
        begin_page();
        $ds_results = do_query("SELECT * FROM graph_ds WHERE id={$_REQUEST["id"]}");
        $ds_row = mysql_fetch_array($ds_results);

	$ds_row["graph_id"] = $_REQUEST["graph_id"];

        js_color_dialog();
        make_edit_table("Edit Graph Item");
        make_edit_text("Item Label:","label","50","100",$ds_row["label"]);

	if ($_REQUEST["edit_monitor"] == 1)
	{
	        make_edit_select_monitor($ds_row["src_id"]);
        }
	else
	{
	        make_edit_label("<big><b>Monitor:</b><br>  " . get_monitor_name($ds_row["src_id"]) . "  [<a href='{$_SERVER["PHP_SELF"]}?id={$_REQUEST["id"]}&action={$_REQUEST["action"]}&graph_id={$_REQUEST["graph_id"]}&edit_monitor=1'>change</a>]</big>");
	        make_edit_hidden("mon_id", $ds_row["src_id"]);
        }

        make_edit_select_from_table("Item Type:","type","graph_types", $ds_row["type"]);
        make_edit_color("Item Color:", "color", $ds_row["color"]);
        make_edit_select_from_table("Alignment:","align","static_pad_types", $ds_row["align"]);
        make_edit_checkbox("Show Stats","show_stats", $ds_row["show_stats"]);
        make_edit_checkbox("Show Inverted","show_inverted", $ds_row["show_inverted"]);
        if ($ds_row["multiplier"] == "") { $ds_row["multiplier"] = "1"; }
        make_edit_text("Value Multiplier:","multiplier","20","20",$ds_row["multiplier"]);
        make_edit_group("Maximum Indicator");
        make_edit_checkbox("Show Indicator","show_indicator", $ds_row["show_indicator"]);
        make_edit_text("Value","hrule_value","15","15", $ds_row["hrule_value"]);
        make_edit_color("Color","hrule_color", $ds_row["hrule_color"]);
        make_edit_text("Label","hrule_label","50","100", $ds_row["hrule_label"]);
        make_edit_group("Advanced Options");
        make_edit_checkbox("Use Alternate Child","use_alt", $ds_row["use_alt"]);
        make_edit_select_from_table("Alternate Child Graph:","alt_graph_id","graphs", $ds_row["alt_graph_id"]);
        make_edit_hidden("action","doedit");
        make_edit_hidden("id",$ds_row["id"]);
        make_edit_submit_button();
        make_edit_end();

} // End editing screen

end_page();

?>
