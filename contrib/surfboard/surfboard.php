#!/usr/bin/php
<?php
/** CONFIG **/
$signalpage = 'http://192.168.100.1/signaldata.htm';
$ip = '192.168.100.1';
$direction = '';
$variable  = '';

// output parameters
$downstream = array();
$upstream   = array();


/*** MAIN ***/
// if we don't have proper parameters, we might as well stop here
// we require 3 arguments: $ip, $direction, $variable
if (!isset($_SERVER['argc']) || $_SERVER['argc'] < 4)
{
	echo "U: Not enough arguments\n";
	echo "  Usage: <prog> ip direction variable\n";
	echo "    direction: downstream\n";
	echo "      variable: frequency, snr, qam, power_level\n";
	echo "    direction: upstream\n";
	echo "      variable: channel_id, frequency, ranging_service_id, \n";
	echo "                symbol_rate, power_level\n";
	exit(1);
} // end if not proper arguments
// assign variables
$ip = $_SERVER['argv'][1];
$direction = $_SERVER['argv'][2];
$variable  = $_SERVER['argv'][3];


// grab page conents
$signalpage_contents = file_get_contents($signalpage);

// find downstream information
if (preg_match('/Downstream.*?Frequency.*?(\d+)\s*Hz.*?Signal to Noise Ratio.*?(\d+)\s*dB.*?QAM.*?(\d+).*?Power Level.*?(\d+)\s*dBmV/s', $signalpage_contents, $matches))
{
	$downstream['frequency'] = $matches[1];
	$downstream['snr']       = $matches[2];
	$downstream['qam']       = $matches[3];
	$downstream['power_level'] = $matches[4];
} // end if matched downstream info

// find upstream information
if (preg_match('/Upstream.*?Channel ID.*?(\d+).*?Frequency.*?(\d+)\s*Hz.*?Ranging Service ID.*?(\d+).*?Symbol Rate.*?([\d\.]+)\s*Msym\/s.*?Power Level.*?(\d+)\s*dBmV/s', $signalpage_contents, $matches))
{
	$upstream['channel_id']  = $matches[1];
	$upstream['frequency']   = $matches[2];
	$upstream['ranging_service_id'] = $matches[3];
	$upstream['symbol_rate'] = $matches[4];
	$upstream['power_level'] = $matches[5];
} // end if matched upstream info

// check direction and variable to output
if (isset(${$direction}) && isset(${$direction}[$variable]))
{
	echo ${$direction}[$variable] . "\n";
} // end if we passed a valid direction and variable

?>
