<?

require_once("./snmp_caching.php");
$test["id"] = 57;
$test["ip"] = "mrtg1.pa.net";
$test["snmp_read_community"] = "public";
$test["name"] = "MRTG1.pa.net";
cache_disks($test);

?>