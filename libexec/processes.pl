#!/usr/bin/perl
#Outputs MRTG data based on current number of processes and users

$outbound = `ps ax | wc -l`;

$outbound =~ s/ +//;

chop($outbound);

$outbound--;

print("$outbound\n");
