<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Enclose Graph in a Page                    #
#           enclose_graph.php     		       #
#                                                      #
#           named in honor of Ian Berry's              #
#           "enclose_url.php"                          #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/graphing.php");

function tailer() {
	GLOBAL $type, $id, $togglelegend, $hist, $show_children;
	$toggle = 1 - $togglelegend;
	$newhist = 1 - $hist;
	$new_show_children = 1 - $show_children;
	?>
	<div align="right">
	<br>
	<a href="./enclose_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=<? print($newhist) ?>&togglelegend=<? print($togglelegend) ?>&show_children=<? print($show_children) ?>">Toggle History</a><br>
	<a href="./enclose_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=<? print($hist) ?>&togglelegend=<? print($toggle) ?>&show_children=<? print($show_children) ?>">Toggle Legend</a><br>
	<? if ($type == "custom") { 
	?><a href="./enclose_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=<? print($hist) ?>&togglelegend=<? print($togglelegend) ?>&show_children=<? print($new_show_children) ?>">Toggle Children</a><br>
	<? } ?>
	<Br>
	<a href="javascript:history.back(1)">Back</a>
	</div>
	<?
} # end tailer

function show_a_graph() {
	GLOBAL $type, $id, $togglelegend, $hist, $show_source;
	if ($hist == 0) {
	?>


	<div align="center">
	<img src="./get_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&togglelegend=<? print($togglelegend) ?>">
	</div>
	<?
if ($show_source == 1) { print("<pre>" . get_graph_command($type, $id, 0, $togglelegend) . "</pre>");	}
	} else {

	?>
	<div align="center">
	<img src="./get_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=0&togglelegend=<? print($togglelegend) ?>">
	<img src="./get_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=1&togglelegend=<? print($togglelegend) ?>">
	<img src="./get_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=2&togglelegend=<? print($togglelegend) ?>">
	<img src="./get_graph.php?type=<? print($type) ?>&id=<? print($id) ?>&hist=3&togglelegend=<? print($togglelegend) ?>">

	</div>

	<?

	} # end if

} # end show_a_graph
refresh_tag();
begin_page();
show_a_graph();
if ($show_children == 1) {

	$query = do_query("SELECT * FROM graph_ds WHERE graph_id=$id ORDER BY position,id ");
	$count = mysql_num_rows($query);
	$old_id = $id;
	for ($i = 0; $i < $count; $i++) {
	$row = mysql_fetch_array($query);
		if ($row["use_alt"] == 0) {
			$type = "custom_ds";
			$id = $row["id"];
		} else {
			$type = "custom";
			$id = $row["alt_graph_id"];
		}
	show_a_graph();
	}
	$type = "custom";
	$id = $old_id;
}
tailer();
end_page();

?>
