# Process this file with autoconf to produce a configure script.

AC_PREREQ(2.53)
AC_INIT(NetMRG,0.10pre1)

#
# Autoconf is dumb and doesn't expand ${prefix} for this variable
# If it does this in the future, this can probably be removed
#
if test "x$exec_prefix" = "xNONE"; then
	if test "x$prefix" = "xNONE" ; then
		exec_prefix="/usr/local"
	else
		exec_prefix="$prefix"
	fi
	ac_configure_args="$ac_configure_args --exec-prefix=$exec_prefix"
fi

AC_REVISION($Revision: 1.12 $)
AM_INIT_AUTOMAKE
AC_PROG_MAKE_SET

AC_PATH_PROG(RRDTOOL, rrdtool)

if test "x$RRDTOOL" = "x"; then
	AC_MSG_ERROR([Couldn't find rrdtool. Please verify that it is installed.])
fi

#
#  Run configure in directories
#
AC_CONFIG_SUBDIRS(src)

AC_CONFIG_FILES( \
	Makefile \
	bin/netmrg_cron.sh \
	etc/Makefile \
	etc/netmrg.conf \
	etc/netmrg.xml \
	src/include/netmrg.h \
	www/Makefile \
	www/include/config.php
)

AC_OUTPUT
