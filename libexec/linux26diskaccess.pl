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
my $blockdev = "/sys/block";
### status variables
my $hd = "";
my $partition = "";
my @fields = ();


### argument processing
if (scalar(@ARGV) != 2
	|| ($ARGV[0] ne "-r" && $ARGV[0] ne "-w")
	|| ($ARGV[1] !~ /^hd\w(\d+)?$/)
	)
{
	print "U\n";
	print "\n";
	print "$0 [-r|-w] <hdx(N)>\n";
	print "  -r: Report Reads\n";
	print "  -w: Report Writes\n";
	print "\n";
	exit(1);
} # end if not enough parameters


### figure out device/partitions
if ($ARGV[1] =~ /^(hd\w)\d+$/)
{
	$partition = $ARGV[1];
	$hd = $1;
} # end if hd has a partition
else
{
	$hd = $ARGV[1];
} # end else hd is just the drive


### read info from system block

# read the data from the correct path
my $path = "$blockdev/$hd";
$path .= "/$partition" if ($partition ne "");
$path .= "/stat";
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


