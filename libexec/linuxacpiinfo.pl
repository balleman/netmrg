#!/usr/bin/perl
#
# linuxacpiinfo.pl
#
# reports some acpi battery info
#
# % remaining, minutes remaining, ac status
#
# works for all batteries (if you have > 1) and non-standard
# acpi directories (ie, not BATx)
#

use strict;

### config variables
my $acpibatdir = "/proc/acpi/battery";
my $acpiacdir = "/proc/acpi/ac_adapter";
### status variables
my $ac_status = 0;
my $battery_status = 0;
my $battery_maxcapacity = 0;
my $battery_curcapacity = 0;
my $battery_dischargerate = 0;
my $time_remaining = 0;
my $percent_left = 0;


### argument processing
if (scalar(@ARGV) != 1
	|| ($ARGV[0] ne "-p" && $ARGV[0] ne "-th" && $ARGV[0] ne "-tm" && $ARGV[0] ne "-a")
	)
{
	print "U\n";
	print "\n";
	print "$0 [-p|-tm|-th|-a]\n";
	print "   -p: Report Percent Left\n";
	print "  -th: Report Time Remaining (in hours)\n";
	print "  -tm: Report Time Remaining (in minutes)\n";
	print "   -a: Report AC status\n";
	print "\n";
	exit(1);
} # end if not enough parameters


### read info from acpi

# find our ac status
opendir(DIR, $acpiacdir);
my @acdirfiles = grep { !/^\./ } readdir(DIR);
closedir(DIR);

foreach my $acfile (@acdirfiles)
{
	open(AC, "$acpiacdir/$acfile/state") or die("ERROR: couldn't open acfile");
	while (my $line = <AC>)
	{
		$ac_status = $1 if ($line =~ /^state:\s+(\S+)$/);
	} # end while lines
	close(AC);
} # end foreach acfile

# find our battery info
opendir(DIR, $acpibatdir);
my @batdirfiles = grep { !/^\./ } readdir(DIR);
closedir(DIR);

foreach my $batfile (@batdirfiles)
{
	open(BATINFO, "$acpibatdir/$batfile/info") or die("ERROR: couldn't open batfile");
	while (my $line = <BATINFO>)
	{
		$battery_maxcapacity += $1 if ($line =~ /^last full capacity:\s+(\d+)/);
	} # end while lines
	close(BATINFO);

	open(BATSTATE, "$acpibatdir/$batfile/state") or die("ERROR: couldn't open batstate");
	while (my $line = <BATSTATE>)
	{
		$battery_status = $1 if ($line =~ /^charging state:\s+(\S+)$/);
		$battery_curcapacity = $1 if ($line =~ /^remaining capacity:\s+(\d+)/);
		$battery_dischargerate = $1 if ($line =~ /^present rate:\s+(\d+)/);
	} # end while lines
	close(BATSTATE);
} # end foreach batfile

$time_remaining = sprintf("%.2f", $battery_curcapacity/$battery_dischargerate);
$percent_left = sprintf("%.2f", $battery_curcapacity/$battery_maxcapacity*100);


### do what the user wanted
if ($ARGV[0] eq "-p")
{
	print $percent_left."\n";
} # end if percent left

elsif ($ARGV[0] eq "-th")
{
	print $time_remaining."\n";
} # end if time left (hours)

elsif ($ARGV[0] eq "-tm")
{
	print ($time_remaining*60);
	print "\n";
} # end if time left (minutes)

elsif ($ARGV[0] eq "-a")
{
	print $ac_status."\n";
} # end if ac status


### DEBUG
#print "AC Status: $ac_status\n";
#print "BAT Max Capacity: $battery_maxcapacity\n";
#print "BAT Current Capacity: $battery_curcapacity\n";
#print "BAT Discharge Rate: $battery_dischargerate\n";
#print "Time Left: $time_remaining\n";
#print "Percent Left: $percent_left\n";

