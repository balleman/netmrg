#!/usr/bin/perl

($ip, $community, $oid) = @ARGV;

$data = `/usr/bin/snmpget $ip $community $oid -O v`;

$data =~ s/.* //;

print($data);