#!/usr/bin/php -q
<?
$device_id = $argv[1];
set_time_limit(0);
$today = getdate();
echo "{$today['month']} {$today['mday']} {$today['hours']}:{$today['minutes']}:{$today['seconds']}\n";
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");
require_once(netmrg_root() . "lib/processing.php");
require_once(netmrg_root() . "lib/snmp_caching.php");

$dev_results = do_query("SELECT * FROM mon_devices WHERE id=$device_id");
$dev_row = mysql_fetch_array($dev_results);
	
	$avoid_this_device = 0;  # this is set to 1 during a snmp-timeout and snmp-gets thereafter are skipped
	
	if ($dev_row["snmp_recache"] == 1) { cache_device($dev_row); }
	
	$mon_results = do_query("
	SELECT mon_monitors.id, mon_devices.ip AS ip, mon_test.cmd AS cmd, mon_monitors.params AS params,
		   mon_devices.name AS dev_name, mon_test.name as test_name, graph_dst.name AS dst,
		   mon_test.data_type AS data_type, mon_monitors.graphed AS graphed,
		   mon_monitors.mon_type AS mon_type, snmp_tests.oid AS oid, mon_devices.snmp_read_community AS snmp_read_community,
		   mon_monitors.snmp_index_type AS snmp_index_type, mon_monitors.snmp_index_value AS snmp_index_value,
		   mon_monitors.disk_index_type AS disk_index_type, mon_monitors.disk_index_value AS disk_index_value,
		   snmp_data.oidroot AS snmp_oidroot, disk_data.oidroot AS disk_oidroot 
	FROM mon_monitors 
	LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id 
	LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id 
	LEFT JOIN graph_dst ON mon_monitors.rrd_id=graph_dst.id 
	LEFT JOIN snmp_tests ON mon_monitors.snmp_test=snmp_tests.id 
	LEFT JOIN snmp_data ON mon_monitors.snmp_data=snmp_data.id 
	LEFT JOIN disk_data ON mon_monitors.disk_data=disk_data.id 
	WHERE mon_monitors.device_id=$device_id");
	$mon_total = mysql_num_rows($mon_results);
	
	for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count) {
	# For each monitor, do test
		
		$mon_row = mysql_fetch_array($mon_results);
		
		$mon_id  = $mon_row["id"];
		
		//$test_result = 0;
		if (isset($test_result)) { unset($test_result); }
		if (isset($test_output)) { unset($test_output); }
		
		# added by Doug Warner <dwarner@pa.net> on 2001.06.18
		echo "\n";
		echo "Device: {$mon_row['dev_name']}\n";
		# Run test based on how data is gathered
		$test_output = "";
		switch ($mon_row["mon_type"]) {
			case 1:
				$test_command = $mon_row["cmd"] . " " . $mon_row["params"];
				$test_command = str_replace("%dev_name", $mon_row["dev_name"], $test_command);
				$test_command = str_replace("%ip", $mon_row["ip"], $test_command);
				$test_command = str_replace("%test_name", $mon_row["test_name"], $test_command);
				echo "Command:$test_command\n";
				if ($mon_row["data_type"] == 1) {
					echo "Result Type: Error Condition\n";
					system($test_command, $test_result);
				} else {
					echo "Result type: Standard Output\n";
					$test_result = exec($test_command, $test_output, $junk2);
				} // end if
				break;
			case 2:
				$test_result = do_snmp_get($mon_row["ip"], $mon_row["snmp_read_community"], $mon_row["oid"]);
				echo "SNMP Get: " . $mon_row['ip'] . " " . $mon_row["snmp_read_community"] . " " . $mon_row["oid"] . "\n";
				break;
			case 3:
				switch ($mon_row['snmp_index_type']) {
					case 2:
						$if_index = $mon_row['snmp_index_value'];
						break;
					case 3:
						ensure_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id=$device_id AND if_name=\"" . $mon_row['snmp_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$if_index = $snmp_row["if_index"];
						break;
					case 4:
						ensure_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id=$device_id AND if_ip=\"" . $mon_row['snmp_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$if_index = $snmp_row["if_index"];
						break;
					case 5:
						ensure_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id=$device_id AND if_mac=\"" . $mon_row['snmp_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$if_index = $snmp_row["if_index"];
						break;
					case 6:
						ensure_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id=$device_id AND if_desc=\"" . $mon_row['snmp_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$if_index = $snmp_row["if_index"];
						break;
					case 7:
						ensure_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id=$device_id AND if_alias=\"" . $mon_row['snmp_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$if_index = $snmp_row["if_index"];
						break;
				}
				echo "Interface Get: $if_index\n";
				$oid = $mon_row['snmp_oidroot'] . "." . $if_index;
				$test_result = do_snmp_get($mon_row["ip"], $mon_row["snmp_read_community"], $oid);
				echo "Interface SNMP Get: " . $mon_row['ip'] . " " . $mon_row["snmp_read_community"] . " " . $oid . "\n";
				break;
			case 4:
				switch ($mon_row["disk_index_type"]) {
					case 1:
						$dsk_index = $mon_row['disk_index_value'];
						break;
					case 2:
						ensure_disk_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_disk_cache WHERE dev_id=$device_id AND disk_path=\"" . $mon_row['disk_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$dsk_index = $snmp_row["disk_index"];
						break;
					case 3:
						ensure_disk_cached($device_id);
						$snmp_query = do_query("SELECT * FROM snmp_disk_cache WHERE dev_id=$device_id AND disk_device=\"" . $mon_row['disk_index_value'] . "\"");
						$snmp_row = mysql_fetch_array($snmp_query);
						$dsk_index = $snmp_row["disk_index"];
						break;
					}
				echo "Disk Get: $dsk_index\n";
				$oid = $mon_row['disk_oidroot'] . "." . $dsk_index;
				$test_result = do_snmp_get($mon_row["ip"], $mon_row["snmp_read_community"], $oid);
				echo "Disk SNMP Get: " . $mon_row['ip'] . " " . $mon_row["snmp_read_community"] . " " . $oid . "\n";
				break;
		}
		
		if (($test_result == "") && ($test_output[0] != "")) {
			$test_result = $test_output[0];
		} // end if $test_result emtpy;
		echo "Data Captured: $test_result\n";
		
		echo "Is Graphed: {$mon_row['graphed']}\n";
		if ($mon_row["graphed"] == 1) { update_monitor_rrd($mon_row, $test_result); }
		
		$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id");
		$event_total = mysql_num_rows($event_results);
		
		for ($event_count = 1; $event_count <= $event_total; ++$event_count) { 
		# For each event
			
			$event_row = mysql_fetch_array($event_results);
			$event_id = $event_row["id"];
			
			# modified by Doug Warner <dwarner@pa.net> on 2001.06.19
			#   dont need nested 'if's here -- elseif will work fine
			if ($event_row["condition"] == 1) {
				# Trigger if equal
				$triggered = ($test_result == $event_row["result"]);
			} elseif ($event_row["condition"] == 2) {
				# Trigger if greater than
				$triggered = ($test_result > $event_row["result"]);
			} else {
				# Trigger if less than
				$triggered = ($test_result < $event_row["result"]);
			} # End Triggers
			
			$triggered = $triggered + 0;
			
			do_update("UPDATE mon_events SET last_status=$triggered WHERE id=$event_id");
			if ($triggered != $event_row["last_status"]) {
			do_update("UPDATE mon_events SET last_change=" . time() . " WHERE id=$event_id");
			}
			echo "\n\n";
			echo "Trigger:  {$event_row['condition']}\n";
			echo "Result:   $test_result\n";
			echo "CompRes:  {$event_row['result']}\n";
			echo "Triggerd: $triggered\n";
			
			$triggered = ($triggered AND (($event_row["options"] == 0) OR ($triggered != $event_row["last_status"])));
			
			
			if ($triggered) {
				print("Processing Responses...\n");
				# If the event is triggered, respond
				
				do_update("INSERT INTO event_log SET dev_name=\"" . $dev_row["name"] . 
				           "\", date=" . time() . ", event_text='" . $event_row["display_name"] . "'" . 
						   ", since_last_change=" . (time() - $event_row["last_change"]) . 
						   ", situation=" . $event_row["situation"]);
			
				$resp_results = do_query("
				SELECT mon_responses.id, mon_responses.cmd_params, mon_notify.cmd, mon_devices.name as dev_name,
				mon_devices.ip, mon_test.name as test_name, mon_events.result, mon_notify.disabled AS notify_disabled 
				FROM mon_responses 
				LEFT JOIN mon_events ON mon_responses.events_id=mon_events.id
				LEFT JOIN mon_notify ON mon_responses.notify_id=mon_notify.id 
				LEFT JOIN mon_monitors ON mon_events.monitors_id=mon_monitors.id
				LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id 
				LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
				WHERE events_id=$event_id AND notify_disabled=0");
				$resp_total = mysql_num_rows($resp_results);
				for ($resp_count = 1; $resp_count <= $resp_total; ++$resp_count) { 
				# For each response
					print("Processing a response...\n");
					$resp_row = mysql_fetch_array($resp_results);
					$resp_command = $resp_row["cmd"] . " " . $resp_row["cmd_params"];
					$resp_command = str_replace("%dev_name",$resp_row["dev_name"],$resp_command);
					$resp_command = str_replace("%ip",$resp_row["ip"],$resp_command);
					$resp_command = str_replace("%test_name",$resp_row["test_name"],$resp_command);
					$resp_command = str_replace("%test_result",$resp_row["result"],$resp_command);
					system($resp_command);
				} # end response for
			} # end if triggered
		} # end event for
	} # end monitor for
	
	
	unlink("/var/www/netmrg/dat/dev_locks/$device_id.lock");
?>
