#!/usr/bin/perl
#Outputs MRTG data based on present TCP/IP connections

$device = $ARGV[0];
$outbound = `/sbin/ifconfig | grep "Link encap:" | grep "eth" | wc -l`;
$outbound =~ s/ +//;
chop($outbound);
print("$outbound\n");
