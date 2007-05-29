#!/bin/sh

# Author: Uwe Steinmann <uwe@steinmann.cx>

# usage linuxload.sh <item>
#   item can be 1, 2, or 3

if [ -f /proc/loadavg -a "$1" != "" -a $1 -ge 1 -a $1 -le 3 ]; then
	cut -d' ' -f$1 /proc/loadavg
else
	echo "U"
fi
