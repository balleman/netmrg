#!/usr/bin/php -q
<?
/*  old-cisco-dev-props.php
**
**  Usage:
**    old-cisco-dev-props.php <host> <community> <model|version>
**
**  Descritpion:
**    Returns either the model number (actually closer to "family" than model),
**    and the IOS version.  Designed for use on 2900XL and 3500XL switches with 
**    older IOS versions that do not support MIBs containing these values separately.
*/

// no errors!
error_reporting(E_ALL);

// if less than 3 arguments, exit
if ($argc != 4) { print "U\nnot enough args"; exit; }

$snmp_result = snmpget($argv[1], $argv[2], "sysDescr.0");

//print($snmp_result);

$model = preg_replace("/.* \(tm\) /s", "", $snmp_result);
$model = preg_replace("/ .*/s", "", $model);
$model = "WS-" . $model; 

$version = preg_replace("/.* Version /s", "", $snmp_result);
$version = preg_replace("/,.*/s", "", $version);

switch ($argv[3])
{
	case "version":	echo("$version\n"); break;
	case "model":	echo("$model\n");	break;
}

exit;
?>

