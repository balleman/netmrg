<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Graphing Engine                            #
#                                                      #
#           RRDTOOL Command Integration Library        #
#           graphing.php                               #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");
require_once(netmrg_root() . "lib/processing.php");

function get_graph_command($type, $id, $hist, $togglelegend) {

# Determine what domain the graph is for

$end_time = "-360";

switch ($hist) {
	case 0:
		// daily view - 30 hours
		$start = "-108000";
		$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600));
		$sum_label = "24 Hour";
		$sum_time = "86400";
		#$break_time = (time() - (time() % 86400) + (5 * 3600));
		break;
	case 1:
		// weekly view - 9 days
		$start = "-777600";
		$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("w") * 86400));
		$sum_label = "7 Day";
		$sum_time = "604800";
		#$break_time = (time() - (time() % 604800) + (5 * 3600));
		break;
	case 2:
		// montly view - 6 weeks
		$start = "-3628800";
		$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("d") * 86400));
		$sum_label = "4 Week";
		$sum_time = "2419200";
		#$break_time = (time() - (time() % 2419200) + (5 * 3600));
		break;
	case 3:
		// yearly view - 425 days
		$start = "-36720000";
		$break_time = (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("z") * 86400));
		$sum_label = "1 Year";
		$sum_time = "31536000";
		#$break_time = (time() - (time() % 31536000) + (5 * 3600));
		break;
	}

if ($type == "mon") {
	
	Return(get_path_by_name("rrdtool") . " graph - -s " . $start . " -e " . $end_time .
			" --title=\"" . get_monitor_name($id) . " (#" . $id . ")\"  --imgformat PNG -g " .
			"DEF:data1=" . netmrg_root() . "rrd/mon_" . $id . ".rrd:mon_" . $id . ":AVERAGE " .
			"AREA:data1#151590");
	
}

if ($type == "tinymon")
{
	return(get_path_by_name("rrdtool") . " graph - -s $start -e $end_time -a PNG -g -w 275 -h 25 " .
		"DEF:data1=" . netmrg_root() . "rrd/mon_$id.rrd:mon_$id:AVERAGE " .
		"AREA:data1#151590");
}

