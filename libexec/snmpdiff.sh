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

snmpwalk="snmpwalk -v1 -c $community -Oqv $hostname"
val=`$snmpwalk $1`;
shift

echo $val;
for i in "$@"; do
	val=$(($val - `$snmpwalk $i`));
	echo $val;
done
