<?php
/********************************************
* NetMRG Integrator
*
* processing.php
* Internal Processing Functions
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


// Simple Formatting Section

function format_time_elapsed($num_secs)
{
	// Makes a string from a 'seconds elapsed' integer
	$the_secs = $num_secs;
	$new_secs = $num_secs % 86400;
	$days = ($num_secs - $new_secs) / 86400;
	if ($days > 10000)
	{
		return "Never";
		exit;
	}
	$num_secs = $new_secs;
	$new_secs = $num_secs % 3600;
	$hours = ($num_secs - $new_secs) / 3600;
	$num_secs = $new_secs;
	$new_secs = $num_secs % 60;
	$mins = ($num_secs - $new_secs) / 60;

	$res = "";
	if ($the_secs > 0)
	{
		if ($days > 0)
		{
			$res = sprintf("%d days, ", $days);
		}
		$res .= sprintf("%02d:%02d:%02d", $hours, $mins, $new_secs);
	}
	else
	{
		$res .= "Unavailable";
	}

	return $res;

} // end format_time_elapsed


function sanitize_number($number, $round_to = 2)
{

	$format = "%4." . $round_to . "f";

	if ($number < 1000)
	{
		return sprintf($format, $number);
	}
	elseif ($number < 1000000)
	{
		return sprintf("$format k", $number / 1000);
	}
	elseif ($number < 1000000000)
	{
		return sprintf("$format M", $number / 1000000);
	}
	elseif ($number < 1000000000000)
	{
		return sprintf("$format G", $number / 1000000000);
	}
	else
	{
		return sprintf("$format T", $number / 1000000000000);
	}

} // end sanitize_number

function paraphrase($string, $length, $etc = "...")
{
	if (strlen($string) <= $length)
		return $string;

	return substr($string, 0, $length) . $etc;
}

function make_spaces($length)
{
	return str_repeat(" ", $length);
} // end make_spaces

function make_nbsp($length)
{
	return str_repeat("&nbsp;", $length);
} // end make_nbsp


// prepends spaces to a string to cause it to be a certain length
function align_right($string, $length)
{
	$space_length = $length - strlen($string);
	return(make_spaces($space_length) . $string);
} // end align_right


function align_left($string, $length)
{
	$space_length = $length - strlen($string);
	return($string . make_spaces($space_length));
} // end align_left


function align_right_split($string, $length)
{
	$space_length = $length - strlen($string);
	$pos = strrchr($string, " ");
	return(substr($string, 0, -strlen($pos)) . make_spaces($space_length) . $pos);
} //end align_right_split


// manipulates a string by applying the appropriate padding method
function do_align($string, $length, $method)
{
	if ($string == "") return "";	

	switch ($method)
	{
		case 1:
			$result = align_left($string, $length);
			break;
		case 2:
			$result = align_right($string, $length);
			break;
		case 3:
			$result = align_right_split($string, $length);
			break;
	} // end switch($method)

	return($result);
} // end do_align

function rrd_legend_escape($string)
{
	if ($string == "") return "";
	$string = str_replace(":", "\:", $string);
	return (":" . escapeshellarg($string));
}

function get_microtime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function isin($haystack, $needle)
{
	return is_integer(strpos($haystack, $needle));
}

function compare_interface_names($a, $b)
{
	$astuff = preg_split("~([-/\. ])~", strtolower(trim($a)), 0, PREG_SPLIT_DELIM_CAPTURE);
	$bstuff = preg_split("~([-/\. ])~", strtolower(trim($b)), 0, PREG_SPLIT_DELIM_CAPTURE);
	for ($i = 0; $i < max(count($astuff), count($bstuff)); $i++)
	{
		if (isset($astuff[$i]))
		{
			if (isset($bstuff[$i]))
			{
				if ($astuff[$i] != $bstuff[$i])
				{
					if (is_numeric($astuff[$i]) && (is_numeric($bstuff[$i])))
					{
						return $astuff[$i] - $bstuff[$i];
					}
					else
					{
						return strcmp($astuff[$i], $bstuff[$i]);
					}
				}
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return -1;
		}
	}
	return 0;
}

function compare_mac_addresses($a, $b)
{
	$astuff = explode(":", $a);
	$bstuff = explode(":", $b);
	for ($i = 0; $i < 6; $i++) if (strlen($astuff[$i]) == 1) { $astuff[$i] = "0" . $astuff[$i]; }
	for ($i = 0; $i < 6; $i++) if (strlen($bstuff[$i]) == 1) { $bstuff[$i] = "0" . $bstuff[$i]; }
	$a1 = implode("", $astuff);
	$b1 = implode("", $bstuff);
	return strcmp($a1, $b1);
}

function compare_ip_addresses($a, $b)
{
	$astuff = explode(".", $a);
	$bstuff = explode(".", $b);
	for ($i = 0; $i < 4; $i++) while (strlen($astuff[$i]) != 3) { $astuff[$i] = "0" . $astuff[$i]; }
	for ($i = 0; $i < 4; $i++) while (strlen($bstuff[$i]) != 3) { $bstuff[$i] = "0" . $bstuff[$i]; }
	$a1 = implode("", $astuff);
	$b1 = implode("", $bstuff);
	return strcmp($a1, $b1);
}

/**
* simple_math_parse($input)
*
* $input - string to be parsed
*
* use eval to do math if everything looks safe. 
* 
*
*/

function simple_math_parse($input)
{
	$val = 1;
	if (!preg_match("/[^012345467890.\/*\-+]/", $input))
	{
		eval("\$val = $input;");
	}
	return $val;
}


// RRD Support Functions

