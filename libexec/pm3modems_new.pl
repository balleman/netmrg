#!/usr/bin/perl

$count = `/var/www/netmrg/cmd/test/snmp.pl $ARGV[0] public interfaces.ifNumber.0`;
if ($count > 0) { $count--; }
print($count);
print("\n");
