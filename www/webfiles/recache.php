<? 
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/snmp_caching.php");
require_once(netmrg_root() . "lib/database.php");

$handle = do_query("SELECT * FROM mon_devices WHERE id=$dev_id");
$row = mysql_fetch_array($handle);
begin_page(2);
echo "<PRE>";
if ($type == "disk") {
	cache_disks($row);
	} else {
	cache_device($row);
	}
echo "</PRE>";
end_page();
?>
