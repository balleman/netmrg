# Process this file with autoconf to produce a configure script.

AC_PREREQ(2.53)
AC_INIT(NetMRG,0.8)

#
# Autoconf is dumb and doesn't expand ${prefix} for this variable
# If it does this in the future, this can probably be removed
#
exec_prefix="/usr/local"

AC_REVISION($Revision: 1.4 $)
AM_INIT_AUTOMAKE
AC_PROG_MAKE_SET

AC_PREFIX_DEFAULT(/usr/local)

#
#  Run configure in directories
#
AC_CONFIG_SUBDIRS(src)

AC_CONFIG_FILES( \
	./Makefile \
	./src/include/netmrg.h \
	./www/include/config.php
)

AC_OUTPUT
