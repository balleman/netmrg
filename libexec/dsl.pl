#!/usr/bin/perl

$count = `/var/www/netmrg/cmd/test/snmp.pl chm3.rtr.pa.net public interfaces.ifNumber.0`;
if ($count > 0) { $count = $count - 14; }
print($count);
print("\n");
