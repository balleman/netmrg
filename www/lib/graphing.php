<?php
/********************************************
* NetMRG Integrator
*
* graphing.php
* RRDTOOL Command Integration Library
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
		$row['test_params'] = db_escape_string($row['test_params']);
		
		$query2	= db_query("SELECT id FROM monitors WHERE sub_dev_id='$subdev_id' AND test_id='{$row['test_id']}' AND test_type='{$row['test_type']}' AND test_params='{$row['test_params']}'");
		if ($row2 = db_fetch_array($query2))
		{
			return $row2["id"];
		}
		else
		{
			return false;
		}
	}
	else
	{
		return $mon_id;
	}
} // end dereference_templated_monitor();

function esc_colon()
{
	// if escaping a colon, return the escape sequence, otherwise an empty string

	if (!strstr($GLOBALS['netmrg']['rrdtool_version'], "1.0"))
		return "\\"; else return "";
}

function rrd_slope()
{
	// use slope smoothing if rrdtool > 1.0

	if (!strstr($GLOBALS['netmrg']['rrdtool_version'], "1.0"))
		return "-E"; else return "";
}

function rrd_watermark()
{
	// add a watermark if rrdtool > 1.0

	if (!strstr($GLOBALS['netmrg']['rrdtool_version'], "1.0"))
		return " -W 'NetMRG - www.netmrg.net' "; else return "";
}

function get_graph_command($type, $id, $hist)
{

	// Determine the time domain of the graph

	switch ($type)
	{
		case "mon":				return monitor_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist]);
		case "tinymon":			return tiny_monitor_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist]);
		case "custom":			return custom_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist], false, false);
		case "custom_item":		return custom_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist], false, true);
		case "template":		return custom_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist], true, false);
		case "template_item":	return custom_graph_command($id, $GLOBALS['TIMEFRAMES'][$hist], true, true);
	}

}

function monitor_graph_command($id, $timeframe)
{
	if (isset($_REQUEST['start']))
		{
			$timeframe['start_time'] = $_REQUEST['start'];
		}

		if (isset($_REQUEST['end']))
		{
			$timeframe['end_time'] = $_REQUEST['end'];
		}

		if (strpos($timeframe['start_time'], " ") !== false)
		{
			$timeframe['start_time'] = strtotime(substr($timeframe['start_time'],1));

		}

		if (strpos($timeframe['end_time'], " ") !== false)
		{
			$timeframe['end_time'] = strtotime(substr($timeframe['end_time'],1));
		}

		if (isset($_REQUEST['min']) && isset($_REQUEST['max']) && ($_REQUEST['max'] > $_REQUEST['min']))
		{
			$boundary = " -r -l {$_REQUEST['min']} -u {$_REQUEST['max']}";
		}
		else
		{
			$boundary = "";
		}

	return($GLOBALS['netmrg']['rrdtool'] . " graph - " . rrd_slope() . " -s " . $timeframe['start_time'] . 
			" -e " . $timeframe['end_time'] . " " . $boundary .
			" --title=" . escapeshellarg(get_monitor_name($id) . " (#" . $id . ")") . " --imgformat PNG -g -w 575 -h 100 " .
			"DEF:data1=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $id . ".rrd:mon_" . $id . ":AVERAGE " .
			"AREA:data1#151590");

}

function tiny_monitor_graph_command($id, $timeframe)
{
	return($GLOBALS['netmrg']['rrdtool'] . " graph - " . rrd_slope() . " -s {$timeframe['start_time']} " . 
		"-e {$timeframe['end_time']} -a PNG -g -w 275 -h 25 " .
		"DEF:data1=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_$id.rrd:mon_$id:AVERAGE " .
		"AREA:data1#151590");
}

function custom_graph_command($id, $timeframe, $templated, $single_ds)
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
		$graph_row['title'] 		= escapeshellarg($fields[0]);
		$graph_row['vert_label'] 	= escapeshellarg($fields[1]);
		$graph_row['comment'] 		= escapeshellarg($fields[2]);
	}
	else
	{
		# escape the arguments in either case
		$graph_row['title']		= escapeshellarg($graph_row['title']);
		$graph_row['vert_label']	= escapeshellarg($graph_row['vert_label']);
		$graph_row['comment']		= escapeshellarg($graph_row['comment']);
	}
	
	# escapeshellarg() won't enclose an empty string in quotes, so
	# fix that up if necessary
	if ($graph_row['title'] == '')
		$graph_row['title'] = "''";	
	if ($graph_row['vert_label'] == '')
		$graph_row['vert_label'] = "''";	
	if ($graph_row['comment'] == '')
		$graph_row['comment'] = "''";	
	
	if (isset($_REQUEST['start']))
	{
		$timeframe['start_time'] = $_REQUEST['start'];
		$timeframe['overridden'] = true;
	}

	if (isset($_REQUEST['end']))
	{
		$timeframe['end_time'] = $_REQUEST['end'];
		$timeframe['overridden'] = true;
	}

	if (strpos($timeframe['start_time'], " ") !== false)
	{
		$timeframe['start_time'] = strtotime(substr($timeframe['start_time'],1));
	}

	if (strpos($timeframe['end_time'], " ") !== false)
	{
		$timeframe['end_time'] = strtotime(substr($timeframe['end_time'],1));
	}
	
	if (isset($timeframe['overridden']) && ($timeframe['overridden']))
	{
		$timeframe['sum_label'] = "Interval";
		$timeframe['sum_time'] = $timeframe['end_time'] - $timeframe['start_time'];
	}

	if (isset($_REQUEST['min']) && isset($_REQUEST['max']) && ($_REQUEST['max'] > $_REQUEST['min']))
	{
		$boundary = " -r -l {$_REQUEST['min']} -u {$_REQUEST['max']}";
	}
	elseif (isset($graph_row['min']) || isset($graph_row['max']))
	{
		$boundary = " -r";
		
		if (isset($graph_row['min']))
		{
			$boundary .= " -l " . $graph_row['min'];
		}
		
		if (isset($graph_row['max']))
		{
			$boundary .= " -u " . $graph_row['max'];
		}
	}
	else
	{
		$boundary = "";
	}

	// options
	$options = " ";
	if (isin($graph_row["options"], "nolegend"))
	{
		$options .= "-g ";
	}
	if (isin($graph_row["options"], "logarithmic"))
	{
		$options .= "-o ";
	}

	// initial definition
	$command = $GLOBALS['netmrg']['rrdtool'] . " graph - " . rrd_slope() . rrd_watermark() . " -s " . $timeframe['start_time'] . 
			" -e " . $timeframe['end_time'] . $boundary . " --title " . $graph_row["title"] . " -w " .
			$graph_row["width"] . " -h " . $graph_row["height"] . $options . "-b " . $graph_row["base"] . " -v " .
			$graph_row["vert_label"] . " --imgformat PNG $options";

	// setup condition to only display non-AVERAGE items on graphs 
	$ew = "";
	if ($timeframe['show_max'] == false)
	{
		$ew = "AND cf=1";
	}

	// *** Padded Length Calculation
	$padded_length = 5;
	$ds_results = db_query("SELECT max(length(graph_ds.label)) as maxlen FROM graph_ds WHERE graph_ds.graph_id=$id $ew");
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
		$ds_where = "graph_id=$id $ew ORDER BY position, id";
	}

	$ds_results = db_query("SELECT * FROM graph_ds WHERE $ds_where");
	$ds_total = db_num_rows($ds_results);

	$CDEF_A = "zero,UN,0,0,IF";
	$CDEF_L = "zero,UN,0,0,IF";
	$CDEF_M = "zero,UN,0,0,IF";
	$total_sum = 0;

	$command .= " DEF:zero=" . $GLOBALS['netmrg']['rrdroot'] . "/zero.rrd:mon_25:AVERAGE ";

	for ($ds_count = 1; $ds_count <= $ds_total; $ds_count++)
	{

		$ds_row = db_fetch_array($ds_results);
		
		// work around those using STACKs at the bottom of a graph
		if (($ds_row['type'] == 5) && ($ds_count == 1))
		{
			$ds_row['type'] = 4;
		}
		
		$ds_row["type"] = $GLOBALS["RRDTOOL_ITEM_TYPES"][$ds_row["type"]];

		if ($single_ds && ($ds_row["type"] == "STACK"))
		{
			$ds_row["type"] = "AREA";
		}

		// time periods
		if (($ds_row['start_time'] != "") && ($ds_row['end_time'] != ""))
		{
			$relative_times = false;

			if (strpos($ds_row['start_time'], "+") !== false)
			{
				$ds_row['start_time'] = strtotime(substr($ds_row['start_time'],1));
				$relative_times = true;
			}

			if (strpos($ds_row['end_time'], "+") !== false)
			{
				$ds_row['end_time'] = strtotime(substr($ds_row['end_time'],1));
				$relative_times = true;
			}

			if (($timeframe['sum_time'] != 86400) && $relative_times)
			{
				$ds_row['start_time'] = 0;
				$ds_row['end_time'] = 0;
			}

			if (!$relative_times)
			{
				$time_pre		= "TIME,{$ds_row['start_time']},{$ds_row['end_time']},LIMIT,UN,UNKN,";
			}
			else
			{
				$time_pre		= "TIME,{$ds_row['start_time']},{$ds_row['end_time']},LIMIT,UN," .
					"TIME," . ($ds_row['start_time'] - 86400) . "," . ($ds_row['end_time'] - 86400) . "," .
					"LIMIT,UN,MIN,UNKN,";
			}
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

			$rawness = (($ds_row["multiplier"] == "1") && !$time_shaping) ? "" : "raw_";
			$command .= " DEF:" . $rawness . "data" . $ds_count . "="  . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":AVERAGE " .
						" DEF:" . $rawness . "data" . $ds_count . "l=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":LAST " .
						" DEF:" . $rawness . "data" . $ds_count . "m=" . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . $ds_row["mon_id"] . ".rrd:mon_" . $ds_row["mon_id"] . ":MAX ";

			if (($ds_row["multiplier"] != "1") || $time_shaping)
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

			// Reset totals
			$CDEF_A = "zero,UN,0,0,IF";
			$CDEF_L = "zero,UN,0,0,IF";
			$CDEF_M = "zero,UN,0,0,IF";
		}

		$suffix = "";
		if ($ds_row['cf'] == 2)
		{
			$suffix = "m";
		}
		$command .= $ds_row["type"] . ":data" . $ds_count . $suffix . $ds_row["color"] . rrd_legend_escape(do_align($ds_row["label"], $padded_length, $ds_row["alignment"])) . " ";

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

		if (isin($ds_row["stats"], "MINIMUM"))
		{
			$command .= 'GPRINT:data' . $ds_count . 'm:MIN:"Minimum\\:' . $format . '" ';
		}

		if (isin($ds_row["stats"], "SUMS"))
		{
			if ($ds_row['mon_id'] > 0)
			{
				$sum_val = rrd_sum($ds_row['mon_id'], -1 * $timeframe['sum_time'], "now", $timeframe['sum_time']);
				if (isin($ds_row["stats"], "MULTSUM"))
				{
					$sum_val = $sum_val * $ds_row['multiplier'];
				}
				$total_sum += $sum_val;
			}
			elseif ($ds_row['mon_id'] == -2)
			{
				$sum_val = $total_sum;
				$total_sum = 0;
			}

			if (isin($ds_row["stats"], "INTEGER"))
			{
				$sum_text = sprintf("%.0f", $sum_val);
			}
			else
			{
				$sum_text = sanitize_number($sum_val);
			}
			
			$command .= " COMMENT:'". $timeframe['sum_label'] . " Sum" . esc_colon() . ": $sum_text" . "' ";
		}

		if ($ds_row['label'] != "")
			$command .= 'COMMENT:"\\n" ';

		// add to the running total CDEF
		if (($ds_row["multiplier"] != "INF") && ($ds_row["mon_id"] != -2))
		{
			$CDEF_A .= ",data" . $ds_count . ",UN,0,data"  . $ds_count . ",IF,+";
			$CDEF_L .= ",data" . $ds_count . "l,UN,0,data" . $ds_count . ",IF,+";
			$CDEF_M .= ",data" . $ds_count . "m,UN,0,data" . $ds_count . ",IF,+";
		}
	}

	// make MRTG-like VRULE
	$command .= " VRULE:" . $timeframe['break_time']  . "#F00000";

	// print out the graph comment, if any
	if ($graph_row["comment"] != "''")
	{
		$temp_comment = str_replace(":", esc_colon() . ":", $graph_row["comment"]);
		$temp_comment = str_replace("%n", '\' COMMENT:\\\n COMMENT:\'', $temp_comment);
		$command .= ' COMMENT:\\\n';
		$command .= ' COMMENT:' . $temp_comment;
		$command .= ' COMMENT:\\\n';
	}

	return($command);
}

?>
