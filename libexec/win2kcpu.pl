#!/usr/bin/perl
#
# win2kcpu.pl
#
# uses snmpwalk to return the cpu load for
# the cpu requested, or the first cpu if no cpu specified
#

use strict;

# we need at least a host and a community string
if (scalar(@ARGV) < 2)
{
	print "U\n";
	exit();
} # end if only one argument

# some variables to be used later
my $host = $ARGV[0];
my $community = $ARGV[1];
my $cpunumber = 1;
my $cmdoutput;
my @outputpercpu;

if (defined($ARGV[2]))
{
	$cpunumber = $ARGV[2];
} # end if cpu number specified


# execute snmpwalk and store output
$cmdoutput = `snmpwalk -Ov -c $community $host host.hrDevice.hrProcessorTable.hrProcessorEntry.hrProcessorLoad 2> /dev/null`;
@outputpercpu = split(/\n$/, $cmdoutput);

my $i = 0;
foreach my $line (@outputpercpu)
{
	$i++;
	if ($i == $cpunumber)
	{
		$line =~ /(\d+)$/;
		print "$1\n";
		exit();
	} # end if time to output cpu info
} # end output for each cpu

print "U\n";

