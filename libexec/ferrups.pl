#!/usr/bin/perl

($name, $id) = @ARGV;

if ($id eq "input-volts")    { $id = 1; }
if ($id eq "output-volts")   { $id = 2; }
if ($id eq "input-amps")     { $id = 3; }
if ($id eq "output-amps")    { $id = 4; }
if ($id eq "output-va")	     { $id = 5; }
if ($id eq "batt-amps")      { $id = 6; }
if ($id eq "batt-volts")     { $id = 7; }
if ($id eq "temp-ambient")   { $id = 11; }
if ($id eq "temp-housing")   { $id = 12; }

$result = `head -n $id /var/www/netmrg/dat/ups.$name 2>/dev/null | tail -n 1`;

$result =~ s/\n//;

if ($result eq "") { $result = "U"; }

print("$result\n");
