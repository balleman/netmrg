#!/usr/bin/perl

#This script produces output for MRTG based on DF outputs.

$device = $ARGV[0];

open(DF, "df --block-size=1 $device |");
<DF>;
$input = <DF>;
close(DF);

$device =~ s/\//\\\//g;

$input =~ s/$device +//;
$input =~ s/  +/ /;
$input =~ s/  +/ /;
$input =~ s/  +/ /;
@values = split(/ /, $input);

print("$values[2]\n");
