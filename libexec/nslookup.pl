#!/usr/bin/perl

# Return 0 if successful, 1 if failure

($name, $ip) = @ARGV;

system("nslookup $name $ip > /tmp/nslookup 2>&1");
$result = system("grep 'No response' /tmp/nslookup");

if ($result = 0) { $result = 1; } else { $result = 0; }

exit $result;
