<?

/*
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
*/

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/graphing.php");

function tailer()
{
	GLOBAL $_REQUEST;

	if (empty($_REQUEST["togglelegend"]))
	{
		$_REQUEST["togglelegend"] = 0;
	}
	
	if (empty($_REQUEST["hist"]))
	{
		$_REQUEST["hist"] = 0;
	}
	
	if (empty($_REQUEST["show_children"]))
	{
		$_REQUEST["show_children"] = 0;
	}


	$toggle = 1 - $_REQUEST["togglelegend"];
	$newhist = 1 - $_REQUEST["hist"];
	$new_show_children = 1 - $_REQUEST["show_children"];
	?>
	<div align="right">
	<br>
	<a href="<? print($_SERVER["PHP_SELF"]); ?>?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=<? print($newhist) ?>&togglelegend=<? print($_REQUEST["togglelegend"]) ?>&show_children=<? print($_REQUEST["show_children"]) ?>">Toggle History</a><br>
	<a href="<? print($_SERVER["PHP_SELF"]); ?>?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=<? print($_REQUEST["hist"]) ?>&togglelegend=<? print($toggle) ?>&show_children=<? print($_REQUEST["show_children"]) ?>">Toggle Legend</a><br>
	<? if ($_REQUEST["type"] == "custom") {
	?><a href="<? print($_SERVER["PHP_SELF"]); ?>?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=<? print($_REQUEST["hist"]) ?>&togglelegend=<? print($_REQUEST["togglelegend"]) ?>&show_children=<? print($new_show_children) ?>">Toggle Children</a><br>
	<? } ?>
	<Br>
	<a href="javascript:history.back(1)">Back</a>
	</div>
	<?

} // end tailer

function show_a_graph()
{
	GLOBAL $_REQUEST;

	if (empty($_REQUEST["hist"]) || ($_REQUEST["hist"] == 0))
	{
		?>
		<div align="center">
		<img src="get_graph.php?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&togglelegend=<? print($_REQUEST["togglelegend"]) ?>">
		</div>
		<?

		if (!empty($_REQUEST["show_source"]))
		{
			print("<pre>" . get_graph_command($type, $id, 0, $togglelegend) . "</pre>");
		}
	}
	else
	{

		?>
		<div align="center">
		<img src="get_graph.php?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=0&togglelegend=<? print($_REQUEST["togglelegend"]) ?>">
		<img src="get_graph.php?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=1&togglelegend=<? print($_REQUEST["togglelegend"]) ?>">
		<img src="get_graph.php?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=2&togglelegend=<? print($_REQUEST["togglelegend"]) ?>">
		<img src="get_graph.php?type=<? print($_REQUEST["type"]) ?>&id=<? print($_REQUEST["id"]) ?>&hist=3&togglelegend=<? print($_REQUEST["togglelegend"]) ?>">
		</div>
        	<?

	} // end if

} // end show_a_graph

refresh_tag();
begin_page();
show_a_graph();

if ((!empty($_REQUEST["show_children"])) && ($_REQUEST["show_children"] == 1))
{
	$query = do_query("SELECT * FROM graph_ds WHERE graph_id={$_REQUEST["id"]} ORDER BY position,id ");
	$count = mysql_num_rows($query);
	$old_id = $_REQUEST["id"];
	for ($i = 0; $i < $count; $i++)
	{
		$row = mysql_fetch_array($query);

		if ($row["use_alt"] == 0)
		{
			$_REQUEST["type"] = "custom_ds";
			$_REQUEST["id"] = $row["id"];
		}
		else
		{
			$_REQUEST["type"] = "custom";
			$_REQUEST["id"] = $row["alt_graph_id"];
		}

		show_a_graph();
	}

	$_REQUEST["type"] = "custom";
	$_REQUEST["id"] = $old_id;
}

tailer();
end_page();

?>
