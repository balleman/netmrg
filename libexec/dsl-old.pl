#!/usr/bin/perl
#Outputs data based on present Virtual-Access interfaces 

$outbound = `snmpwalk chm3.rtr.pa.net public ifDescr | grep "Virtual-Access" | wc -l`;
$outbound =~ s/ +//;
chop($outbound);
print("$outbound\n");
