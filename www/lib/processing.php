<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Internal Processing Functions              #
#           processing.php                             #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");

// Simple Formatting Section

function format_time_elapsed($num_secs) {
# Makes a string from a 'seconds elapsed' integer
	$the_secs = $num_secs;
	$new_secs = $num_secs % 86400;
	$days = ($num_secs - $new_secs) / 86400;
	if ($days > 10000) {
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

        	$res .= sprintf("%02d:%02d:%02d",$hours,$mins,$new_secs);
	} else {
		$res .= "Unavailable";
	}

	return $res;

} # end format_time_elapsed


function sanitize_number($number)
{
$round_to = 5;

	if ($number < 1000)
	{
		return round($number,$round_to);
	} elseif ($number < 1000000)
	{
		return round(($number / 1000),$round_to) . " k";
	} elseif ($number < 1000000000)
	{
		return round(($number / 1000000),$round_to) . " M";
	} elseif ($number < 1000000000000) {
		return round(($number / 1000000000),$round_to) . " G";
	} else {
		return round(($number / 1000000000000),$round_to) . " T";
	}


} # end sanitize_number

function make_spaces($length) {
	$spaces = "";

	for ($i = 0; $i < $length; $i++) {
	$spaces = $spaces . " ";
	}
	return($spaces);
} // end make_spaces

function align_right($string, $length) 
{
// prepends spaces to a string to cause it to be a certain length
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
	$pos = strrchr($string," ");
	return(substr($string, 0, -strlen($pos)) . make_spaces($space_length) . $pos);
} //end align_right_split

function do_align($string, $length, $method) 
{
// manipulates a string by applying the appropriate padding method

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
        }

        return($result);

} // end do_align

function get_microtime()
{
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

// Recursive status determination section

function get_monitor_status($mon_id)
{
// Takes a monitor ID and determines the monitor's status based on
// the individual status of each event subordinate to the monitor.

	if (isset($GLOBALS["state_monitor_" . $mon_id]))
	{
		return $GLOBALS["state_monitor_" . $mon_id];
	} else {

		$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id");
		$event_total = mysql_num_rows($event_results);

		$status = -1;

		for ($event_count = 1; $event_count <= $event_total; ++$event_count)
		{
		# For each event
			$event_row = mysql_fetch_array($event_results);
			$event_id = $event_row["id"];
			$ev_status = $event_row["situation"];
			if ($event_row["last_status"] == 1)
			{
				if ($status < $ev_status) { $status = $ev_status; }
			}
		} # end event for

	$GLOBALS["state_monitor_" . $mon_id] = $status;
	return $status;
	}

} # end get_monitor_status


function get_device_status($dev_id) {
# Takes a dev_id and returns the current device aggregate status

        if (isset($GLOBALS["state_device_" . $dev_id]))
        {
	        return $GLOBALS["state_device_" . $dev_id];

        } else {

                $status = -1;

		$dev_results = do_query("SELECT disabled FROM mon_devices WHERE id=$dev_id");
		$dev_row     = mysql_fetch_array($dev_results);

		if ($dev_row["disabled"] == 1)
		{
		        $status = 4;
                } else {

		        $mon_results = do_query("SELECT mon_monitors.id FROM mon_monitors WHERE device_id=$dev_id");
                        $mon_total = mysql_num_rows($mon_results);

                        for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count)
		        {
        	                $mon_row = mysql_fetch_array($mon_results);
	                        $mon_status = get_monitor_status($mon_row["id"]);
	                        if ($mon_status > $status) { $status = $mon_status; }
	                } # end for

                }

		$GLOBALS["state_device_" . $dev_id] = $status;
	        return $status;
	}

} # end get_device_status

function get_group_status($grp_id)
{
# Takes a grp_id and returns the current group aggregate status

        if (isset($GLOBALS["state_group_" . $grp_id]))
        {
	        return $GLOBALS["state_group_" . $grp_id];
        } else {

        	$status = -1;

                $grp_results = do_query("SELECT id FROM mon_groups WHERE parent_id=$grp_id");
                $grp_total = mysql_num_rows($grp_results);

                for ($grp_count = 1; $grp_count <= $grp_total; $grp_count++)
                {
                        $grp_row = mysql_fetch_array($grp_results);
        	        $grp_status = get_group_status($grp_row["id"]);
        	        if (($grp_status > $status) && ($dev_status != 4)) { $status = $grp_status; }
                }


                $dev_results = do_query("SELECT dev_id FROM dev_parents WHERE grp_id=$grp_id");
                $dev_total = mysql_num_rows($dev_results);

                for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count)
                {
                # For each device

	                $dev_row = mysql_fetch_array($dev_results);
        	        $dev_status = get_device_status($dev_row["dev_id"]);
	                if (($dev_status > $status) && ($dev_status != 4)) { $status = $dev_status; }
                } # end for

		$GLOBALS["state_group_" . $grp_id] = $status;
                return $status;
        }

} // end get_group_status


