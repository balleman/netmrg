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
		case "mon":			return monitor_graph_command($id, $start_time, $end_time);
		case "tinymon":		return tiny_monitor_graph_command($id, $start_time, $end_time);
		case "custom":		return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, false);
		case "template":	return custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, true);
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

/*

	if ($type == "custom_ds")
	{

		$results = db_query("
		SELECT
		graph_ds.src_id AS src_id,
		graph_ds.type AS type,
		graph_ds.color AS color,
		graph_ds.label AS label,
		graphs.name AS title,
		graphs.vert_label AS vert,
		graphs.disp_integer_only AS disp_integer_only,
		graph_ds.show_indicator AS show_indicator,
		graph_ds.hrule_value AS hrule_value,
		graph_ds.hrule_color AS hrule_color,
		graph_ds.hrule_label AS hrule_label,
		graph_ds.multiplier AS multiplier
		FROM graph_ds
		LEFT JOIN graphs ON graph_ds.graph_id=graphs.id
		WHERE graph_ds.id=$id");
		$row = db_fetch_array($results);

		GLOBAL $RRDTOOL_ITEM_TYPES;
		$row["type"] = $RRDTOOL_ITEM_TYPES[$row["type"]];

		if ($row["type"] == "STACK")
		{
			$row["type"] = "AREA";
		}

		if ($row["multiplier"] == "")
		{
			$row["multiplier"] = 1;
		}

		if ($row["show_indicator"])
		{
			$append = " HRULE:" . $row["hrule_value"] . $row["hrule_color"];

			if ($row["hrule_label"] != "")
			{
				$append .= ':"' . $row["hrule_label"] . '"';
			}
		}
		else
		{
			$append = "";
		}

		if ($row["multiplier"] == 0)
		{
			$ds_row["multiplier"] = 1;
		}

		if ($row["disp_integer_only"])
		{
			$gprint =
			'GPRINT:data1l:LAST:"Current\\:%8.2lf %s" ' .
			'GPRINT:data1:AVERAGE:"Average\\:%8.2lf %s" ' .
			'GPRINT:data1m:MAX:"Maximum\\:%8.2lf %s\\n"';

		}
		else
		{

			$gprint =
			'GPRINT:data1l:LAST:"Current\\:%5.0lf %s" ' .
			'GPRINT:data1:AVERAGE:"Average\\:%5.0lf %s" ' .
			'GPRINT:data1m:MAX:"Maximum\\:%5.0lf %s\\n"' ;
		}


		return($GLOBALS['netmrg']['rrdtool'] . " graph - -s " . $start . " -e " . $end_time .
				" --title=\"" . $row["title"] . "\" --imgformat PNG -v \"" . $row["vert"] . "\" " .
				"DEF:raw_data1="  . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":AVERAGE " .
				"DEF:raw_data1l=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":LAST " .
				"DEF:raw_data1m=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":MAX " .
				"CDEF:data1=raw_data1," . $row["multiplier"] . ",* " .
				"CDEF:data1l=raw_data1l," . $row["multiplier"] . ",* " .
				"CDEF:data1m=raw_data1m," . $row["multiplier"] . ",* " .
				$row["type"] . ":data1" . $row["color"] .":\"" . $row["label"] . "\" " .
				$gprint . $append);

	}*/

function custom_graph_command($id, $start_time, $end_time, $break_time, $sum_label, $sum_time, $templated)
{
	$options = "";

	$graph_results = db_query("SELECT * FROM graphs WHERE id=$id");
	$graph_row = db_fetch_array($graph_results);

	if ($templated)
	{	
		$fields = array($graph_row['name'], $graph_row['vert_label'], $graph_row['comment']);
		$fields = expand_parameters($fields, $_REQUEST['subdev_id']);
		$graph_row['name'] 			= $fields[0];
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
	$command = $GLOBALS['netmrg']['rrdtool'] . " graph - -s " . $start_time . " -e " . $end_time . $boundary . " --title \"" . $graph_row["name"] . "\" -w " .
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

	$ds_results = db_query("SELECT * FROM graph_ds WHERE graph_ds.graph_id=$id ORDER BY position, id");
	$ds_total = db_num_rows($ds_results);

	$CDEF_A = "zero,UN,0,0,IF";
	$CDEF_L = "zero,UN,0,0,IF";
	$CDEF_M = "zero,UN,0,0,IF";

	$command .= " DEF:zero=" . $GLOBALS['netmrg']['rrdroot'] . "/zero.rrd:mon_25:AVERAGE ";

	for ($ds_count = 1; $ds_count <= $ds_total; $ds_count++)
	{

		$ds_row = db_fetch_array($ds_results);
		$ds_row["type"] = $GLOBALS["RRDTOOL_ITEM_TYPES"][$ds_row["type"]];
		
		if ($templated)
		{
			$ds_row["mon_id"] = dereference_templated_monitor($ds_row["mon_id"], $_REQUEST['subdev_id']);
		}

		// Data is from a monitor
		if ($ds_row['mon_id'] >= 0)
		{
			$rawness = ($ds_row["multiplier"] == 1) ? "" : "raw_"; 
			$command .= " DEF:" . $rawness . "data" . $ds_count . "=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":AVERAGE " .
						" DEF:" . $rawness . "data" . $ds_count . "l=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":LAST " .
						" DEF:" . $rawness . "data" . $ds_count . "m=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":MAX ";

			if ($ds_row["multiplier"] != 1)
			{
				$command .= "CDEF:data" . $ds_count . "=raw_data" . $ds_count . "," . $ds_row["multiplier"] . ",* ";
				$command .= "CDEF:data" . $ds_count . "l=raw_data" . $ds_count . "l," . $ds_row["multiplier"] . ",* ";
				$command .= "CDEF:data" . $ds_count . "m=raw_data" . $ds_count . "m," . $ds_row["multiplier"] . ",* ";
			}
		}
		// Data is from a fixed value
		elseif ($ds_row['mon_id'] == -1)
		{
			$command .= "CDEF:data" . $ds_count . "=zero,UN,1,1,IF," . $ds_row["multiplier"] . ",* ";
			$command .= "CDEF:data" . $ds_count . "l=zero,UN,1,1,IF," . $ds_row["multiplier"] . ",* ";
			$command .= "CDEF:data" . $ds_count . "m=zero,UN,1,1,IF," . $ds_row["multiplier"] . ",* ";
		}
		// Data is the sum of all prior items
		elseif ($ds_row['mon_id'] == -2)
		{
			$command .= "CDEF:data" . $ds_count . "="  . $CDEF_A . "," . $ds_row["multiplier"] . ",* ";
			$command .= "CDEF:data" . $ds_count . "l=" . $CDEF_L . "," . $ds_row["multiplier"] . ",* ";
			$command .= "CDEF:data" . $ds_count . "m=" . $CDEF_M . "," . $ds_row["multiplier"] . ",* ";
		}

		$command .= $ds_row["type"] . ":data" . $ds_count . $ds_row["color"] .":\"" . do_align($ds_row["label"], $padded_length, $ds_row["alignment"]) . "\" ";

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
