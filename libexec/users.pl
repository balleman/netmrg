#!/usr/bin/perl
#Outputs MRTG data based on current number of processes and users

$inbound = `w | wc -l`;
$inbound =~ s/ +//;
chop($inbound);
$inbound = $inbound - 2;
print("$inbound\n");