// Uniform Name Creation Section


function get_short_monitor_name($mon_id)
{

	$mon_query = do_query("
		SELECT test_type, test_id, test_params, test_types.name AS test_name
		FROM monitors
		LEFT JOIN test_types ON monitors.test_type=test_types.id
		WHERE monitors.id = $mon_id");
	$mon_row = mysql_fetch_array($mon_query);

	switch($mon_row["test_type"])
	{
		case 1:
			$test_query = "SELECT name FROM tests_script WHERE id = " . $mon_row["test_id"];
			break;
		case 2:
			$test_query = "SELECT name FROM tests_snmp   WHERE id = " . $mon_row["test_id"];
			break;
		case 3:
			$test_query = "SELECT name FROM tests_sql    WHERE id = " . $mon_row["test_id"];
			break;
	} // end switch test type

	$test_row = mysql_fetch_array(do_query($test_query));

	return $test_row["name"];
} // end get_short_monitor_name()


function get_monitor_name($mon_id)
{

        $query_handle = do_query("      SELECT  mon_devices.name AS dev_name,
                                                sub_devices.name AS sub_name
                                        FROM monitors

                                        LEFT JOIN sub_devices ON monitors.sub_dev_id=sub_devices.id
                                        LEFT JOIN mon_devices ON sub_devices.dev_id=mon_devices.id

                                        WHERE monitors.id=$mon_id");

        $row = mysql_fetch_array($query_handle);

        return $row["dev_name"] . " - " . $row["sub_name"] . " (" . get_short_monitor_name($mon_id) . ")";

}


function get_graph_name($graph_id)
{
        $graph_query = do_query("SELECT name FROM graphs WHERE id=$graph_id");
	$graph_row   = mysql_fetch_array($graph_query);
	return $graph_row["name"];
}

function get_device_name($dev_id)
{
	$dev_query = do_query("SELECT name FROM mon_devices WHERE id=$dev_id");
	$dev_row   = mysql_fetch_array($dev_query);
	return $dev_row["name"];
}

function get_sub_device_name($sub_dev_id)
{
        $dev_query = do_query(" SELECT mon_devices.name AS dev_name, sub_devices.name AS sub_name
	                        FROM sub_devices
                                LEFT JOIN mon_devices   ON sub_devices.dev_id=mon_devices.id
				WHERE sub_devices.id = $sub_dev_id");
        $row = mysql_fetch_array($dev_query);
	return $row["dev_name"] . " - " . $row["sub_name"];
}


// Recursive Deletion Section (for orphan prevention if nothing else)


function delete_group($group_id)
{

	// delete the graph
	do_update("DELETE FROM mon_groups WHERE id=$group_id");

	// delete the associated graphs
	do_update("DELETE FROM view WHERE pos_id_type=0 AND pos_id=$group_id");

	$devices_handle = do_query("SELECT id FROM mon_devices WHERE group_id=$group_id");

	for ($i = 0; $i < mysql_num_rows($devices_handle); $i++) {
	        $device_row = mysql_fetch_array($devices_handle);
	        delete_device($device_row["id"]);
	}
}

function delete_device($device_id)
{

        // delete the device
	do_update("DELETE FROM mon_devices WHERE id=$device_id");

	// remove the snmp-cache for the device
	do_update("DELETE FROM snmp_cache WHERE dev_id=$device_id");

	// remove the disk-cache for the device
	do_update("DELETE FROM snmp_disk_cache WHERE dev_id=$device_id");

	// remove associated graphs
	do_update("DELETE FROM view WHERE pos_id_type=1 AND pos_id=$device_id");

	// remove group associations
	do_update("DELETE FROM dev_parents WHERE dev_id=$device_id");


	$subdev_handle = do_query("SELECT id FROM sub_devices WHERE dev_id=$device_id");

	for ($i = 0; $i < mysql_num_rows($subdev_handle); $i++)
	{
	        $subdev_row = mysql_fetch_array($subdev_handle);
	        delete_subdevice($subdev_row["id"]);
	}
}

function delete_subdevice($subdev_id)
{
	// delete the subdevice
	do_update("DELETE FROM sub_devices WHERE id=$subdev_id");

	// delete the subdevice parameters
	do_update("DELETE FROM sub_dev_variables WHERE sub_dev_id=$subdev_id");

	$monitors_handle = do_query("SELECT id FROM monitors WHERE sub_dev_id=$subdev_id");

	for ($i = 0; $i < mysql_num_rows($monitors_handle); $i++)
	{
		$monitor_row = mysql_fetch_array($monitors_handle);
		delete_monitor($monitor_row["id"]);
	}

}

function delete_monitor($monitor_id)
{
	do_update("DELETE FROM monitors WHERE id=$monitor_id");

	$events_handle = do_query("SELECT id FROM mon_events WHERE monitors_id=$monitor_id");
	for ($i = 0; $i < mysql_num_rows($events_handle); $i++)
	{
		$event_row = mysql_fetch_array($events_handle);
		delete_event($event_row["id"]);
	} // end for each row
} // end delete_monitor()

function delete_event($event_id)
{

	do_update("DELETE FROM mon_events WHERE id=$event_id");

        $responses_handle = do_query("SELECT id FROM mon_responses WHERE events_id=$event_id");

	for ($i = 0; $i < mysql_num_rows($responses_handle); $i++)
	{
	        $response_row = mysql_fetch_array($responses_handle);
	        delete_response($response_row["id"]);
        }

}

function delete_response($response_id)
{

        do_update("DELETE FROM mon_responses WHERE id=$response_id");

}

function delete_graph($graph_id)
{

	// delete the graph
	do_update("DELETE FROM graphs WHERE id=$graph_id");

	// delete the graphs from associated graphs
	do_update("DELETE FROM view WHERE graph_id_type='custom' AND graph_id=$graph_id");

        $ds_handle = do_query("SELECT id FROM graph_ds WHERE graph_id=$graph_id");

	for ($i = 0; $i < mysql_num_rows($ds_handle); $i++)
	{
	        $ds_row = mysql_fetch_array($ds_handle);
	        delete_ds($ds_row["id"]);
        }

}

function delete_ds($ds_id)
{
        do_update("DELETE FROM graph_ds WHERE id=$ds_id");
}


// Unified update/insert code


// Generic

function generic_insert($sql)
{

        do_update("INSERT INTO $sql");

}

function generic_update($sql, $id)
{

        do_update("UPDATE $sql WHERE id=$id");

}


// Group

function sql_group($grp_name, $grp_comment, $parent_id)
{

        return "mon_groups SET name=\"$grp_name\", comment=\"$grp_comment\", parent_id=$parent_id";

}

function create_group($grp_name, $grp_comment, $parent_id)
{

        generic_insert(sql_group($grp_name, $grp_comment, $parent_id));

}

function update_group($id, $grp_name, $grp_comment, $parent_id)
{

	generic_update(sql_group($grp_name, $grp_comment, $parent_id), $id);

}

// Graph Items

function sql_graph_item($src_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier, $position)
{

        return "graph_ds SET src_id=$src_id, color=\"$color\", type=$type, label=\"$label\", align=$align, graph_id=$graph_id, show_stats=$show_stats, show_indicator=$show_indicator, hrule_value=\"$hrule_value\", hrule_color=\"$hrule_color\", hrule_label=\"$hrule_label\", show_inverted=$show_inverted, alt_graph_id=$alt_graph_id, use_alt=$use_alt, multiplier=$multiplier, position=$position";

}

function create_graph_item($src_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier, $position)
{

        generic_insert(sql_graph_item($src_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier, $position));
}

function update_graph_item($id, $src_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier, $position)
{

        generic_update(sql_graph_item($src_id, $color, $type, $label, $align, $graph_id, $show_stats, $show_indicator, $hrule_value, $hrule_color, $hrule_label, $show_inverted, $alt_graph_id, $use_alt, $multiplier, $position), $id);
}
