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

if ($action == "doedit") {
check_auth(2);
if (!isset($show_stats)) { $show_stats = 0; }
if (!isset($show_indicator)) { $show_indicator = 0; }
if (!isset($show_inverted)) { $show_inverted = 0; }
if (!isset($use_alt)) { $use_alt = 0; }
if ($id == 0) {
create_graph_item($mon_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier);
} else {
update_graph_item($id, $mon_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier);
}
} # done adding/editing

if ($action == "move") {
# do moving and stuff (... and things)
do_update("UPDATE graph_ds SET position=$val WHERE id=$id");
unset($action);
} # end do move

if ($action == "dodelete") {
check_auth(2);
delete_ds($id);
} # done deleting

if ((!isset($action)) || ($action == "doedit") || ($action == "dodelete") || ($action == "doadd")) {
# Change databases if necessary and then display list
begin_page();

$where = "";
if (isset($graph_id)) { $where = " WHERE graph_ds.graph_id=$graph_id"; $delete_append = "&graph_id=$graph_id"; }

js_confirm_dialog("del", "Are you sure you want to delete graph item ", "?", "$SCRIPT_NAME?action=dodelete" . $delete_append . "&id=");

$ds_results = do_query("
SELECT
graphs.name AS name,
graph_ds.label AS label,
graph_ds.id AS id,
graph_ds.position AS pos
FROM graph_ds
LEFT JOIN graphs ON graph_ds.graph_id=graphs.id" . $where . " ORDER BY position, id");
$ds_total = mysql_num_rows($ds_results);

if (isset($graph_id)) {
$custom_add_link = "$SCRIPT_NAME?action=add&graph_id=$graph_id&edit_monitor=1";}

make_display_table("Custom Graph Data Sources","Graph","","Item Label","","","");

for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count) {
# For each group

$ds_row = mysql_fetch_array($ds_results);
$id  = $ds_row["id"];

if (isset($graph_id)) {
$graph_id_tag = "&graph_id=$graph_id";
}

make_display_item($ds_row["name"],"",
$ds_row["label"],"",
formatted_link("View", "./enclose_graph.php?type=custom_ds&id=" . $ds_row["id"]) . "&nbsp;" .
formatted_link("Move Up", "$SCRIPT_NAME?action=move&val=" . ($ds_row["pos"] - 1) . "&id=" . $ds_row["id"] . "$graph_id_tag") . "&nbsp;" .
formatted_link("Move Down", "$SCRIPT_NAME?action=move&val=" . ($ds_row["pos"] + 1) . "&id=" . $ds_row["id"] . "$graph_id_tag"), "",
formatted_link("Edit", "$SCRIPT_NAME?action=edit&id=$id") . "&nbsp;" .
formatted_link("Delete", "javascript:del('" . $ds_row["label"] . "', '" . $ds_row["id"] . "')"), "");


}

?></table><?

} # end no action

if (($action == "edit") || ($action == "add"))
{

        if ($action == "add")
	{
	        $id = 0;
        }

        check_auth(2);
        begin_page();
        $ds_results = do_query("SELECT * FROM graph_ds WHERE id=$id");
        $ds_row = mysql_fetch_array($ds_results);
        $ds_name = $ds_row["name"];

	if (isset($graph_id))
	{
	        $ds_row["graph_id"] = $graph_id;
        }

        js_color_dialog();
        make_edit_table("Edit Graph Item");
        make_edit_text("Item Label:","label","50","100",$ds_row["label"]);
	if ($edit_graph == 1)
	{
	        make_edit_select_from_table("Graph:","graph_id","graphs",$ds_row["graph_id"]);
        } else {
		if (isset($graph_id))
		{
		        $graph_thingy = "&graph_id=$graph_id";
                }
	        make_edit_label("<big><b>Graph:</b><br>  " . get_graph_name($ds_row["graph_id"]) . "  [<a href='$SCRIPT_NAME?id=$id&action=$action" . $graph_thingy . "&edit_graph=1&edit_monitor=$edit_monitor'>change</a>]</big>");
	        make_edit_hidden("graph_id", $ds_row["graph_id"]);
        }

	if ($edit_monitor == 1)
	{
	        make_edit_select_monitor($ds_row["src_id"]);
        } else {
                if (isset($graph_id))
		{
		        $graph_thingy = "&graph_id=$graph_id";
                }
	        make_edit_label("<big><b>Monitor:</b><br>  " . get_monitor_name($ds_row["src_id"]) . "  [<a href='$SCRIPT_NAME?id=$id&action=$action" . $graph_thingy . "&edit_graph=$edit_graph&edit_monitor=1'>change</a>]</big>");
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

} # End editing screen

end_page();

?>
