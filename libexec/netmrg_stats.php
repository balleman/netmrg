#!/usr/bin/php -q
<?
mysql_connect("localhost", "netmrgread", "netmrgread")
        or die("ERROR: cannot connect to server\n");
mysql_select_db("netmrg")
        or die("ERROR: cannot connect to database\n");

$type_of_stats = $argv[1];

if ($type_of_stats == "rundev")
	{
		$stat_query = "SELECT id FROM mon_devices WHERE disabled=0";
	} 
if ($type_of_stats == "runmon")
	{
		$stat_query = "SELECT mon_monitors.id FROM mon_monitors LEFT JOIN mon_devices ON mon_devices.id=mon_monitors.device_id WHERE mon_devices.disabled = 0";
	}


$stat_result = mysql_query($stat_query) or 
        die("ERROR: cannot perform query\n$stat_query\n\n");

$stat = mysql_num_rows($stat_result);

if ($stat != "") {
echo "$stat\n";
} else {
echo "0\n";
}

?>
