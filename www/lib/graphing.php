<?php
/********************************************
* NetMRG Integrator
*
* graphing.php
* RRDTOOL Command Integration Library
*
* see doc/LICENSE for copyright information
********************************************/

/**
* dereference_templated_monitor()
*
* determines the actual monitor referenced in a graph template
*
* @param int $mon_id      the monitor id as defined in the graph template
* @param int $subdev_id   the subdevice id to which the template is applied
*/
function dereference_templated_monitor($mon_id, $subdev_id)
{
	if ($mon_id > 0)
	{
	
		$query	= db_query("SELECT test_id, test_type, test_params FROM monitors WHERE id=$mon_id");
		$row	= db_fetch_array($query);

		$query2	= db_query("SELECT id FROM monitors WHERE sub_dev_id=$subdev_id AND test_id={$row['test_id']} AND test_type={$row['test_type']} AND test_params='{$row['test_params']}'");
		$row2   = db_fetch_array($query2);
	
		return $row2["id"];
	}
	else
	{
		return $mon_id;
	}
} // end dereference_templated_monitor();


function get_graph_command($type, $id, $hist)
{

	// Determine the time domain of the graph

	$end_time = "-360";

	switch ($hist)
	{
		case 0:
			// daily view - 30 hours
			$start_time = "-108000";
			$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600));
			$sum_label = "24 Hour";
			$sum_time = "86400";
			//$break_time = (time() - (time() % 86400) + (5 * 3600));
			break;
		case 1:
			// weekly view - 9 days
			$start_time = "-777600";
			$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("w") * 86400));
			$sum_label = "7 Day";
			$sum_time = "604800";
			//$break_time = (time() - (time() % 604800) + (5 * 3600));
			break;
		case 2:
			// montly view - 6 weeks
			$start_time = "-3628800";
			$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("d") * 86400));
			$sum_label = "4 Week";
			$sum_time = "2419200";
			//$break_time = (time() - (time() % 2419200) + (5 * 3600));
			break;
		case 3:
			// yearly view - 425 days
			$start_time = "-36720000";
			$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("z") * 86400));
			$sum_label = "1 Year";
			$sum_time = "31536000";
			//$break_time = (time() - (time() % 31536000) + (5 * 3600));
			break;
		}

	switch ($type)
	{
		case "mon":				return monitor_graph_command($id, $start_time, $end_time);
		case "tinymon":			return tiny_monitor_graph_command($id, $start_time, $end_time);
		case "custom":			return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, false, false);
		case "custom_item":		return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, false, true);
		case "template":		return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, true, false);
		case "template_item":	return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, true, true);
	}

}

function monitor_graph_command($id, $start_time, $end_time)
{

	return($GLOBALS['netmrg']['rrdtool'] . " graph - -s " . $start_time . " -e " . $end_time .
			" --title=\"" . get_monitor_name($id) . " (#" . $id . ")\"  --imgformat PNG -g " .
			"DEF:data1=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $id . ".rrd:mon_" . $id . ":AVERAGE " .
			"AREA:data1#151590");

}

function tiny_monitor_graph_command($id, $start_time, $end_time)
{
	return($GLOBALS['netmrg']['rrdtool'] . " graph - -s $start_time -e $end_time -a PNG -g -w 275 -h 25 " .
		"DEF:data1=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_$id.rrd:mon_$id:AVERAGE " .
		"AREA:data1#151590");
}

function custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, $templated, $single_ds)
{
	$options = "";

	if ($single_ds)
	{
		$ds_q = db_query("SELECT graph_id FROM graph_ds WHERE id=$id");
		$ds_r = db_fetch_array($ds_q);
		$ds_id = $id;
		$id = $ds_r["graph_id"];
	}
	
	$graph_results = db_query("SELECT * FROM graphs WHERE id=$id");
	$graph_row = db_fetch_array($graph_results);

	if ($templated)
	{	
		$fields = array($graph_row['title'], $graph_row['vert_label'], $graph_row['comment']);
		$fields = expand_parameters($fields, $_REQUEST['subdev_id']);
		$graph_row['title'] 		= $fields[0];
		$graph_row['vert_label'] 	= $fields[1];
		$graph_row['comment'] 		= $fields[2];
	}

	if (isset($_REQUEST['start']))
	{
		$start_time = $_REQUEST['start'];
	}
	
	if (isset($_REQUEST['end']))
	{
		$end_time = $_REQUEST['end'];
	}
	
	if (isset($_REQUEST['min']) && isset($_REQUEST['max']) && ($_REQUEST['max'] > $_REQUEST['min']))
	{
		$boundary = " -r -l {$_REQUEST['min']} -u {$_REQUEST['max']}";
	}
	else
	{
		//$boundary = " --alt-autoscale-max";
		$boundary = "";
	}
		
	// initial definition
	$command = $GLOBALS['netmrg']['rrdtool'] . " graph - -s " . $start_time . " -e " . $end_time . $boundary . " --title \"" . $graph_row["title"] . "\" -w " .
			$graph_row["width"] . " -h " . $graph_row["height"] . " -v \"" . $graph_row["vert_label"] .
			"\" --imgformat PNG $options";


	// *** Padded Length Calculation
	$padded_length = 5;
	$ds_results = db_query("SELECT max(length(graph_ds.label)) as maxlen FROM graph_ds WHERE graph_ds.graph_id=$id");
	$ds_row = mysql_fetch_array($ds_results);
	if (!empty($ds_row['maxlen']) && $padded_length < $ds_row['maxlen'])
	{
		$padded_length = $ds_row['maxlen'];
	}
	// ***

	if ($single_ds)
	{
		$ds_where = "id=$ds_id";
	}
	else
	{
		$ds_where = "graph_id=$id ORDER BY position, id";
	}
	
	$ds_results = db_query("SELECT * FROM graph_ds WHERE $ds_where");
	$ds_total = db_num_rows($ds_results);

	$CDEF_A = "zero,UN,0,0,IF";
	$CDEF_L = "zero,UN,0,0,IF";
	$CDEF_M = "zero,UN,0,0,IF";

	$command .= " DEF:zero=" . $GLOBALS['netmrg']['rrdroot'] . "/zero.rrd:mon_25:AVERAGE ";

	for ($ds_count = 1; $ds_count <= $ds_total; $ds_count++)
	{

		$ds_row = db_fetch_array($ds_results);
		$ds_row["type"] = $GLOBALS["RRDTOOL_ITEM_TYPES"][$ds_row["type"]];
		
		if ($single_ds && ($ds_row["type"] == "STACK"))
		{
			$ds_row["type"] = "AREA";
		}

		// time periods
		if (($ds_row['start_time'] != "") && ($ds_row['end_time'] != ""))
		{
			if (strpos($ds_row['start_time'], "+") !== false)
			{
				$ds_row['start_time'] = strtotime(substr($ds_row['start_time'],1));
			}
			
			if (strpos($ds_row['end_time'], "+") !== false)
			{
				$ds_row['end_time'] = strtotime(substr($ds_row['end_time'],1));
			}
			
			if ($sum_time != 86400)
			{
				$ds_row['start_time'] = 0;
				$ds_row['end_time'] = 0;
			}
			
			$time_pre		= "TIME,{$ds_row['start_time']},{$ds_row['end_time']},LIMIT,UN,UNKN,";
			$time_post		= ",IF";
			$time_shaping	= true;
		}
		else
		{
			$time_shaping	= false;
			$time_pre		= "";
			$time_post		= "";
		}
				
		// Data is from a monitor
		if ($ds_row['mon_id'] >= 0)
		{
			if ($templated)
			{
				$ds_row["mon_id"] = dereference_templated_monitor($ds_row["mon_id"], $_REQUEST['subdev_id']);
			}
		
			$rawness = (($ds_row["multiplier"] == 1) && !$time_shaping) ? "" : "raw_"; 
			$command .= " DEF:" . $rawness . "data" . $ds_count . "="  . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":AVERAGE " .
						" DEF:" . $rawness . "data" . $ds_count . "l=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":LAST " .
						" DEF:" . $rawness . "data" . $ds_count . "m=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":MAX ";

			if (($ds_row["multiplier"] != 1) || $time_shaping)
			{
				if ($templated)
				{
					$fields = expand_parameters(array($ds_row["multiplier"]), $_REQUEST['subdev_id']);
					$ds_row["multiplier"] = $fields[0];
				}
				$ds_row["multiplier"] = simple_math_parse($ds_row["multiplier"]);
				$command .= "CDEF:data" . $ds_count . "="  . $time_pre . "raw_data" . $ds_count . "," . $ds_row["multiplier"] . ",*" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "l=" . $time_pre . "raw_data" . $ds_count . "l," . $ds_row["multiplier"] . ",*" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "m=" . $time_pre . "raw_data" . $ds_count . "m," . $ds_row["multiplier"] . ",*" . $time_post . " ";
			}
		}
		// Data is from a fixed value
		elseif ($ds_row['mon_id'] == -1)
		{
			if ($templated)
			{
				$fields = expand_parameters(array($ds_row["multiplier"]), $_REQUEST['subdev_id']);
				$ds_row["multiplier"] = $fields[0];
			}
			if ($ds_row["multiplier"] != "INF")
			{
				$ds_row["multiplier"] = simple_math_parse($ds_row["multiplier"]);
				$command .= "CDEF:data" . $ds_count . "="  . $time_pre . "zero,UN,1,1,IF," . $ds_row["multiplier"] . ",*" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "l=" . $time_pre . "zero,UN,1,1,IF," . $ds_row["multiplier"] . ",*" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "m=" . $time_pre . "zero,UN,1,1,IF," . $ds_row["multiplier"] . ",*" . $time_post . " ";
			}
			else
			{
				$command .= "CDEF:data" . $ds_count . "="  . $time_pre . "zero,UN,INF,INF,IF" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "l=" . $time_pre . "zero,UN,INF,INF,IF" . $time_post . " ";
				$command .= "CDEF:data" . $ds_count . "m=" . $time_pre . "zero,UN,INF,INF,IF" .  $time_post. " ";
			}
		}
		// Data is the sum of all prior items
		elseif ($ds_row['mon_id'] == -2)
		{
			$command .= "CDEF:data" . $ds_count . "="  . $time_pre . $CDEF_A . "," . $ds_row["multiplier"] . ",*" . $time_post . " ";
			$command .= "CDEF:data" . $ds_count . "l=" . $time_pre . $CDEF_L . "," . $ds_row["multiplier"] . ",*" . $time_post . " ";
			$command .= "CDEF:data" . $ds_count . "m=" . $time_pre . $CDEF_M . "," . $ds_row["multiplier"] . ",*" . $time_post . " ";
		}

		$command .= $ds_row["type"] . ":data" . $ds_count . $ds_row["color"] . rrd_legend_escape(do_align($ds_row["label"], $padded_length, $ds_row["alignment"])) . " ";

		// define the formatting string
		if (isin($ds_row["stats"], "INTEGER"))
		{
			$format = "%5.0lf";
		}
		else
		{
			$format = "%8.2lf %s";
		}

		// Display each field requested
		if (isin($ds_row["stats"], "CURRENT"))
		{
			$command .= 'GPRINT:data' . $ds_count . 'l:LAST:"Current\\:' . $format . '" ';
		}

		if (isin($ds_row["stats"], "AVERAGE"))
		{
			$command .= 'GPRINT:data' . $ds_count . ':AVERAGE:"Average\\:' . $format . '" ';
		}

		if (isin($ds_row["stats"], "MAXIMUM"))
		{
			$command .= 'GPRINT:data' . $ds_count . 'm:MAX:"Maximum\\:' . $format . '" ';
		}

		if (isin($ds_row["stats"], "SUMS"))
		{
			$sum_val = rrd_sum($ds_row['mon_id'], -1 * $sum_time, "now", $sum_time);
			if (isin($ds_row["stats"], "INTEGER"))
			{
				$sum_text = sprintf("%.0f", $sum_val);
			}
			else
			{
				$sum_text = sanitize_number($sum_val);
			}

			$command .= " COMMENT:\"$sum_label Sum: $sum_text\" ";
		}

		if ($ds_row['label'] != "")
			$command .= 'COMMENT:"\\n" ';

		// add to the running total CDEF
		$CDEF_A .= ",data" . $ds_count . ",UN,0,data" . $ds_count . ",IF,+";
		$CDEF_L .= ",data" . $ds_count . "l,UN,0,data" . $ds_count . ",IF,+";
		$CDEF_M .= ",data" . $ds_count . "m,UN,0,data" . $ds_count . ",IF,+";

	}

	// make MRTG-like VRULE
	if ($break_time != "")
	{
		$command .= " VRULE:" . $break_time  . "#F00000";

	}

	// print out the graph comment, if any
	if ($graph_row["comment"] != "")
	{
		$temp_comment = str_replace("%n", "\" COMMENT:\"\\n\" COMMENT:\"", $graph_row["comment"]);
		$command .= ' COMMENT:"\\n"';
		$command .= ' COMMENT:"' . $temp_comment .'\\n"';
	}

	return($command);
}

?>