/**
* rrd_sum($mon_id, $start, $end, $resolution)
*
* $mon_id	= monitor id of RRD to sum
* $start	= start time, formatted for RRDTOOL
* $end		= end time, formatted for RRDTOOL (defaults to "now")
* $resolution = resolution of data, formatted for RRDTOOL (defaults to 1 day)
*/
function rrd_sum($mon_id, $start, $end = "now", $resolution = 86400)
{
	$rrd_handle = popen($GLOBALS['netmrg']['rrdtool'] . " fetch " . $GLOBALS['netmrg']['rrdroot'] . "/mon_" . 
		$mon_id . ".rrd AVERAGE -r $resolution -s $start -e $end", "r");
	
	$row_count = 0;
	$sum = 0;
		
	while ($row = fgets($rrd_handle))
	{
		// the first two lines are of no use
		if ($row_count > 1)
		{
			// ignore missing data points
			if (!preg_match("/nan/i", $row))
			{
				$row_val = preg_replace("/.*: /", "", $row);
				list($mantissa, $exponent) = preg_split("/e/i", $row_val);
				$row_value = $mantissa * pow(10, intval($exponent));
				$sum += $row_value;
			}
		}
		$row_count++;
	}

	$average = $sum / ($row_count - 1);
	$total_sum = $average * $resolution;
	pclose($rrd_handle);
	return $total_sum;
}

/**
* rrdtool_syntax_highlight($txt)
*
* $txt		= a string normally passed to rrdtool
*/
function rrdtool_syntax_highlight($txt)
{
	$txt = preg_replace("/(#[0-9,a-f,A-F]+)/", "<span style='color:#0F4B47'>\\1</span>", $txt);
	$txt = preg_replace("/(\s)DEF:/", "\\1<span style='color:blue'>DEF</span>:", $txt);
	$txt = str_replace("\\n", "<span style='color:red'>\\n</span>", $txt);
	$txt = str_replace("CDEF", "<span style='color:green'>CDEF</span>", $txt);
	$txt = preg_replace("/(\s)(AREA|STACK|LINE1|LINE2|LINE3|HRULE|VRULE):/", "\\1<span style='color:orange'>\\2</span>:", $txt);
	$txt = preg_replace("/:(MAX|AVERAGE|LAST)/", ":<span style='color:brown'>\\1</span>", $txt);
	$txt = preg_replace("/(\s)(GPRINT|PRINT|COMMENT):/", "\\1<span style='color:red'>\\2</span>:", $txt);
	$txt = preg_replace("/(data\d+[lm]*)/", "<span style='color:#344D6C'>\\1</span>", $txt);
	//$txt = preg_replace("/=(.*\.rrd):/", "=<span style='color:grey'>\\1</span>:", $txt);
	//$txt = preg_replace("/(\s)(\-+)(\s)/", "\\1<span style='color:red'>\\2</span>\\3", $txt);
	//$txt = preg_replace("/:(\".*\") /", ":<span style='color:purple'>\\1</span>", $txt);
	return $txt;
}

// Templating Functions

function expand_parameters($input, $subdev_id)
{
	$query = db_query("SELECT * FROM sub_dev_variables WHERE type='dynamic' AND sub_dev_id=$subdev_id");

	while (($row = db_fetch_array($query)) != NULL)
	{
		$input = str_replace("%" . $row['name'] . "%", $row['value'], $input);
	}

	//$input = preg_replace("/\%..+\%/", "N/A", $input);

	return $input;
}  // expand_parameters()

function apply_template($subdev_id, $template_id)
{

	// add the appropriate monitors to the subdevice
	$q = db_query("SELECT monitors.id, data_type, test_id, test_type, test_params, min_val, max_val FROM graph_ds, monitors WHERE graph_ds.graph_id='$template_id' AND graph_ds.mon_id=monitors.id");
	for ($i = 0; $i < db_num_rows($q); $i++)
	{
		$row = db_fetch_array($q);
		if (empty($row['min_val'])) $row['min_val'] = "NULL";
		if (empty($row['max_val'])) $row['max_val'] = "NULL";

		// only add a new monitor if there is no existing one that matches the template
		if (dereference_templated_monitor($row['id'], $subdev_id) === false)
		{
			db_update("INSERT INTO monitors SET sub_dev_id=$subdev_id, data_type={$row['data_type']}, test_id={$row['test_id']}, test_type={$row['test_type']}, test_params='{$row['test_params']}', min_val={$row['min_val']}, max_val={$row['max_val']}");
		}
	}

	// add templated graph to the device's view
	$q = db_query("SELECT dev_id FROM sub_devices WHERE id=$subdev_id");
	$sd_row = db_fetch_array($q);

	$q = db_query("SELECT max(pos)+1 AS newpos FROM view WHERE object_type='device' AND object_id={$sd_row['dev_id']}");
	$pos_row = db_fetch_array($q);
	if (!isset($pos_row['newpos']) || empty($pos_row['newpos']))
	{
		$pos_row['newpos'] = 1;
	}

	db_update("INSERT INTO view SET object_id={$sd_row['dev_id']}, object_type='device', graph_id=$template_id, type='template', pos={$pos_row['newpos']}, subdev_id=$subdev_id");
	
	// add templated graph to the sub-device's view
	$q = db_query("SELECT max(pos)+1 AS newpos FROM view WHERE object_type='subdevice' AND object_id=$subdev_id");
	$pos_row = db_fetch_array($q);
	if (!isset($pos_row['newpos']) || empty($pos_row['newpos']))
	{
		$pos_row['newpos'] = 1;
	}

	db_update("INSERT INTO view SET object_id=$subdev_id, object_type='subdevice', graph_id=$template_id, type='template', pos={$pos_row['newpos']}, subdev_id=$subdev_id");

}  // apply_template()


// Recursive status determination section

