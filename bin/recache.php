#!/usr/bin/php -q
<? 
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/snmp_caching.php");
require_once(netmrg_root() . "lib/database.php");

$dev_id = $argv[1];
$type = $argv[2];
$handle = do_query("SELECT * FROM mon_devices WHERE id=$dev_id");
$row = mysql_fetch_array($handle);
if ($type == "disk") {
	cache_disks($row);
	} else {
	cache_device($row);
	}
?>
