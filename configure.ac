# Process this file with autoconf to produce a configure script.

AC_PREREQ(2.53)
AC_INIT(NetMRG,0.8)

AC_REVISION($Revision: 1.2 $)
AM_INIT_AUTOMAKE
AC_PROG_MAKE_SET

AC_PREFIX_DEFAULT(/var/www/netmrg)

#
#  Run configure in directories
#
AC_CONFIG_SUBDIRS(src)

AC_CONFIG_FILES( \
	./Makefile \
	./include/netmrg.h \
	./include/config.php
)

AC_OUTPUT
