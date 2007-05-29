#!/usr/bin/perl

use strict;

### config variables
my $blockdev = "/sys/block";


### argument processing
if (@ARGV != 2 or
    $ARGV[0] !~ /^-[rw]b?$/ or
    $ARGV[1] !~ /^\w+(?:\d+)?$/)
{
	print "U\n";
	print "\n";
	print "$0 [-r|-rb|-w|-wb] <dev(N)>\n";
	print "\n";
	print "Reports disk I/O statistics counters for use as a NetMRG test script.\n";
	print "\n";
	print "Options:\n";
	print "  -r      Report the number of read operations\n";
	print "  -rb     Report the number of read blocks\n";
	print "  -w      Report the number of write operations\n";
	print "  -wb     Report the number of written blocks\n";
	print "  dev(n)  Is a relative block device or partition name.\n";
	print "          For instance hda, hda1 or md0\n";
	print "\n";
	exit 1;
}


### figure out device/partitions
my ($hd, $partition);
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
close(STAT);
chomp($line);
$line =~ s/^\s+//;
my ($read, $readb, $write, $writeb);
if ($partition eq "")
{
	($read, $readb, $write, $writeb)=(split /\s+/, $line)[0, 2, 4, 6];
}
else
{
	($read, $readb, $write, $writeb)=split /\s+/, $line;
}

### output the data
if ($ARGV[0] eq "-r")
{
	print "$read\n";
}
elsif ($ARGV[0] eq "-w")
{
	print "$write\n";
}
elsif ($ARGV[0] eq "-rb")
{
	print "$readb\n";
}
elsif ($ARGV[0] eq "-wb")
{
	print "$writeb\n";
}
