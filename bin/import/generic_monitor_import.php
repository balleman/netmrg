#!/usr/bin/php -q
<?
mysql_connect("localhost", "netmrgwrite", "netmrgwrite")
        or die("ERROR: cannot connect to server\n");
mysql_select_db("netmrg")
        or die("ERROR: cannot connect to database\n");

$stat_query = "select id from mon_devices";
$stat_result = mysql_query($stat_query) or die("ERROR: cannot perform query\n$stat_query\n\n");

for ($i = 1; $i <= mysql_num_rows($stat_result); $i++)
{
	$info = mysql_fetch_array($stat_result);
	mysql_query("insert into sub_devices set dev_id=" . $info["id"] . ", type=1");
	$sub_dev_id = mysql_insert_id();

	$mon_q = mysql_query("select * from mon_monitors where device_id=" . $info["id"] . " and mon_type < 3");
	for ($e = 0; $e < mysql_num_rows($mon_q); $e++)
	{
	        $row = mysql_fetch_array($mon_q);
		if ($row["graphed"] == 1)
		{
		        $rrdtype = $row["rrd_id"];
                } else { $rrdtype = -1; }

		if ($row["max_val"] == 0){
                	$maxval = "NULL";
			} else { $maxval = $row["max_val"]; }

                if ($row["mon_type"] == 1)
		        { $testid = $row["test_id"]; } else { $testid = $row["snmp_test"]; }

		mysql_query("insert into monitors set id=" . $row["id"] . ", sub_dev_id=$sub_dev_id, rrd_type=$rrdtype, min_val=NULL, max_val=$maxval, test_type=" . $row["mon_type"] . ", test_id=$testid, test_params=\"" . $row["params"] . "\"");
        }

}

exit();





?>
