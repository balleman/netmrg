#!/usr/bin/php -q
<?
mysql_connect("localhost", "netmrgread", "netmrgread")
        or die("ERROR: cannot connect to server\n");
mysql_select_db("netmrg")
        or die("ERROR: cannot connect to database\n");

$dev_id = $argv[1];

$stat_query = "SELECT avoided FROM mon_devices WHERE id = $dev_id";
$stat_result = mysql_query($stat_query) or 
        die("ERROR: cannot perform query\n$stat_query\n\n");

$stat = mysql_fetch_array($stat_result);

if ($stat["avoided"] != "") {
echo "{$stat["avoided"]}\n";
} else {
echo "1\n";
}

exit();





?>
