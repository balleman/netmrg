#!/usr/bin/php -q
<?

## Config Variables ##
$max_num_threads = 3;  # The most threads to have running simultaneously
$sleep_period = 5;     # Period to wait between checks on number of threads running



set_time_limit(0);

if (file_exists("/var/www/netmrg/dat/lockfile")) {
echo("ERROR:  My lockfile exists!  Is another netmrg_master running?\n  If not, remove the lockfile and try again\n");
exit;
}

touch("/var/www/netmrg/dat/lockfile");

$start_time = time();

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");
require_once(netmrg_root() . "lib/processing.php");

function num_threads() {
clearstatcache(); 
$num_children = 0;
chdir("/var/www/netmrg/dat/dev_locks");
if ($dir = opendir("/var/www/netmrg/dat/dev_locks")) {
  while ($file = readdir($dir)) {
   if (file_exists($file)) { $num_children++; }
  }  
  closedir($dir);
}

$num_children = $num_children - 2;
echo("Children: $num_children \n");
return $num_children;
}

# added by Doug Warner <dwarner@pa.net> on 2001.06.19
$today = getdate();
echo "{$today['month']} {$today['mday']} {$today['hours']}:{$today['minutes']}:{$today['seconds']}\n";

$dev_results = do_query("SELECT id, name FROM mon_devices WHERE disabled=0");
$dev_total = mysql_num_rows($dev_results);

for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count) { 
# For each device
	$dev_row = mysql_fetch_array($dev_results);
	$device_id = $dev_row["id"];
	while ( num_threads() > $max_num_threads) { sleep($sleep_period); }
	echo("Spawning for device $device_id...\n");
	exec("/var/www/netmrg/bin/netmrg_slave.php $device_id >>/var/www/netmrg/log/" . $dev_row["name"] . " 2>&1 &");
	touch("/var/www/netmrg/dat/dev_locks/$device_id.lock");
	#sleep(1);
} # end device for

while (num_threads() > 1) { sleep($sleep_period); }

$end_time = time();
$run_time = $end_time - $start_time;
print("Total Runtime: $run_time seconds\n");
`echo '$run_time' > /var/www/netmrg/dat/runtime`;

unlink("/var/www/netmrg/dat/lockfile");

?>