//Takes a grp_id and returns the current group aggregate status
function get_group_status($grp_id)
{
	$status = -1;

	$grp_results = db_query("SELECT id FROM groups WHERE parent_id=$grp_id");

	while ($grp_row = db_fetch_array($grp_results))
	{
		$grp_status = get_group_status($grp_row["id"]);
		if (($grp_status > $status) && ($grp_status != 4))
		{
			$status = $grp_status;
		}
	} // end while rows left

	$dev_results = db_query("
		SELECT max(devices.status) AS status 
		FROM dev_parents, devices 
		WHERE grp_id = $grp_id 
		AND dev_parents.dev_id=devices.id 
		AND devices.status < 4 
		GROUP BY grp_id");
	$dev_row = db_fetch_array($dev_results);
	$grp_status = $dev_row["status"];
	if ($grp_status > $status)
	{
		$status = $grp_status;
	}

	return $status;

} // end get_group_status()



// Uniform Name Creation Section

function get_short_monitor_name($mon_id)
{
	$mon_query = db_query("
		SELECT	test_id, test_params, test_type
		FROM	monitors
		WHERE	monitors.id = $mon_id");
	$mon_row = db_fetch_array($mon_query);

	return get_short_test_name($mon_row['test_type'], $mon_row['test_id'], $mon_row['test_params']);
}

function get_short_property_name($prop_id)
{
	$prop_query = db_query("
		SELECT	test_id, test_params, test_type
		FROM	dev_props
		WHERE	dev_props.id = $prop_id");
	$prop_row = db_fetch_array($prop_query);

	return get_short_test_name($prop_row['test_type'], $prop_row['test_id'], $prop_row['test_params']);
}

function get_short_test_name($test_type, $test_id, $test_params)
{
	GLOBAL $TEST_TYPES;

	switch($test_type)
	{
		case 1:
			$test_query = "SELECT name FROM tests_script   WHERE id = " . $test_id;
			break;
		case 2:
			$test_query = "SELECT name FROM tests_snmp     WHERE id = " . $test_id;
			break;
		case 3:
			$test_query = "SELECT name FROM tests_sql      WHERE id = " . $test_id;
			break;
		case 4:
			$test_query = "SELECT name FROM tests_internal WHERE id = " . $test_id;
			break;

	} // end switch test type

	$test_row = db_fetch_array(db_query($test_query));

	$res = $test_row["name"];

	if ($test_params != "")
	{
		$res .= " - " . $test_params;
	}

	return $res;
} // end get_short_test_name()


function get_monitor_name($mon_id)
{
	$query_handle = db_query("
		SELECT  devices.name AS dev_name,
		sub_devices.name AS sub_name
		FROM monitors
		LEFT JOIN sub_devices ON monitors.sub_dev_id=sub_devices.id
		LEFT JOIN devices ON sub_devices.dev_id=devices.id
		WHERE monitors.id=$mon_id");

	$row = db_fetch_array($query_handle);

	return $row["dev_name"] . " - " . $row["sub_name"] . " (" . get_short_monitor_name($mon_id) . ")";
} // end get_monitor_name()

function get_graph_name($graph_id)
{
	$graph_query = db_query("SELECT name FROM graphs WHERE id=$graph_id");
	$graph_row   = db_fetch_array($graph_query);
	return $graph_row["name"];
}

function get_group_name($grp_id)
{
	$grp_query = db_query("SELECT name FROM groups WHERE id=$grp_id");
	$grp_row   = db_fetch_array($grp_query);
	return $grp_row["name"];
}

function get_device_name($dev_id)
{
	$dev_query = db_query("SELECT name FROM devices WHERE id=$dev_id");
	$dev_row   = db_fetch_array($dev_query);
	return $dev_row["name"];
}

function get_sub_device_name($sub_dev_id)
{
	$dev_query = db_query("SELECT name FROM sub_devices WHERE id = $sub_dev_id");
	if (db_num_rows($dev_query) == 0)
		return "Not Set";
	$row = db_fetch_array($dev_query);
	return $row["name"];
}

function get_dev_sub_device_name($sub_dev_id)
{
	$dev_query = db_query("
		SELECT devices.name AS dev_name, sub_devices.name AS sub_name
		FROM sub_devices
		LEFT JOIN devices   ON sub_devices.dev_id=devices.id
		WHERE sub_devices.id = $sub_dev_id");
	if (db_num_rows($dev_query) == 0)
		return "Not Set";
	$row = db_fetch_array($dev_query);
	return $row["dev_name"] . " - " . $row["sub_name"];
}

function get_event_name($event_id)
{
	$e_query = db_query("SELECT name FROM events WHERE id=$event_id");
	$e_row   = db_fetch_array($e_query);
	return $e_row["name"];
}


/**
* GetNumAssocItems($object_type, $object_id)
*
* $object_type = (group, device, monitor, event)
* $object_id = id
*/
function GetNumAssocItems($object_type, $object_id)
{
	$db_result = db_query("
		SELECT count(*) AS count
		FROM view, graphs
		WHERE view.graph_id = graphs.id
		AND object_type='$object_type'
		AND object_id='$object_id'");
	$row = db_fetch_array($db_result);
	return $row["count"];
} // end GetNumAssocItems();


/**
* GetGroupParents($group_id);
*
* returns all the groups parent group ids
*/
function GetGroupParents($group_id)
{
	$group_arr = array();
	$group_item = $group_id;
	
	while ($group_item != 0)
	{
		$group_item = db_fetch_cell("SELECT parent_id FROM groups WHERE id = '$group_item'");
		array_push($group_arr, $group_item);
	} // end while more parents
	
	return $group_arr;
} // end GetGroupParents();


/**
* GetGroups($type,$id);
*
* returns an array of groups that this $type is in
*
*/
function GetGroups($type,$id)
{
	$group_arr = array();
	switch ($type)
	{
		case "group":
			$query = array("SELECT '$id' as group_id");
			break;
		case "device":
			$query = array("
				SELECT groups.id AS group_id 
				FROM groups, dev_parents, devices
				WHERE devices.id = '$id' 
				AND devices.id = dev_parents.dev_id
				AND dev_parents.grp_id = groups.id
				GROUP BY group_id");
			break;
		case "subdevice":
			$query = array("
				SELECT groups.id AS group_id 
				FROM groups, dev_parents, devices, sub_devices 
				WHERE sub_devices.id = '$id' 
				AND sub_devices.dev_id = devices.id
				AND devices.id = dev_parents.dev_id
				AND dev_parents.grp_id = groups.id
				GROUP BY group_id",
				"
				SELECT object_id AS group_id
				FROM view
				WHERE object_type='group'
				AND subdev_id = '$id'
				GROUP BY group_id"
				);
			break;
		case "monitor":
			$query = array("
				SELECT groups.id AS group_id
				FROM groups, dev_parents, devices, sub_devices, monitors
				WHERE monitors.id = '$id'
				AND sub_devices.id = monitors.sub_dev_id
				AND sub_devices.dev_id = devices.id
				AND devices.id = dev_parents.dev_id
				AND dev_parents.grp_id = groups.id
				GROUP BY group_id");
			break;
		case "customgraph":
			$query = array("
				SELECT object_id, object_type
				FROM view
				WHERE type = 'graph'
				AND graph_id = '$id'");
			break;
		default:
			// an unknown type should have no groups
			return $group_arr;
	} // end switch($type)
	
	foreach ($query as $sql_cmd)
	{
		$db_result = db_query($sql_cmd);
		while ($r = mysql_fetch_array($db_result))
		{
			if ($type == "customgraph")
			{
				$group_arr = array_merge($group_arr, GetGroups($r["object_type"],$r["object_id"]));
			}
			else
			{
				array_push($group_arr, $r["group_id"]);
				$group_arr = array_merge($group_arr, GetGroupParents($r["group_id"]));
			}
		} // end while we have results
	}
	
	return $group_arr;
} // end GetGroups();


/**
* GetSubdeviceParent($subdevice_id);
*
* returns the parent device of the $subdevice_id
*
* @param integer subdevice_id
*/
function GetSubdeviceParent($subdevice_id)
{
	return db_fetch_cell("SELECT dev_id FROM sub_devices WHERE id = '$subdevice_id'");
} // end GetSubdeviceParent();


/**
* GetUsername($uid)
*
* returns the username for a uid
*/
function GetUsername($uid)
{
	$sql = "SELECT user FROM user WHERE id='$uid'";
	$handle = db_query($sql);
	$row = db_fetch_array($handle);
	return $row["user"];
} // end GetUsername();

// Recursive Duplication Section

function duplicate_device($dev_id)
{
	// duplicate device
	db_query("CREATE TEMPORARY TABLE tmpdev 
		SELECT id, name, ip, snmp_read_community, dev_type, snmp_recache_method, 
			disabled, snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, 
			snmp_retries, snmp_port, no_snmp_uptime_check
			FROM devices
			WHERE id='$dev_id'");
	db_query("
		INSERT INTO devices
		(name, ip, snmp_read_community, dev_type, snmp_recache_method, disabled,  
			snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, 
			snmp_retries, snmp_port, no_snmp_uptime_check)
		SELECT concat(name, ' (duplicate)'), ip, snmp_read_community, dev_type, snmp_recache_method, 
			disabled, snmp_avoided, snmp_uptime, snmp_ifnumber, snmp_version, snmp_timeout, 
			snmp_retries, snmp_port, no_snmp_uptime_check 
			FROM tmpdev
			WHERE id='$dev_id'");
	$new_dev_id = db_insert_id();
	db_query("DROP TABLE tmpdev");
	
	// duplicate parent associations
	db_query("CREATE TEMPORARY TABLE tmp_dev_parents
		SELECT grp_id, dev_id FROM dev_parents WHERE dev_id=$dev_id");
	db_query("INSERT INTO dev_parents (grp_id, dev_id) 
		SELECT grp_id, $new_dev_id FROM tmp_dev_parents WHERE dev_id=$dev_id");
	db_query("DROP TABLE tmp_dev_parents");
	
	// duplicate view
	db_query("CREATE TEMPORARY TABLE tmp_view
		SELECT object_id, object_type, graph_id, type, pos, separator_text, subdev_id
		FROM view WHERE object_id=$dev_id AND object_type='device'");
	db_query("INSERT INTO view (object_id, object_type, graph_id, type, pos, separator_text, subdev_id)
		SELECT $new_dev_id, object_type, graph_id, type, pos, separator_text, subdev_id
		FROM tmp_view WHERE object_id=$dev_id AND object_type='device'");
	db_query("DROP TABLE tmp_view");
	
	// duplicate subdevices
	$res = db_query("SELECT id FROM sub_devices WHERE dev_id=$dev_id");
	while ($row = db_fetch_array($res))
	{
		duplicate_subdevice($row['id'], $new_dev_id);
	}

} // end duplicate_device();

function duplicate_subdevice($subdev_id, $new_parent = -1)
{
	if ($new_parent == -1)
	{
		$new_parent = "dev_id";
		$name = "concat(name, ' (duplicate)')";
	}
	else
	{
		$name = "name";
	}
	
	// duplicate subdevice
	db_query("CREATE TEMPORARY TABLE tmp_sub_devices SELECT id, dev_id, type, name FROM sub_devices WHERE id=$subdev_id");
	db_query("INSERT INTO sub_devices (dev_id, type, name) SELECT $new_parent, type, $name FROM tmp_sub_devices WHERE id=$subdev_id");
	$new_subdev_id = db_insert_id();
	db_query("DROP TABLE tmp_sub_devices");
	
	// duplicate parameters
	db_query("CREATE TEMPORARY TABLE tmp_sub_dev_variables
		SELECT sub_dev_id, name, value, type FROM sub_dev_variables WHERE sub_dev_id=$subdev_id");
	db_query("INSERT INTO sub_dev_variables (sub_dev_id, name, value, type)
		SELECT $new_subdev_id, name, value, type FROM tmp_sub_dev_variables WHERE sub_dev_id=$subdev_id");
	db_query("DROP TABLE tmp_sub_dev_variables");
	
	if ($new_parent != "dev_id")
	{
		// translate subdevices on device view
		$res = db_query("SELECT id FROM view WHERE object_id=$new_parent AND object_type='device' AND type='template' AND subdev_id=$subdev_id");
		while ($row = db_fetch_array($res))
		{
			db_query("UPDATE view SET subdev_id=$new_subdev_id WHERE id={$row['id']}");
		}
	}
	
	// duplicate monitors
	$res = db_query("SELECT id FROM monitors WHERE sub_dev_id=$subdev_id");
	while ($row = db_fetch_array($res))
	{
		duplicate_monitor($row['id'], $new_subdev_id);
	}
} // end duplicate_subdevice();

function duplicate_monitor($mon_id, $new_parent = "sub_dev_id")
{
	// duplicate monitor
	db_query("CREATE TEMPORARY TABLE tmp_monitors
		SELECT id, sub_dev_id, data_type, min_val, max_val, test_type, test_id, test_params FROM monitors
		WHERE id=$mon_id");
	db_query("INSERT INTO monitors (sub_dev_id, data_type, min_val, max_val, test_type, test_id, test_params)
		SELECT $new_parent, data_type, min_val, max_val, test_type, test_id, test_params FROM tmp_monitors
		WHERE id=$mon_id");
	$new_mon_id = db_insert_id();
	db_query("DROP TABLE tmp_monitors");

	// duplicate events
	$res = db_query("SELECT id FROM events WHERE mon_id=$mon_id");
	while ($row = db_fetch_array($res))
	{
		duplicate_event($row['id'], $new_mon_id);
	}
} // end duplicate_monitor();

function duplicate_event($ev_id, $new_parent = -1)
{
	if ($new_parent == -1)
	{
		$new_parent = "mon_id";
		$name = "concat(name, ' (duplicate)')";
	}
	else
	{
		$name = "name";
	}

	// duplicate event
	db_query("CREATE TEMPORARY TABLE tmp_events
		SELECT id, mon_id, trigger_type, situation, name
		FROM events WHERE id=$ev_id");
	db_query("INSERT INTO events (mon_id, trigger_type, situation, name)
		SELECT $new_parent, trigger_type, situation, $name
		FROM tmp_events WHERE id=$ev_id");
	$new_ev_id = db_insert_id();
	db_query("DROP TABLE tmp_events");
	
	// duplicate conditions
	db_query("CREATE TEMPORARY TABLE tmp_conditions
		SELECT event_id, value, condition, logic_condition, value_type
		FROM conditions WHERE event_id=$ev_id");
	db_query("INSERT INTO conditions (event_id, value, condition, logic_condition, value_type)
		SELECT $new_ev_id, value, condition, logic_condition, value_type
		FROM tmp_conditions WHERE event_id=$ev_id");
	db_query("DROP TABLE tmp_conditions");
	
	// duplicate responses
	$res = db_query("SELECT id FROM responses WHERE event_id = $ev_id");
	while ($row = db_fetch_array($res))
	{
		duplicate_response($row['id'], $new_ev_id);
	}
} // end duplicate_event();

function duplicate_response($rsp_id, $new_parent = "event_id")
{
	db_query("CREATE TEMPORARY TABLE tmp_responses
		SELECT id, event_id, notification_id, parameters FROM responses WHERE id=$rsp_id");
	db_query("INSERT INTO responses (event_id, notification_id, parameters)
		SELECT $new_parent, notification_id, parameters FROM tmp_responses WHERE id=$rsp_id");
	db_query("DROP TABLE tmp_responses");
} // end duplicate_response();

function duplicate_graph($graph_id)
{
	db_query("CREATE TEMPORARY TABLE tmp_graphs
		SELECT id, name, title, comment, width, height, vert_label, type, base, options, max, min FROM graphs WHERE id=$graph_id");
	db_query("INSERT INTO graphs (name, title, comment, width, height, vert_label, type, base, options, max, min) SELECT concat(name, ' (duplicate)'), title, comment, width, height, vert_label, type, base, options, max, min FROM tmp_graphs WHERE id=$graph_id");
	$new_grp_id = db_insert_id();
	db_query("DROP TABLE tmp_graphs");	

	$ds_handle = db_query("SELECT * FROM graph_ds WHERE graph_id='$graph_id'");
	while ($row = db_fetch_array($ds_handle))
	{
		duplicate_graph_item($row['id'], $new_grp_id);
	} 
} // end duplicate_graph

function duplicate_graph_item($item_id, $new_parent = -1)
{
	if ($new_parent == -1)
	{
		$new_parent = "graph_id";
		$label = "concat(label, ' (duplicate)')";
		$move_others = true;
	}
	else
	{
		$label = "label";
		$move_others = false;
	}

	db_query("CREATE TEMPORARY TABLE tmp_graph_ds
		SELECT id, mon_id, color, type, graph_id, label, alignment, stats, position, multiplier, start_time, end_time FROM graph_ds WHERE id=$item_id");
	db_query("INSERT INTO graph_ds (mon_id, color, type, graph_id, label, alignment, stats, position, multiplier, start_time, end_time) SELECT mon_id, color, type, $new_parent, $label, alignment, stats, position, multiplier, start_time, end_time FROM tmp_graph_ds WHERE id=$item_id");
	$new_id = db_insert_id();	
	db_query("DROP TABLE tmp_graph_ds");
	if ($move_others)
	{
		$dq = db_query("SELECT graph_id, position FROM graph_ds WHERE id = $item_id");
		$dr = db_fetch_array($dq);
		db_query("UPDATE graph_ds SET position = position+1 WHERE graph_id = {$dr['graph_id']} AND position > {$dr['position']}");
		db_query("UPDATE graph_ds SET position = position+1 WHERE id = $new_id");
	}
}

function move_graph_item($graph_id, $graph_item_id, $direction)
{
	$query = db_query("
		SELECT id, position
		FROM graph_ds
		WHERE graph_id=$graph_id
		ORDER BY position
		");
	$row = array("id" => 0, "position" => 0);
	for ($ds_count = 0; $ds_count < db_num_rows($query); $ds_count++)
	{
		$last_row = $row;
		$row = db_fetch_array($query);

		if ($direction == "up")
		{
			if ($graph_item_id == $row['id'])
			{
				db_update("UPDATE graph_ds SET position = {$last_row['position']} WHERE id = {$row['id']}");
				db_update("UPDATE graph_ds SET position = {$row['position']} WHERE id = {$last_row['id']}");
				break;
			}
		}
		else
		{
			if ($graph_item_id == $row['id'])
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE graph_ds SET position = {$next_row['position']} WHERE id = {$row['id']}");
				db_update("UPDATE graph_ds SET position = {$row['position']} WHERE id = {$next_row['id']}");
				break;
			}
		}
	}
}

function delete_view_item($item_id)
{
	$q = db_query("SELECT pos, object_id, object_type FROM view WHERE id=" . $item_id);
	$r = db_fetch_array($q);

	$pos = $r["pos"];

	db_update("DELETE FROM view WHERE id=" . $item_id);

	db_update("UPDATE view SET pos = pos - 1
		WHERE object_id=" . $r["object_id"] . "
		AND object_type='" . $r["object_type"] . "'
		AND pos > " . $pos);
}

function is_view_item_extreme($object_id, $object_type, $item_id, $which)
{
	$query = db_query("
	SELECT 	id, pos
	FROM 	view
	WHERE 	object_id=$object_id
	AND 	object_type='$object_type'
	ORDER BY pos $which");

	$row = db_fetch_array($query);

	return ($row['id'] == $item_id);
}

function is_view_item_top($object_id, $object_type, $item_id)
{
	return is_view_item_extreme($object_id, $object_type, $item_id, "ASC");
}

function is_view_item_bottom($object_id, $object_type, $item_id)
{
	return is_view_item_extreme($object_id, $object_type, $item_id, "DESC");
}

function move_view_item($object_id, $object_type, $item_id, $direction)
{
	//echo ("$object_id, $object_type, $item_id, $direction");
	$query = db_query("
		SELECT 	id, pos
		FROM 	view
		WHERE 	object_id=$object_id
		AND 	object_type='$object_type'
		ORDER BY pos");
	
	$row = array("id" => 0, "pos" => 0);
	
	for ($i = 0; $i < db_num_rows($query); $i++)
	{
		$last_row = $row;
		$row = db_fetch_array($query);

		if ($direction == "up")
		{
			if ($item_id == $row['id'])
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE view SET pos = {$last_row['pos']} WHERE object_id = $object_id AND object_type = '$object_type' AND id = {$row['id']}");
				db_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = $object_id AND object_type = '$object_type' AND id = {$last_row['id']}");
				break;
			}
		}
		else
		{
			if ($item_id == $row['id'])
			{
				$next_row = db_fetch_array($query);
				db_update("UPDATE view SET pos = {$next_row['pos']} WHERE object_id = $object_id AND object_type = '$object_type' AND id = {$row['id']}");
				db_update("UPDATE view SET pos = {$row['pos']} WHERE object_id = $object_id AND object_type = '$object_type' AND id = {$next_row['id']}");
				break;
			}
		}
	}
}

function move_view_item_top($object_id, $object_type, $item_id)
{
	while (!is_view_item_top($object_id, $object_type, $item_id))
		move_view_item($object_id, $object_type, $item_id, "up");
}

function move_view_item_bottom($object_id, $object_type, $item_id)
{
	while (!is_view_item_bottom($object_id, $object_type, $item_id))
		move_view_item($object_id, $object_type, $item_id, "down");
}

// Recursive Deletion Section (for orphan prevention if nothing else)

function delete_group($group_id)
{
	// get group info
	$grp_info_handle = db_query("SELECT * FROM groups WHERE id='$group_id'");
	$grp_info = db_fetch_array($grp_info_handle);
	
	// reparent children groups
	db_query("UPDATE groups SET parent_id = '{$grp_info['parent_id']}' WHERE parent_id = '$group_id'");
	
	// delete the group
	db_query("DELETE FROM groups WHERE id='$group_id'");

	// delete the associated graphs
	db_query("DELETE FROM view WHERE object_type='group' AND object_id='$group_id'");

	// get devices in this group
	$devs_in_grp_handle = db_query("SELECT dev_id FROM dev_parents WHERE grp_id='$group_id'");
	$devs_in_grp = array();
	while ($r = db_fetch_array($devs_in_grp_handle))
	{
		array_push($devs_in_grp, $r["dev_id"]);
	} // end while devices in this group

	// delete devices from this group
	db_query("DELETE FROM dev_parents WHERE grp_id = '$group_id'");

	// for each device we had, if it no longer has parents, delete it
	foreach ($devs_in_grp as $device_id)
	{
		$dev_res = db_query("SELECT 1 FROM dev_parents WHERE dev_id = '$device_id'");
		if (db_num_rows($dev_res) == 0)
		{
			delete_device($device_id);
		} // end delete if this device has no parents left
	} // end foreach device we had

} // end delete_group();


function delete_device($device_id, $group_id = false)
{
	if ($group_id !== false)
	{
		// 'unparent' the device
		db_update("DELETE FROM dev_parents WHERE dev_id = '$device_id' AND grp_id = '$group_id'");

		/** If this device is not part of any groups anymore, finish deleting it **/
		$dev_parent_res = db_query("SELECT count(*) AS count FROM dev_parents WHERE dev_id = '$device_id'");
		$dev_parent_row = db_fetch_array($dev_parent_res);
	}
	else
	{
		$dev_parent_row["count"] = 0;
	}

	if ($dev_parent_row["count"] == 0)
	{
		// delete the device
		db_update("DELETE FROM devices WHERE id = '$device_id'");

		// remove the interface for the device
		db_update("DELETE FROM snmp_interface_cache WHERE dev_id = '$device_id'");

		// remove the disk cache for the device
		db_update("DELETE FROM snmp_disk_cache WHERE dev_id = '$device_id'");

		// remove the device properties
		db_update("DELETE FROM dev_prop_vals WHERE dev_id = '$device_id'");

		// remove associated graphs
		db_update("DELETE FROM view WHERE object_type='device' AND object_id = '$device_id'");

		$subdev_handle = db_query("SELECT id FROM sub_devices WHERE dev_id = '$device_id'");
		
		while ($subdev_row = db_fetch_array($subdev_handle))
		{
			delete_subdevice($subdev_row["id"]);
		}
	} // end if no parents left for device
} // end delete_device();


function delete_subdevice($subdev_id)
{
	// delete the subdevice
	db_update("DELETE FROM sub_devices WHERE id='$subdev_id'");
	
	// delete the subdevice parameters
	db_update("DELETE FROM sub_dev_variables WHERE sub_dev_id='$subdev_id'");
	
	$monitors_handle = db_query("SELECT id FROM monitors WHERE sub_dev_id='$subdev_id'");
	
	while ($monitor_row = db_fetch_array($monitors_handle))
	{
		delete_monitor($monitor_row["id"]);
	}

	$view_handle = db_query("SELECT id FROM view WHERE type='template' AND subdev_id=$subdev_id");
	
	while ($view_row = db_fetch_array($view_handle))
	{
		delete_view_item($view_row['id']);
	}

} // end delete_subdevice();


function delete_monitor($monitor_id)
{
	// check things that depend on this
	// * custom graphs
	// * template graphs
	db_update("UPDATE graph_ds SET mon_id = '-1', type = '4', multiplier = '0' WHERE mon_id = '$monitor_id'");
	db_update("DELETE FROM monitors WHERE id='$monitor_id'");
	
	$events_handle = db_query("SELECT id FROM events WHERE mon_id='$monitor_id'");
	while ($event_row = db_fetch_array($events_handle))
	{
		delete_event($event_row["id"]);
	} // end for each row
} // end delete_monitor()


function delete_event($event_id)
{

	db_update("DELETE FROM events WHERE id='$event_id'");
	db_update("DELETE FROM conditions WHERE event_id='$event_id'");

	$responses_handle = db_query("SELECT id FROM responses WHERE event_id='$event_id'");

	while ($response_row = db_fetch_array($responses_handle))
	{
		delete_response($response_row["id"]);
	}
} // end delete_event();


function delete_response($response_id)
{
	db_update("DELETE FROM responses WHERE id=$response_id");
}


function delete_graph($graph_id)
{
	// delete the graph
	db_update("DELETE FROM graphs WHERE id = $graph_id");

	// delete the graphs from associated graphs
	db_update("DELETE FROM view WHERE graph_id = $graph_id AND (type = 'graph' OR type = 'template')");

	$ds_handle = db_query("SELECT id FROM graph_ds WHERE graph_id=$graph_id");

	while ($ds_row = db_fetch_array($ds_handle))
	{
		delete_ds($ds_row["id"]);
	}
} // end delete_graph();


function delete_ds($ds_id)
{
	$q = db_query("SELECT graph_id, position FROM graph_ds WHERE id=$ds_id");
	$r = db_fetch_array($q);
	
	db_update("DELETE FROM graph_ds WHERE id=$ds_id");
	db_update("UPDATE graph_ds SET position = position - 1 WHERE graph_id = {$r['graph_id']} AND position > {$r['position']}");
} // end delete_ds();


// Unified update/insert code


// Generic

function generic_insert($sql)
{
	db_update("INSERT INTO $sql");
}

function generic_update($sql, $id)
{
	db_update("UPDATE $sql WHERE id='$id'");
}


// Group

function sql_group($grp_name, $grp_comment, $parent_id)
{
	return "groups SET name='$grp_name', comment='$grp_comment', parent_id='$parent_id'";
}


function create_group($grp_name, $grp_comment, $parent_id)
{
	generic_insert(sql_group($grp_name, $grp_comment, $parent_id));
}


function update_group($id, $grp_name, $grp_comment, $parent_id)
{
	generic_update(sql_group($grp_name, $grp_comment, $parent_id), $id);
}


/**
* addslashes_deep($value)
*
* adds slashes to all values inside $value
*/
function addslashes_deep($value)
{
	return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
} // end addslashes_deep();


/**
* CreateLocalMenu():
*
* creates a local version of the menu w/ only the user's authorized items
*/
function CreateLocalMenu()
{
	global $MENU, $LOCAL_MENU, $LOCAL_MENU_CURTREE, $LOCAL_MENU_CURITEM;
	
	while (list($menuname, $menuitems) = each($MENU))
	{
		// foreach menu item
		$authorized_subitems = array();
		foreach ($menuitems as $menuitem)
		{
			if (basename($_SERVER["SCRIPT_NAME"]) == $menuitem["link"])
			{
				$LOCAL_MENU_CURTREE = $menuname;
				$LOCAL_MENU_CURITEM = $menuitem["link"];
			} // end if we're in this group, display its menu items
			
			if ($_SESSION["netmrgsess"]["permit"] >= $menuitem["authLevelRequired"]
				&& $menuitem["display"] !== false)
			{
				array_push($authorized_subitems, $menuitem);
			} // end if we have enough permissions to view this subitem
		} // end foreach menu item
		
		// if we had some item output (ie, we had auth to view at least ONE item in this submenu)
		// and we're under this current menu heading
		if (count($authorized_subitems))
		{
			$LOCAL_MENU[$menuname] = $authorized_subitems;
		} // end if item output wasn't empty
	} // end while we still have menu items
} // end CreateLocalMenu();


/**
* GetUserPref($module, $pref)
*
* returns the value for the $module and $pref wanted for user $uid
*/
function GetUserPref($uid, $module, $pref)
{
	$sql = "SELECT user_prefs.value
		FROM user_prefs
		WHERE user_prefs.uid = '$uid'
		AND user_prefs.module = '$module' AND user_prefs.pref = '$pref'";
	$handle = db_query($sql);
	if (db_num_rows($handle) > 0)
	{
		$row = db_fetch_array($handle);
		return $row["value"];
	} // end if a result
	return "";
} // end GetUserPref();


/**
* SetUserPref($uid, $module, $pref, $value)
*
* sets the $value for the $module and $pref for user $uid
*/
function SetUserPref($uid, $module, $pref, $value)
{
	$sql = "SELECT user_prefs.id
		FROM user_prefs
		WHERE user_prefs.uid = '$uid'
		AND user_prefs.module = '$module' AND user_prefs.pref = '$pref'";
	$handle = db_query($sql);
	if (db_num_rows($handle) > 0)
	{
		$row = db_fetch_array($handle);
		$update_query = "UPDATE user_prefs SET value = '$value' WHERE id = '{$row['id']}'";
	} // end if a result
	else
	{
		$update_query = "INSERT INTO user_prefs
			SET uid = '$uid', module = '$module', pref = '$pref', value = '$value'";
	} // end no result
	db_query($update_query);
} // end SetUserPref();


/**
* GetDBVersion()
*
* returns the version the database thinks we are
*/
function GetDBVersion()
{
	$sql = "SELECT version FROM versioninfo WHERE module='Main'";
	$handle = db_query($sql);
	$row = db_fetch_array($handle);
	return $row["version"];
} // end GetDBVersion();


/**
* UpdaterNeedsRun()
*
* returns true if the updater needs run
*/
function UpdaterNeedsRun()
{
	return ($GLOBALS["netmrg"]["verhist"][$GLOBALS["netmrg"]["version"]] > $GLOBALS["netmrg"]["verhist"][$GLOBALS["netmrg"]["dbversion"]]);
} // end UpdaterNeedsRun();


/**
* UpdateDBVersion($ver)
*
* updates the version the database is in
*/
function UpdateDBVersion($ver)
{
	$sql = "UPDATE versioninfo SET version='$ver' WHERE module='Main'";
	$handle = db_query($sql);
} // end UpdateDBVersion();


/**
* GetXMLConfig()
*
* reads xml config file and puts values in config array
*/
function GetXMLConfig()
{
	$xmlconfig = GetXMLTree($GLOBALS["netmrg"]["xmlfile"]);

	// cosmetic variables
	$GLOBALS["netmrg"]["company"]      = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANY"][0]["VALUE"];
	$GLOBALS["netmrg"]["companylink"]  = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANYLINK"][0]["VALUE"];
	$GLOBALS["netmrg"]["webhost"]      = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBHOST"][0]["VALUE"];
	$GLOBALS["netmrg"]["webroot"]      = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBROOT"][0]["VALUE"];
	if (!isset($xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"]))
	{
		$xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"][0]["VALUE"] = false;
	} // end set default for external auth
	if ($xmlconfig["NETMRG"][0]["WEBSITE"][0]["EXTERNALAUTH"][0]["VALUE"] == "true")
	{
		$GLOBALS["netmrg"]["externalAuth"] = true;
	} // end if true
	else
	{
		$GLOBALS["netmrg"]["externalAuth"] = false;
	} // end else false


	// DB Config
	$GLOBALS["netmrg"]["dbhost"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["HOST"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbname"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["DB"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbuser"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["USER"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbpass"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["PASSWORD"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbsock"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["SOCKET"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbport"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["PORT"][0]["VALUE"];

	// Path Config
	$GLOBALS["netmrg"]["rrdtool"]     = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDTOOL"][0]["VALUE"];
	$GLOBALS["netmrg"]["rrdroot"]     = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDS"][0]["VALUE"];
	$GLOBALS["netmrg"]["fileroot"]    = $xmlconfig["NETMRG"][0]["PATHS"][0]["WEBFILEROOT"][0]["VALUE"];
	$GLOBALS["netmrg"]["locale"]      = $xmlconfig["NETMRG"][0]["PATHS"][0]["LOCALE"][0]["VALUE"];

	// RRDTool Config
	$GLOBALS["netmrg"]["rrdtool_version"] = $xmlconfig["NETMRG"][0]["RRDTOOL"][0]["VERSION"][0]["VALUE"];
	if (empty($GLOBALS["netmrg"]["rrdtool_version"])) { $GLOBALS["netmrg"]["rrdtool_version"] = "1.0"; }

} // end GetXMLConfig();


/**
* PrereqsMet()
* 
* checks if the prerequisits for running NetMRG are met
*
* @returns array of errors
*/
function PrereqsMet()
{
	/**
	PHP >= v4.1.0
	PHP Safe Mode == off
	RRDtool is executable
	netmrg-gatherer is executable
	*/
	
	$errors = array();
	
	// PHP >= 4.1.0
	$phpver = explode(".", phpversion());
	if ($phpver[0] < 4 && $phpver[1] < 1 && $phpver[2] < 0)
	{
		array_push($errors, "PHP Version 4.1.0 or higher required");
	} // end if version less than 4.1.0
	
	// PHP Safe Mode == off
	if (ini_get("safe_mode"))
	{
		array_push($errors, "PHP Safe Mode not supported");
	} // end if safe mode enabled
	
	// RRDtool is executable
	if (!is_executable($GLOBALS["netmrg"]["rrdtool"]))
	{
		array_push($errors, "RRD Tool not found or is not executable");
	} // end if rrdtool not executable
	
	// netmrg-gatherer is executable
	if (!is_executable($GLOBALS["netmrg"]["binary"]))
	{
		array_push($errors, "NetMRG Gatherer not found or not executable");
	} // end if gatherer not executable
	
	return $errors;
} // end PrereqsMet();
?>
