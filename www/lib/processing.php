<?php
/********************************************
* NetMRG Integrator
*
* processing.php
* Internal Processing Functions
*
* see doc/LICENSE for copyright information
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


function sanitize_number($number, $round_to = 5)
{
	
	if ($number < 1000)
	{
		return round($number,$round_to);
	}
	elseif ($number < 1000000)
	{
		return round(($number / 1000),$round_to) . " k";
	}
	elseif ($number < 1000000000)
	{
		return round(($number / 1000000),$round_to) . " M";
	}
	elseif ($number < 1000000000000)
	{
		return round(($number / 1000000000),$round_to) . " G";
	}
	else
	{
		return round(($number / 1000000000000),$round_to) . " T";
	}

} // end sanitize_number


function make_spaces($length)
{
	$spaces = "";

	for ($i = 0; $i < $length; $i++)
	{
		$spaces = $spaces . " ";
	}

	return($spaces);
} // end make_spaces


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
	
	return (":\"" . $string . "\"");
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
	if (!preg_match("/[^012345467890.\/*-+]/", $input))
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
	$q = db_query("SELECT data_type, test_id, test_type, test_params FROM graph_ds, monitors WHERE graph_ds.graph_id=$template_id AND graph_ds.mon_id=monitors.id");	
	for ($i = 0; $i < db_num_rows($q); $i++)
	{
		$row = db_fetch_array($q);
		db_update("INSERT INTO monitors SET sub_dev_id=$subdev_id, data_type={$row['data_type']}, test_id={$row['test_id']}, test_type={$row['test_type']}, test_params='{$row['test_params']}'");
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
	GLOBAL $TEST_TYPES;

	$mon_query = db_query("
		SELECT	test_id, test_params, test_type
		FROM	monitors
		WHERE	monitors.id = $mon_id");
	$mon_row = db_fetch_array($mon_query);

	switch($mon_row["test_type"])
	{
		case 1:
			$test_query = "SELECT name FROM tests_script   WHERE id = " . $mon_row["test_id"];
			break;
		case 2:
			$test_query = "SELECT name FROM tests_snmp     WHERE id = " . $mon_row["test_id"];
			break;
		case 3:
			$test_query = "SELECT name FROM tests_sql      WHERE id = " . $mon_row["test_id"];
			break;
		case 4:
			$test_query = "SELECT name FROM tests_internal WHERE id = " . $mon_row["test_id"];
			break;

	} // end switch test type

	$test_row = db_fetch_array(db_query($test_query));

	$res = $test_row["name"];

	if ($mon_row["test_params"] != "")
	{
		$res .= " - " . $mon_row["test_params"];
	}

	return $res;
} // end get_short_monitor_name()


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
* GetDeviceGroups($device_id);
*
* returns the groups that this device is in as an array
*
*/
function GetDeviceGroups($device_id)
{
	$db_result = db_query("
		SELECT groups.id AS group_id 
		FROM groups, dev_parents, devices 
		WHERE devices.id = '$device_id' 
		AND devices.id = dev_parents.dev_id
		AND dev_parents.grp_id = groups.id
		GROUP BY group_id");

	$group_arr = array();
	while ($r = mysql_fetch_array($db_result))
	{
		array_push($group_arr, $r["group_id"]);
	} // end while we have results

	return $group_arr;
} // end GetDeviceGroups();


/**
* GetSubdeviceGroups($subdevice_id);
*
* returns the groups that this subdevice is in as an array
*
*/
function GetSubdeviceGroups($subdevice_id)
{
	$db_result = db_query("
		SELECT groups.id AS group_id 
		FROM groups, dev_parents, devices, sub_devices 
		WHERE sub_devices.id = '$subdevice_id' 
		AND sub_devices.dev_id = devices.id
		AND devices.id = dev_parents.dev_id
		AND dev_parents.grp_id = groups.id
		GROUP BY group_id");

	$group_arr = array();
	while ($r = mysql_fetch_array($db_result))
	{
		array_push($group_arr, $r["group_id"]);
	} // end while we have results

	return $group_arr;
} // end GetSubdeviceGroups();


/**
* GetMonitorGroups($monitor_id);
*
* returns the groups that this monitor is in as an array
*
*/
function GetMonitorGroups($monitor_id)
{
	$db_result = db_query("
		SELECT groups.id AS group_id 
		FROM groups, dev_parents, devices, sub_devices, monitors 
		WHERE monitors.id = '$monitor_id'
		AND sub_devices.id = monitors.sub_dev_id
		AND sub_devices.dev_id = devices.id
		AND devices.id = dev_parents.dev_id
		AND dev_parents.grp_id = groups.id
		GROUP BY group_id");

	$group_arr = array();
	while ($r = mysql_fetch_array($db_result))
	{
		array_push($group_arr, $r["group_id"]);
	} // end while we have results

	return $group_arr;
} // end GetMonitorGroups();


/**
* GetCustomGraphGroups($customgraph_id);
*
* returns the groups that this custom graph is in as an array
*
*/
function GetCustomGraphGroups($customgraph_id)
{
	$db_result = db_query("
		SELECT groups.id AS group_id 
		FROM groups, dev_parents, devices, sub_devices, monitors, graph_ds 
		WHERE graph_ds.graph_id = '$customgraph_id'
		AND graph_ds.mon_id = monitors.id
		AND sub_devices.id = monitors.sub_dev_id
		AND sub_devices.dev_id = devices.id
		AND devices.id = dev_parents.dev_id
		AND dev_parents.grp_id = groups.id
		GROUP BY group_id");

	$group_arr = array();
	while ($r = mysql_fetch_array($db_result))
	{
		array_push($group_arr, $r["group_id"]);
	} // end while we have results

	return $group_arr;
} // end GetCustomGraphGroups();


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



// Recursive Deletion Section (for orphan prevention if nothing else)

function delete_group($group_id)
{

	// delete the graph
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


function delete_device($device_id)
{
	// delete the device
	db_update("DELETE FROM devices WHERE id=$device_id");

	// remove the interface for the device
	db_update("DELETE FROM snmp_interface_cache WHERE dev_id=$device_id");

	// remove the disk cache for the device
	db_update("DELETE FROM snmp_disk_cache WHERE dev_id=$device_id");

	// remove associated graphs
	db_update("DELETE FROM view WHERE object_type='device' AND object_id=$device_id");

	// remove group associations
	db_update("DELETE FROM dev_parents WHERE dev_id=$device_id");

	$subdev_handle = db_query("SELECT id FROM sub_devices WHERE dev_id=$device_id");

	while ($subdev_row = db_fetch_array($subdev_handle))
	{
		delete_subdevice($subdev_row["id"]);
	}
} // end delete_device();


function delete_subdevice($subdev_id)
{
	// delete the subdevice
	db_update("DELETE FROM sub_devices WHERE id=$subdev_id");

	// delete the subdevice parameters
	db_update("DELETE FROM sub_dev_variables WHERE sub_dev_id=$subdev_id");

	$monitors_handle = db_query("SELECT id FROM monitors WHERE sub_dev_id=$subdev_id");

	while ($monitor_row = db_fetch_array($monitors_handle))
	{
		delete_monitor($monitor_row["id"]);
	}
} // end delete_subdevice();


function delete_monitor($monitor_id)
{
	db_update("DELETE FROM monitors WHERE id=$monitor_id");

	$events_handle = db_query("SELECT id FROM events WHERE mon_id=$monitor_id");
	while ($event_row = db_fetch_array($events_handle))
	{
		delete_event($event_row["id"]);
	} // end for each row
} // end delete_monitor()


function delete_event($event_id)
{

	db_update("DELETE FROM events WHERE id=$event_id");
	db_update("DELETE FROM conditions WHERE event_id=$event_id");

	$responses_handle = db_query("SELECT id FROM responses WHERE event_id=$event_id");

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
	db_update("UPDATE $sql WHERE id=$id");
}


// Group

function sql_group($grp_name, $grp_comment, $parent_id)
{
	$grp_name = db_escape_string($grp_name);
	$grp_comment = db_escape_string($grp_comment);
	return "groups SET name=\"$grp_name\", comment=\"$grp_comment\", parent_id=$parent_id";
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
* GetXMLConfig()
*
* reads xml config file and puts values in config array
*/
function GetXMLConfig()
{
	$xmlconfig = GetXMLTree($GLOBALS["netmrg"]["xmlfile"]);

	// cosmetic variables
	$GLOBALS["netmrg"]["company"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANY"][0]["VALUE"];
	$GLOBALS["netmrg"]["companylink"] = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["COMPANYLINK"][0]["VALUE"];
	$GLOBALS["netmrg"]["webhost"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBHOST"][0]["VALUE"];
	$GLOBALS["netmrg"]["webroot"]     = $xmlconfig["NETMRG"][0]["WEBSITE"][0]["WEBROOT"][0]["VALUE"];

	// DB Config
	$GLOBALS["netmrg"]["dbhost"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["HOST"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbname"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["DB"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbuser"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["USER"][0]["VALUE"];
	$GLOBALS["netmrg"]["dbpass"]      = $xmlconfig["NETMRG"][0]["DATABASE"][0]["PASSWORD"][0]["VALUE"];

	// Path Config
	$GLOBALS["netmrg"]["rrdtool"]        = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDTOOL"][0]["VALUE"];
	$GLOBALS["netmrg"]["rrdroot"]        = $xmlconfig["NETMRG"][0]["PATHS"][0]["RRDS"][0]["VALUE"];
	$GLOBALS["netmrg"]["fileroot"]       = $xmlconfig["NETMRG"][0]["PATHS"][0]["WEBFILEROOT"][0]["VALUE"];

} // end GetXMLConfig();


?>
