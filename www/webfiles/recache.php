<?php 
/********************************************
* NetMRG Integrator
*
* recache.php
* SNMP recaching script
*
* see doc/LICENSE for copyright information
********************************************/

require_once("../include/config.php");

$handle = do_query("SELECT * FROM mon_devices WHERE id={$_REQUEST['dev_id']}");
$row = mysql_fetch_array($handle);
begin_page("recache.php", "SNMP Recache");

echo "<PRE>";

if (!empty($_REQUEST["type"]) && $_REQUEST["type"] == "disk")
{
	cache_disks($row);
}
else
{
	cache_device($row);
} // end if type

echo "</PRE>";

end_page();

?>
