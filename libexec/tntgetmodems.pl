#!/usr/bin/perl

sub conf
{
$data = $ARGV[0];
$uptimeLocation = '/usr/bin/snmpwalk';
$unameLocation = '/usr/bin/snmpwalk';
$snmpwalk = '/usr/bin/snmpwalk';
$snmpgetLocation = '/usr/bin/snmpget';
$community = 'public';
}

&conf;
$uptime = &getUptime;
$sysname = &getName;

$counters = &getCounters();
&mrtgOutput($counters);
exit(0);



sub getName
{
return($ARGV[0]);
}


sub oldgetName
{
open(SYS, "$unameLocation $ARGV[0] $community 1 |");
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
open(UP, "$uptimeLocation $ARGV[0] $community 1 |");
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
local($counters) = @_;
print($counters . "\n");
}

sub getCounters
{
local($oldCounters) = @_;
  open(IN, "$snmpwalk $ARGV[0] $community .1.3.6.1.4.1.529.11.16.1.14 |");
  while(<IN>)
    {
    $modems++;
    }
  close(IN);
return($modems);
}