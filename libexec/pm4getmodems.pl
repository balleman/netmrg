#!/usr/bin/perl

sub conf
{
#$data = '/home/httpd/html/monitor/PM3s/crl/crl.data';
$data = $ARGV[0];
$uptimeLocation = '/usr/bin/snmpwalk';
$unameLocation = '/usr/bin/snmpwalk';
$snmpwalk = '/usr/bin/snmpwalk';
$snmpgetLocation = '/usr/bin/snmpget';
$community = 'public';
#$timeMultiplier = 300; #mins * secs;
$timeMultiplier = 1; #mins * secs;
}

&conf;
$uptime = &getUptime;
$sysname = &getName;

#$oldCounters = &getHistory;
$oldCounters = 0;

$counters = &getCounters($oldCounters);
&mrtgOutput($counters, $uptime, $sysname);
&writeHistory($counters);
exit(0);



sub getName
{
return($ARGV[1]);
}


sub oldgetName
{
open(SYS, "$unameLocation $ARGV[1] $community 1 |");
#open(SYS, "/bin/uname -n |");
while(<SYS>)
  {
  if(/sysname/i)
    {
    chop;
    close(SYS);
    return($_);
    }
  }
return($_);
}

sub getUptime
{
open(UP, "$uptimeLocation $ARGV[1] $community 1 |");
#open(UP, "/usr/bin/uptime |");
while(<UP>)
  {
  if(/uptime/i)
    {
    chop;
    local(@line) = split(/ = /);
    close(UP);
    return($line[1]);
    }
  }
}

sub mrtgOutput
{
local($counters, $uptime, $sysname) = @_;
print<<EOF;
$counters
$counters
$uptime
$sysname
EOF
}

sub getHistory
{
local($last);
open(IN, "$data");
$_ = <IN>;
#chop();
#$_ =~ s/ //g;
$last = $_;
close(IN);
#print"$last is last\n";
return($last);
}


sub getCounters
{
local($oldCounters) = @_;
for($i=1;$i<@ARGV;$i++)
  {
  open(IN, "$snmpwalk $ARGV[$i] public .1.3.6.1.4.1.307.3.2.1.1.1.4 |");
  while(<IN>)
    {
    $modems++ unless /""/;
    }
  close(IN);
  }
$modems = $timeMultiplier * $modems;
$modems += $oldCounters;
#print STDERR <<EOF;
#Bytes In = $modems
#Bytes Out = $modems
#EOF
return($modems);
}

sub writeHistory
{
local($counters) = @_;
open(OUT, ">$data");
print OUT $counters;
close(OUT);
}
