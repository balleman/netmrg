#!/usr/bin/perl

# Return average ping time 

($ip) = @ARGV;

@result = `ping -n -c 6 -i 0.2 -w 3 $ip 2>/dev/null`;
$result = $result[$#result];
$result =~ s/.*= //;
$result =~ s/\/.*//;
if ($result =~ /errors/)
{
	print("U\n");
} else {
	print($result);
}
