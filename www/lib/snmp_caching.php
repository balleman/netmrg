<?php
/********************************************
* NetMRG Integrator
*
* snmp_caching.php
* SNMP Structure Caching and Retreival
*
* see doc/LICENSE for copyright information
********************************************/


function is_cached($dev_id)
{
	$h = db_query("SELECT dev_id FROM snmp_cache WHERE dev_id=$dev_id");
	return db_num_rows($h);
} // end is_cached


function is_disk_cached($dev_id)
{
	$h = db_query("SELECT dev_id FROM snmp_disk_cache WHERE dev_id=$dev_id");
	return db_num_rows($h);
} // end is_cached


function snmp_chop($string)
{
	$temp = ereg_replace("\n","",$string);
	return ereg_replace(".*= ","",$temp);
} // end snmp_chop

function snmp_make_ip_array($dev_ip, $community)
{
	$if_ip    = `snmpwalk $dev_ip $community ipAdEntIfIndex`;
	$rows = split("\n", $if_ip);
	for ($i = 0; $i < count($rows); $i++) {
		$index = snmp_chop($rows[$i]);
		$ip = ereg_replace(".*ipAdEntIfIndex.","",$rows[$i]);
		$ip = ereg_replace(" =.*","",$ip);
		$res[$index] = $ip;
		echo "Index: $index, IP: $ip\n";
	}
	return $res;
} // end snmp_make_ip_array


function make_normal_snmp_array($dev_ip, $community, $oid_root)
{
	echo "Begin $oid_root array creation\n";
	$if_data    = `snmpwalk $dev_ip $community $oid_root`;
	$rows = split("\n", $if_data);
	for ($i = 0; $i < count($rows); $i++) {
		$value = snmp_chop($rows[$i]);
		$index = ereg_replace(".*$oid_root.","",$rows[$i]);
		$index = ereg_replace(" =.*","",$index);
		$res[$index] = $value;
		echo "Index: $index, Value: $value\n";
	}
	return $res;
} // end make_normal_snmp_array


function cache_device($dev_row)
{
	$dev_id = $dev_row["id"];
	$dev_ip = $dev_row["ip"];

	// delete the old cache
	db_update("DELETE FROM snmp_cache WHERE dev_id=$dev_id");

	// get a new cache
	echo("Beginning SNMP-caching of Device: " . $dev_row["name"] . "\n");
	$community = $dev_row["snmp_read_community"];
	echo("SNMP-walking ifIndex...\n");
	echo("DEV ID: {$dev_row['id']}\n");
	$if_index = `snmpwalk $dev_ip $community ifIndex`;
	$rows = split("\n", $if_index);

	echo("SNMP-walking ipAndEntIfIndex...\n");
	$ip_data = snmp_make_ip_array($dev_ip, $community);
	echo("SNMP-walking ifName...\n");
	$name_data = make_normal_snmp_array($dev_ip, $community, "ifName");
	echo("SNMP-walking ifPhysAddress...\n");
	$mac_data = make_normal_snmp_array($dev_ip, $community, "ifPhysAddress");
	echo("SNMP-walking ifDescr...\n");
	$desc_data = make_normal_snmp_array($dev_ip, $community, "ifDescr");
	echo("SNMP-walking ifAlias...\n");
	$alias_data = make_normal_snmp_array($dev_ip, $community, "ifAlias");

	for ($i = 0; $i < (count($rows) - 1); $i++) {
		$if_index = snmp_chop($rows[$i]);
		$if_ip    = $ip_data[$if_index];
		$if_name = $name_data[$if_index];
		$if_desc = $desc_data[$if_index];
		$if_mac = $mac_data[$if_index];
		$if_alias = $alias_data[$if_index];

		echo "Interface $if_index:\n";
		echo "Name : " . $if_name . "\n";
		echo "MAC  : " . $if_mac . "\n";
		echo "IP   : " . $ip_data[$if_index] . "\n";
		echo "Descr: " . $if_desc . "\n";
		echo "Alias: " . $if_alias . "\n\n";

		db_update("
			INSERT INTO snmp_cache SET
			dev_id=$dev_id,
			if_index=$if_index,
			if_name=\"$if_name\",
			if_ip=\"$if_ip\",
			if_desc=\"$if_desc\",
			if_mac=\"$if_mac\",
			if_alias=\"$if_alias\"");
	} // end for

} // end cache_device


function ensure_cached($dev_id)
{
	if (is_cached($dev_id) < 1) {
		$h = db_query("SELECT * FROM mon_devices WHERE id=$dev_id");
		$row = db_fetch_array($h);
		cache_device($row);
	} // end if device is cached
} // end ensure_cached;


function cache_disks($dev_row)
{
	$dev_id = $dev_row["id"];
	$dev_ip = $dev_row["ip"];
	db_update("DELETE FROM snmp_disk_cache WHERE dev_id=$dev_id");
	echo("Beginning disk-caching of Device: " . $dev_row["name"] . "\n");
	$community = $dev_row["snmp_read_community"];
	echo("SNMP-walking dskIndex...\n");
	echo("DEV ID: " . $dev_row["id"]);
	$dsk_index = `snmpwalk $dev_ip $community dskIndex`;
	$rows = split("\n", $dsk_index);

	echo("SNMP-walking dskPath...\n");
	$path_data = make_normal_snmp_array($dev_ip, $community, "dskPath");
	echo("SNMP-walking dskDevice...\n");
	$device_data = make_normal_snmp_array($dev_ip, $community, "dskDevice");

	for ($i = 0; $i < (count($rows) - 1); $i++) {
		$dsk_index  = snmp_chop($rows[$i]);
		$dsk_path   = $path_data[$dsk_index];
		$dsk_device = $device_data[$dsk_index];

		echo "Disk $dsk_index:\n";
		echo "Path  : " . $dsk_path . "\n";
		echo "Device: " . $dsk_device . "\n";

		db_update("
			INSERT INTO snmp_disk_cache SET
			dev_id=$dev_id,
			disk_index=$dsk_index,
			disk_path=\"$dsk_path\",
			disk_device=\"$dsk_device\"");
	} // end for
} // end cache_disks


function ensure_disk_cached($dev_id)
{
	if (is_disk_cached($dev_id) < 1) {
		$h = db_query("SELECT * FROM mon_devices WHERE id=$dev_id");
		$row = db_fetch_array($h);
		cache_device($row);
	} // end if disk is cached
} // end ensure_disk_cached;

?>
