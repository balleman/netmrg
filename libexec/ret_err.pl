#!/usr/bin/perl

# Return 0 if successful, 1 if failure

#($ip) = @ARGV;

$result = system(@ARGV);

print("ERROR CODE RETURNED: $result\n\n");
