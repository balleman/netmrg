#!/usr/bin/perl

# given a monitor #, dump it, vi it, and restore it.

$id = $ARGV[0];

# make a backup
system("cp /var/www/netmrg/rrd/mon_$id.rrd /var/www/netmrg/rrd/mon_$id.rrd.autobackup");

# dump it to temp
system("rrdtool dump /var/www/netmrg/rrd/mon_$id.rrd > /tmp/mon_$id.xml");

# edit the xml
system("vi /tmp/mon_$id.xml");

# restore the rrd
system("rrdtool restore /tmp/mon_$id.xml /var/www/netmrg/rrd/mon_$id.rrd");