if ($type == "custom_ds") {
	
	$results = do_query("
	SELECT
	graph_ds.src_id AS src_id,
	graph_types.name AS type,
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
	LEFT JOIN graph_types ON graph_ds.type=graph_types.id
	LEFT JOIN graphs ON graph_ds.graph_id=graphs.id
	WHERE graph_ds.id=$id");
	$row = mysql_fetch_array($results);
	
	if ($row["type"] == "STACK") { $row["type"] = "AREA"; }
	if ($row["multiplier"] == "") { $row["multiplier"] = 1; }
	
	if ($row["show_indicator"]) { 
		$append = " HRULE:" . $row["hrule_value"] . $row["hrule_color"];
		if ($row["hrule_label"] != "") { $append .= ":\"" . $row["hrule_label"] . '"'; }
		} else { $append = ""; }
	if ($ds_row["multiplier"] == 0) { $ds_row["multiplier"] = 1; }

        if ($ds_row["disp_integer_only"]) 
	{
        	$gprint =
        	"GPRINT:data1l:LAST:\"Current\\:%8.2lf %s\" " .
                "GPRINT:data1:AVERAGE:\"Average\\:%8.2lf %s\" " .
                "GPRINT:data1m:MAX:\"Maximum\\:%8.2lf %s\\n\"";

	} else {

		$gprint =
		"GPRINT:data1l:LAST:\"Current\\:%5.0lf %s\" " .
                "GPRINT:data1:AVERAGE:\"Average\\:%5.0lf %s\" " .
                "GPRINT:data1m:MAX:\"Maximum\\:%5.0lf %s\\n\"" ;
	}


	Return(get_path_by_name("rrdtool") . " graph - -s " . $start . " -e " . $end_time . 
			" --title=\"" . $row["title"] . "\" --imgformat PNG -v \"" . $row["vert"] . "\" " .
			"DEF:raw_data1=" . netmrg_root() . "rrd/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":AVERAGE " .
			"DEF:raw_data1l=" . netmrg_root() . "rrd/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":LAST " .
			"DEF:raw_data1m=" . netmrg_root() . "rrd/mon_" . $row["src_id"] . ".rrd:mon_" . $row["src_id"] . ":MAX " .
	            	"CDEF:data1=raw_data1," . $row["multiplier"] . ",* " . 
        	        "CDEF:data1l=raw_data1l," . $row["multiplier"] . ",* " . 
	                "CDEF:data1m=raw_data1m," . $row["multiplier"] . ",* " .
			$row["type"] . ":data1" . $row["color"] .":\"" . $row["label"] . "\" " .
			$gprint . $append); 
	 
}

if ($type == "custom") {
	
	$graph_results = do_query("SELECT * FROM graphs WHERE id=$id");
	$graph_row = mysql_fetch_array($graph_results);
	
	if ($togglelegend == 1) { $graph_row["show_legend"] = (1 - $graph_row["show_legend"]); }
	if ($graph_row["show_legend"] == 0) { $options = "-g "; }
	
	$command = get_path_by_name("rrdtool") . " graph - -s " . $start . " -e " . $end_time . " --alt-autoscale-max --title \"" . $graph_row["name"] . "\" -w " . 
			   $graph_row["xsize"] . " -h " . $graph_row["ysize"] . " -v \"" . $graph_row["vert_label"] . 
			   "\" --imgformat PNG $options";
	
	$padded_length = 5;
	$ds_results = do_query("SELECT graph_ds.label FROM graph_ds WHERE graph_ds.graph_id=$id");
	$ds_total = mysql_num_rows($ds_results);
	for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count) { 
	$ds_row = mysql_fetch_array($ds_results);
	if (strlen($ds_row["label"]) > $padded_length) {
		$padded_length = strlen($ds_row["label"]);
		}
	} # end for;
	
	$ds_results = do_query("
	SELECT
	graph_ds.src_id AS src_id,
	graph_types.name AS type,
	graph_ds.color AS color,
	graph_ds.label AS label,
	graph_ds.align AS align,
	graph_ds.show_stats AS show_stats,
	graph_ds.hrule_value AS hrule_value,
	graph_ds.show_inverted AS show_inverted,
	graph_ds.multiplier AS multiplier,
	graph_ds.position AS position 
	FROM graph_ds
	LEFT JOIN graph_types ON graph_ds.type=graph_types.id
	WHERE graph_ds.graph_id=$id 
	ORDER BY graph_ds.position, graph_ds.id");
	
	
	$ds_total = mysql_num_rows($ds_results);
	
	$CDEF_A = "CDEF:total=0";
	$CDEF_L = "CDEF:totall=0";
	$CDEF_M = "CDEF:totalm=0";
	
	$hrule_total = 0;
	
	for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count) { 
	
		$ds_row = mysql_fetch_array($ds_results);
		
		$hrule_total += $ds_row["hrule_value"];
		
		$command .= " DEF:raw_data" . $ds_count . "=" . netmrg_root() . "rrd/mon_" . $ds_row["src_id"] . ".rrd:mon_" . $ds_row["src_id"] . ":AVERAGE " . 
					" DEF:raw_data" . $ds_count . "l=" . netmrg_root() . "rrd/mon_" . $ds_row["src_id"] . ".rrd:mon_" . $ds_row["src_id"] . ":LAST " .
					" DEF:raw_data" . $ds_count . "m=" . netmrg_root() . "rrd/mon_" . $ds_row["src_id"] . ".rrd:mon_" . $ds_row["src_id"] . ":MAX ";
		if ($ds_row["multiplier"] == 0) { $ds_row["multiplier"] = 1; }
		$command .= "CDEF:data" . $ds_count . "=raw_data" . $ds_count . "," . $ds_row["multiplier"] . ",* ";
		$command .= "CDEF:data" . $ds_count . "l=raw_data" . $ds_count . "l," . $ds_row["multiplier"] . ",* ";
		$command .= "CDEF:data" . $ds_count . "m=raw_data" . $ds_count . "m," . $ds_row["multiplier"] . ",* ";
		if ($ds_row["show_inverted"] == 0) {
		$command .= $ds_row["type"] . ":data" . $ds_count . $ds_row["color"] .":\"" . do_align($ds_row["label"], $padded_length, $ds_row["align"]); # . "\" ";
		} else {
		$command .= " CDEF:inv_data" . $ds_count . "=0,data" . $ds_count . ",- ";
		$command .= $ds_row["type"] . ":inv_data" . $ds_count . $ds_row["color"] .":\"" . do_align($ds_row["label"], $padded_length, $ds_row["align"]); # . "\" ";
		}
		
		
	if ($ds_row["show_stats"]) {
		if ($graph_row["disp_integer_only"]) {
			$command .=
			"\" " .
			"GPRINT:data" . $ds_count . "l:LAST:\"Current\\:%5.0lf\" " . 
			"GPRINT:data" . $ds_count . ":AVERAGE:\"Average\\:%5.0lf\" " . 
			"GPRINT:data" . $ds_count . "m:MAX:\"Maximum\\:%5.0lf";
			if ($graph_row["disp_sum"])
			{
			$sum_cmd = netmrg_root() . "bin/rrdsum.pl " . netmrg_root() . "rrd/mon_" . $ds_row["src_id"] . " -" . $sum_time . " now " . $sum_time;
			$sum_val = `$sum_cmd`;
			$sum_text = sprintf("%.0f",$sum_val);
			$command .= 
			" GPRINT:data" . $ds_count . ":AVERAGE:\"$sum_label Sum\\:\" $sum_text\"";
			} 
	} else {
		$command .=
			"\" " .
			"GPRINT:data" . $ds_count . "l:LAST:\"Current\\:%8.2lf %s\" " . 
			"GPRINT:data" . $ds_count . ":AVERAGE:\"Average\\:%8.2lf %s\" " . 
			"GPRINT:data" . $ds_count . "m:MAX:\"Maximum\\:%8.2lf %s";
			if ($graph_row["disp_sum"])
                        {
                        $sum_cmd = netmrg_root() . "bin/rrdsum.pl " . netmrg_root() . "rrd/mon_" . $ds_row["src_id"] . ".rrd -" . $sum_time . " now " . $sum_time;
                        $sum_val = `$sum_cmd`;
                        $sum_text = sanitize_number($sum_val);
                        $command .=
                        "     $sum_label Sum\\: $sum_text";
			} 

		}
	}
			
	$command .= "\\n\" ";	
		$CDEF_A .= ",data" . $ds_count . ",UN,0,data" . $ds_count . ",IF,+";
		$CDEF_L .= ",data" . $ds_count . "l,UN,0,data" . $ds_count . ",IF,+";
		$CDEF_M .= ",data" . $ds_count . "m,UN,0,data" . $ds_count . ",IF,+";
	}
	
	$command .= " " . $CDEF_A . " " . $CDEF_L . " " . $CDEF_M; 
	if ($graph_row["show_total_line"]) {
		$command .= " LINE1:total" . $graph_row["total_line_color"] . ":\"" . do_align("Total", $padded_length, 1) . '"';
	}
	if ($graph_row["show_total_stats"]) {
		if ($graph_row["disp_integer_only"]) {
		$command .= " GPRINT:totall:LAST:\"Current\\:%5.0lf\" GPRINT:total:AVERAGE:\"Average\\:%5.0lf\" GPRINT:totalm:MAX:\"Maximum\\:%5.0lf\\n\"";
		} else {
		$command .= " GPRINT:totall:LAST:\"Current\\:%8.2lf %s\" GPRINT:total:AVERAGE:\"Average\\:%8.2lf %s\" GPRINT:totalm:MAX:\"Maximum\\:%8.2lf %s\\n\"";
		}
	}
	if ($graph_row["show_summed"]) {
		$command .= " HRULE:$hrule_total#FF0000:\"Maximum Capactiy ($hrule_total)\\n\" ";
	}
	if (($graph_row["max_custom"] != "") && ($graph_row['max_custom'] != 0)) {
		$command .= " HRULE:" . $graph_row['max_custom'] . "#FF0000:\"Maximum Capacity (" . $graph_row['max_custom'] . ")\\n\" ";
	}
	// make MRTG-like VRULE
	if ($break_time != "") {
		$command .= " VRULE:" . $break_time  . "#F00000";
			
	}
	if ($graph_row["comment"] != "") {
		$temp_comment = str_replace("%n", "\" COMMENT:\"\\n\" COMMENT:\"", $graph_row["comment"]);
	  	#$temp_comment = str_replace("%r", "\\r\" COMMENT:\"", $temp_comment);
		#$temp_comment = str_replace("%l", "\\l\" COMMENT:\"", $temp_comment);
		#$temp_comment = str_replace("%c", "\\c\" COMMENT:\"", $temp_comment);
  		#$temp_comment = str_replace("%r", "\" COMMENT:\"\\r\" COMMENT:\"", $temp_comment);
                #$temp_comment = str_replace("%l", "\" COMMENT:\"\\l\" COMMENT:\"", $temp_comment);
                #$temp_comment = str_replace("%c", "\" COMMENT:\"\\c\" COMMENT:\"", $temp_comment);
		#$temp_comment =  str_replace("%l", "\\l", $temp_comment);
		#$temp_comment =  str_replace("%r", "\\r", $temp_comment);
		#$temp_comment =  str_replace("%c", "\\c", $temp_comment);
		$command .= " COMMENT:\"\\n\"";
		$command .= " COMMENT:\"" . $temp_comment ."\\n\"";
	}
#	print($command);
#	print(`$command`);
	Return($command);
	
	
	
}
}
?>
