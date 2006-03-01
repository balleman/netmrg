#!/usr/bin/php
<?php
/** CONFIG **/
// 5100 is at 'http://192.168.100.1/signaldata.html'
$signalpage = 'http://192.168.100.1/signaldata.htm';
$direction = '';
$variable  = '';

// output parameters
$regex     = '';
$outputval = 'U';
$downstream = array();
$upstream   = array();


/*** MAIN ***/
// if we don't have proper parameters, we might as well stop here
// we require 3 arguments: $ip, $direction, $variable
if (!isset($_SERVER['argc']) || $_SERVER['argc'] < 4)
{
	echo "U: Not enough arguments\n";
	echo "  Usage: <prog> url direction variable\n";
	echo "    url: full path to the signal page (not the frame!)\n";
	echo "      ex: $signalpage\n";
	echo "    direction: downstream\n";
	echo "      variable: frequency, snr, qam, power_level\n";
	echo "    direction: upstream\n";
	echo "      variable: channel_id, frequency, ranging_service_id, \n";
	echo "                symbol_rate, power_level\n";
	exit(1);
} // end if not proper arguments
// assign variables
$signalpage = $_SERVER['argv'][1];
$direction  = $_SERVER['argv'][2];
$variable   = $_SERVER['argv'][3];

// grab page conents
$signalpage_contents = file_get_contents($signalpage);

switch ($direction)
{
	case 'downstream':
		switch ($variable)
		{
			case 'frequency':
				$regex = '/Downstream.*?Frequency.*?(\d+)\s*Hz/s';
				break;
			
			case 'snr':
				$regex = '/Downstream.*?Signal to Noise Ratio.*?(\d+)\s*dB/s';
				break;
			
			case 'qam':
				$regex = '/Downstream.*?QAM.*?(\d+)/s';
				break;
			
			case 'power_level':
				$regex = '/Downstream.*?Power Level.*?(\d+)\s*dB(?:mV)?/s';
				break;
		} // end switch variable
		break;
	
	case 'upstream':
		switch ($variable)
		{
			case 'channel_id':
				$regex = '/Upstream.*?Channel ID.*?(\d+).*?/s';
				break;
			
			case 'frequency':
				$regex = '/Upstream.*?Frequency.*?(\d+)\s*Hz/s';
				break;
			
			case 'ranging_service_id':
				$regex = '/Upstream.*?Ranging Service ID.*?(\d+)/s';
				break;
			
			case 'symbol_rate':
				$regex = '/Upstream.*?Symbol Rate.*?([\d\.]+)\s*Msym\/s/s';
				break;
			
			case 'power_level':
				$regex = '/Upstream.*?Power Level.*?(\d+)\s*dBmV/s';
				break;
		} // end switch variable
		break;
} // end switch direction

// lookup appropriate value
if (!empty($regex) && preg_match($regex, $signalpage_contents, $matches))
{
	$outputval = $matches[1];
} // end if regex and regex match

// check output
if (!empty($outputval))
{
	echo "$outputval\n";
} // end if we had output to pass along

?>
