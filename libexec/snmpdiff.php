#!/usr/bin/php -q
<?
/*  snmpdiff.php
**
**  Usage:
**    snmpdiff.php <host> <community> <oid0> <oid1>
**
**  Descritpion:
**    Gets an oid1 and oid2 from a community@host and 
**    returns just the facts
*/

// no errors!
error_reporting(E_NONE);

// if less than 4 arguments, exit
if ($argc != 5) { exit; }

$snmp_result0 = snmpget($argv[1], $argv[2], $argv[3]);
$snmp_result1 = snmpget($argv[1], $argv[2], $argv[4]);

//if (!empty($snmp_result0) && !empty($snmp_result1)) {
	$snmp_parts0 = explode(" ", $snmp_result0);
	$snmp_parts1 = explode(" ", $snmp_result1);
	$diff = ($snmp_parts0[count($snmp_parts0)-1]+0) - ($snmp_parts1[count($snmp_parts1)-1]+0);
	echo "$diff\n";
//} else {
//	echo "U\n";
//} // end if

exit;
?>

