#!/usr/bin/perl

$info = `mysql -u root -psqlroot -e "SHOW STATUS" | grep Connections`;

$info =~ s/Connections//;
$info =~ s/\t//;

print("$info");
