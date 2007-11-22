#!/bin/sh
#
# snmpdiff.sh
#
# takes in arguments and outputs diff of OIDs
#

if (( $# < 4 )); then
	echo "Usage: $0 <hostname> <snmp_community> <OID1> <OID2> [... <OIDN>]";
	exit 1;
fi;

hostname="$1"
community="$2"
shift
shift

snmpget="snmpget -v1 -c $community -OqvU $hostname"
val=`$snmpget $1`;
shift

for i in "$@"; do
	val=$(($val - `$snmpget $i`));
done

if (( $val != 0 )); then echo $val; else echo U; fi

