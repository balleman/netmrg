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
my $max_minutes = 600; # 10hrs
### status variables
my $ac_status = 0;
my $battery_status = 0;
my $battery_maxcapacity = 0;
my $battery_curcapacity = 0;
my $battery_dischargerate = 0;
my $time_remaining = "U";
my $percent_left = "U";


### argument processing
if (scalar(@ARGV) != 1
	|| ($ARGV[0] ne "-p" && $ARGV[0] ne "-th" && $ARGV[0] ne "-tm" && $ARGV[0] ne "-V")
	)
{
	print "U\n";
	print "\n";
	print "$0 [-p|-tm|-th|-a|-V]\n";
	print "   -p: Report Percent Left\n";
	print "  -th: Report Time Remaining (in hours)\n";
	print "  -tm: Report Time Remaining (in minutes)\n";
	print "   -V: acpi -V compatibility\n";
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

$time_remaining = sprintf("%.2f", $battery_curcapacity/$battery_dischargerate) if ($battery_dischargerate);
$time_remaining = "U" if ($time_remaining*60 > $max_minutes);
$percent_left = sprintf("%.2f", $battery_curcapacity/$battery_maxcapacity*100);


### do what the user wanted
if ($ARGV[0] eq "-p")
{
	print int($percent_left)."\n";
} # end if percent left

elsif ($ARGV[0] eq "-th")
{
	print $time_remaining."\n";
} # end if time left (hours)

elsif ($ARGV[0] eq "-tm")
{
	print int($time_remaining*60) if ($time_remaining ne "U");
	print "\n";
} # end if time left (minutes)

elsif ($ARGV[0] eq "-V")
{
#$ acpi -V
#     Battery 1: unknown, 100%
#     Thermal 1: ok, 43.0 degrees C
#  AC Adapter 1: on-line
#$ acpi -V
#     Battery 1: charging, 97%, 00:26:45 until charged
#     Thermal 1: ok, 39.0 degrees C
#  AC Adapter 1: on-line
#$ acpi -V
#     Battery 1: discharging, 71%, 02:12:12 remaining
#     Thermal 1: ok, 48.0 degrees C
#  AC Adapter 1: off-line
	print "	Battery 1: ".$battery_status.", ";
	print int($percent_left)."%";
	if ($battery_status eq "discharging")
	{
		my $hours_remaining = int($time_remaining);
		my $minutes_remaining = ($time_remaining * 60) % 60;
		my $seconds_remaining = ($time_remaining * 3600) % 60;
		print ", ";
		printf("%.2d", $hours_remaining);
		print ":";
		printf("%.2d", $minutes_remaining);
		print ":";
		printf("%.2d", $seconds_remaining);
		print " remaining";
	} # end if discharging
	print "\n";
	print "	Thermal 1: ok, 30.0 degrees C\n";
	print "	AC Adapter 1: ".$ac_status."\n";
} # end if ac status


### DEBUG
#print "AC Status: $ac_status\n";
#print "BAT Status: $battery_status\n";
#print "BAT Max Capacity: $battery_maxcapacity\n";
#print "BAT Current Capacity: $battery_curcapacity\n";
#print "BAT Discharge Rate: $battery_dischargerate\n";
#print "Time Left: $time_remaining\n";
#print "Percent Left: $percent_left\n";

