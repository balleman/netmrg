#!/usr/bin/php -q
<?

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");
require_once(netmrg_root() . "lib/processing.php");

// c5k port names
//
// snmpwalk sw-farm3.pa.net public .1.3.6.1.4.1.9.5.1.4.1.1.4.slot.port
//

$dev_id = $argv[1];

$snmp_query = do_query("SELECT * FROM snmp_cache WHERE dev_id = $dev_id");
$num_rows = mysql_num_rows($snmp_query);

$dev_query = do_query("SELECT * FROM mon_devices WHERE id = $dev_id");
$dev_row = mysql_fetch_array($dev_query);


for ($i = 0; $i < $num_rows; $i++)
{
	$snmp_row = mysql_fetch_array($snmp_query);

	$slotport = split("/", $snmp_row["if_name"]);

	$slot = $slotport[0];
	$port = $slotport[1];

	if ($port != "")
	{
		$dev_ip   = $dev_row["ip"];
		$dev_comm = $dev_row["snmp_read_community"];
		$oid      = ".1.3.6.1.4.1.9.5.1.4.1.1.4." . $slot . "." . $port;
		$raw_name = `snmpwalk $dev_ip $dev_comm $oid`;
		$name     = ereg_replace(".* = \"", "", $raw_name);
		$name     = ereg_replace("\"", "", $name);
		$name	  = ereg_replace("\n", "", $name);

		echo($name . "\n");
	
		$insert_query =
		"UPDATE snmp_cache SET if_alias = \"" . $name . "\" WHERE dev_id = $dev_id AND if_index = " .
		$snmp_row["if_index"];

		echo($insert_query . "\n");
		
		do_update($insert_query);
	}


}






?>
