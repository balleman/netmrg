#!/usr/bin/perl
#
# linux26diskaccess.pl
#
# reports some disk access counters
#
# # reads issued, # writes issued
#

use strict;

### config variables
my $sysblock = "/sys/block";
### status variables
my $partition = "";
my $blockdev = "";
my @fields = ();


### argument processing
if (scalar(@ARGV) != 2
	|| ($ARGV[0] ne "-r" && $ARGV[0] ne "-w")
	|| ($ARGV[1] !~ /^\w+(\d+)?$/)
	)
{
	print "U\n";
	print "\n";
	print "$0 [-r|-w] <blockDev(N)>\n";
	print "  -r: Report Reads\n";
	print "  -w: Report Writes\n";
	print "\n";
	exit(1);
} # end if not enough parameters


### define partitions
$partition = $ARGV[1];
$blockdev = $1 if ($partition =~ /^(\w+)\d+$/);


### read info from system block

# read the data from the correct path
my $path = "";
if (-e "$sysblock/$blockdev/$partition/stat")
{
	$path = "$sysblock/$blockdev/$partition/stat";
}
elsif (-e "$sysblock/$partition/stat")
{
	$path = "$sysblock/$partition/stat";
}
else
{
	die ("U\nERROR: couldn't find $partition\n\n");
}

# open the path and read the status info
open(STAT, $path) || die ("U\nERROR: couldn't open $path\n\n");
my $line = <STAT>;
chomp($line);
@fields = split /\s+/, $line;
close(STAT);


### output the data
if ($ARGV[0] eq "-r")
{
	print $fields[1]."\n";
} # end if read statistics
elsif ($ARGV[0] eq "-w")
{
	print $fields[5]."\n" if ($partition eq "");
	print $fields[3]."\n" if ($partition ne "");
} # end if write statistics
else
{
	print "U\n";
} # else we don't know what happened


