#!/usr/bin/perl

# Return 0 if successful, 1 if failure

($ip) = @ARGV;

$result = system("ping -n -c 2 -i 0.03 -w 3 $ip > /dev/null");

if ($result != 0) { $result = 1; }

exit $result;
