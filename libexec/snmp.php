#!/usr/bin/php -q
<?
/*  snmp.php
**
**  Usage:
**    snmp.php <host> <community> <oid>
**
**  Descritpion:
**    Gets a oid from a community@host and 
**    returns just the value
*/

// no errors!
error_reporting(E_NONE);

// if less than 3 arguments, exit
if ($argc != 4) { exit; }

$snmp_result = snmpget($argv[1], $argv[2], $argv[3]);

if (!empty($snmp_result)) {
	$snmp_parts = explode(" ", $snmp_result);
	echo "{$snmp_parts[count($snmp_parts)-1]}\n";
} else {
	echo "\n";
} // end if

exit;
?>

