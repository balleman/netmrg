#!/usr/bin/php -q
<?
mysql_connect("localhost", "netmrgwrite", "netmrgwrite")
        or die("ERROR: cannot connect to server\n");
mysql_select_db("netmrg")
        or die("ERROR: cannot connect to database\n");

$stat_query = "SELECT id,group_id FROM mon_devices";
$stat_result = mysql_query($stat_query) or 
        die("ERROR: cannot perform query\n$stat_query\n\n");

for ($i = 1; $i <= mysql_num_rows($stat_result); $i++)
{
	$info = mysql_fetch_array($stat_result);	
	mysql_query("INSERT INTO dev_parents SET grp_id=" . $info["group_id"] . ", dev_id=" . $info["id"]);
}

exit();





?>
