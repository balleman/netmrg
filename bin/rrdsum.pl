#!/usr/bin/perl

if ($#ARGV < 1)
{
	print("Usage: rrdsum.pl <rrd_file> <start_time> [ end_time ] [ resolution ] \n");
	exit(0);
}

$rrd_file = $ARGV[0];
$start_time = $ARGV[1];
$end_time = $ARGV[2];
if ($end_time == 0)
	{ $end_time = "now"; }
$resolution = $ARGV[3];
if ($resolution == 0)
	{ $resolution = 86400; }


$debug = 0;

$sum = 0;
$count = 0;
@stuff = `rrdtool fetch $rrd_file AVERAGE -r $resolution -s $start_time -e $end_time`;
$stuff[0] = "nan";
$stuff[1] = "nan";
foreach $i (@stuff)
{
	if (!($i =~ /nan/))
	{
		$i =~ s/.*: //;

	        $num = $i;
      		$num =~ s/e\+.*\n//;
		
		$exp = $i;
		$exp =~ s/.*e\+//;
		$exp =~ s/\n//;
	
		$val = $num * (10 ** $exp);
	
		if ($debug)
		{
			print("num: $num, exp: $exp, val: $val\n");
		}
	
		$sum = $sum + $val;

		#$count++;
	}
	
	$count++;
}

$average = $sum / $count;
$total = $average * $resolution;
if ($debug)
{
	print("sum: $sum\n");
	print("avg: $average\n");
	print("tot: $total\n");
} else {
	print("$total\n");
}

