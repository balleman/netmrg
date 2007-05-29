#!/bin/sh

# Author: Uwe Steinmann <uwe@steinmann.cx>

# usage linuxpmu.sh <battery number> <command>
#   battery number can be 0 or 1
#   command can be 'charge', 'max_charge', 'current', 'voltage'

if [ -f /proc/pmu/battery_$1 ]; then
	cat /proc/pmu/battery_$1 | grep "^$2 *:" | awk -F: '{print $2}'
else
	echo "U"
fi
