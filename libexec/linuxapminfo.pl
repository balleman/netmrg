#!/usr/bin/perl
#
# linuxapminfo.pl
#
# reports values of certain apm values
#

use strict;

if (scalar(@ARGV) != 1 
	|| ($ARGV[0] ne "-p" && $ARGV[0] ne "-t")
	)
{
	print "U\n";
	print "\n";
	print "$0 [-p] [-t]\n";
	print "  -p: Report Percent Left\n";
	print "  -t: Report Time Remaining (in minutes)\n";
	print "\n";
	exit(1);
} # end if not enough parameters

# read in info from apm
open 'apmfh', "/proc/apm";
my $apmline = <apmfh>;
close 'apmfh';

## get the value we want
# percent left
if ($ARGV[0] eq "-p")
{
	if ($apmline =~ /(\d+)%/)
	{
		print "$1\n";
		exit(0);
	} # end if found percent left
} # end if percent left to check

# time remaining
elsif ($ARGV[0] eq "-t")
{
	if ($apmline =~ /(\d+)\s+min$/)
	{
		print "$1\n";
		exit(0);
	} # end if found min left
	elsif ($apmline =~ /(\w+)\s+\?$/)
	{
		print "U\n";
		exit(0);
	} # end if indef minutes left
} # end if time remaining

print "U\n";
exit (1);

