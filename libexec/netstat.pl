#!/usr/bin/perl
#Outputs MRTG data based on present TCP/IP connections

$proto = $ARGV[0];

$outbound = `netstat -an | grep -v 127.0.0.1 | grep $proto | wc -l`;
$outbound =~ s/ +//;
chop($outbound);
print("$outbound\n");
