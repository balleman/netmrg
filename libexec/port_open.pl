#!/usr/bin/perl

# Return 0 if successful, 1 if failure

($ip, $port) = @ARGV;

system("nmap -sT -p $port $ip > /tmp/port_open 2>&1");
$result = system("grep 'open' /tmp/port_open > /dev/null");
if ($result != 0) { $result = 0; } else { $result = 1; }
print(($result) . "\n");
