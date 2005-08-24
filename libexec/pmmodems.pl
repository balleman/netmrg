#!/usr/bin/perl -w
# Copyright (c) 2004, Ben Winslow <rain bluecherry net>
#
# Released under the MIT (aka X11) License:
# 
# Permission is hereby granted, free of charge, to any person obtaining a
# copy of this software and associated documentation files (the "Software"),
# to deal in the Software without restriction, including without limitation
# the rights to use, copy, modify, merge, publish, distribute, sublicense,
# and/or sell copies of the Software, and to permit persons to whom the
# Software is furnished to do so, subject to the following conditions:
# 
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
# THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
# FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
# DEALINGS IN THE SOFTWARE.

# Abstract:
# 	Tallies the number of active modems on a Livingston Portmaster.
#	Modems in the 'active' and 'connecting' state are counted.
#	Tested and working on PM3s running ComOS 3.9.1c1.
# Usage:
#	pmmodems.pl <community> <address>

use Net::SNMP;

sub get_users
{
	my ($comm, $ip) = @_;
	my ($numusers) = 0;
	my ($baseoid, $res, $k);
	my ($snmp, $err) = Net::SNMP->session(
		-hostname => $ip,
		-community => $comm,
		-version => 1
	);
	
	die $err if ($err);
	
	# enterprises.livingston.livingstonMib.livingstonInterfaces.livingstonModem.livingstonModemTable.livingstonModemEntry.livingstonModemStatus
	$baseoid = ".1.3.6.1.4.1.307.3.2.3.1.1.3";
	
	$res = $snmp->get_table($baseoid);

	if (defined($res)) {
		# use oid_lex_sort for properly sorted output if hacking
		# up this script.  it doesn't matter here.
		foreach $k (keys %$res) {
			# none(1), bound(2), connecting(3),
			# active(4), test(5), down(6), ready(7),
			# halt(8), admin(9)
			$numusers++ if ($res->{$k} == 4 || $res->{$k} == 3);
		}
	} else {
		$numusers = -1;
	}
	
	$snmp->close();

	return $numusers;
}

my ($users);

die "Usage: $0 <community> <addr>\n" if (@ARGV != 2);

$users = get_users($ARGV[0], $ARGV[1]);
print "$users\n" if ($users != -1);
