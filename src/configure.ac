# Process this file with autoconf to produce a configure script.

#############################################################
#
#  Autoconf requirements
#  AC_INIT(package, version, bug-report-address)
#  information on the package
#  checks for programs
#  checks for libraries
#  checks for header files
#  checks for types
#  checks for structures
#  checks for compiler characteristics
#  checks for library functions
#  checks for system services
#  AC_CONFIG_FILES([file...])
#  AC_OUTPUT
#
#############################################################

AC_PREREQ(2.53)
AC_INIT(NetMRG,src,[],[])
AC_REVISION($Revision: 1.2 $)
AC_CONFIG_SRCDIR(netmrg.cpp)
AC_CANONICAL_HOST

AM_INIT_AUTOMAKE
AM_CONFIG_HEADER(config.h)

# We wanna use C++, not that silly C language
AC_LANG(C++)


### --with configs
# mysql
AC_ARG_WITH(mysql-include-dir,
	AC_HELP_STRING(
		[--with-mysql-include-dir],
		[MySQL include directory [[/usr/include/mysql]]]),
	[mysql_include_dir=$withval],
	[mysql_include_dir="/usr/include/mysql"])

AC_ARG_WITH(mysql-lib-dir,
	AC_HELP_STRING(
		[--with-mysql-lib-dir],
		[MySQL library directory [[/usr/lib/mysql]]]),
	[mysql_lib_dir=$withval],
	[mysql_lib_dir="/usr/lib/mysql"])

LDFLAGS="$LDFLAGS -L$mysql_lib_dir"
CPPFLAGS="$CPPFLAGS -I$mysql_include_dir"

# snmp
AC_ARG_WITH(snmp-include-dir,
	AC_HELP_STRING(
		[--with-snmp-include-dir],
		[SNMP include base directory [[/usr/(local/)include]]]),
	[snmp_inc_base_dir=$withval])

AC_ARG_WITH(snmp-lib-dir,
	AC_HELP_STRING(
		[--with-snmp-lib-dir],
		[SNMP library directory [[/usr/(local/)lib]]]),
	[snmp_lib_dir=$withval])


# if host_alias is empty, ac_cv_host_alias may still have the info
if test -z "$host_alias"; then
    host_alias=$ac_cv_host_alias
fi

# Platform-specific tweaks
case $host_alias in
*solaris*)
    AC_DEFINE(HAVE_LONG_LONG_SCANF, 1, scanf supports long long)
    CPPFLAGS="$CPPFLAGS -D_POSIX_PTHREAD_SEMANTICS";;
*linux*)
    AC_DEFINE(HAVE_LONG_LONG_SCANF, 1, scanf supports long long);;
*freebsd*)
    LIBS="$LIBS -pthread"
    AC_DEFINE(HAVE_LIBPTHREAD, 1);;
esac


### Checks for programs.
AC_PROG_CXX
AC_PROG_CPP
AC_PROG_INSTALL

AC_MSG_CHECKING([whether to enable -Wall])
AC_ARG_ENABLE(warnings,
	AC_HELP_STRING(
		[--enable-warnings],
		[Enable -Wall if using g++]),
	[ if test -n "$GXX"; then
		AC_MSG_RESULT(adding -Wall to CXXFLAGS.)
		CXXFLAGS="$CXXFLAGS -Wall"
	  fi],
	AC_MSG_RESULT(no))


### Checks for libraries.
AC_CHECK_LIB(dl, dlopen)
AC_CHECK_LIB(z, compress)
AC_CHECK_LIB(pthread, pthread_exit)
AC_CHECK_LIB(stdc++, main)
AC_CHECK_LIB(mysqlclient, mysql_init,,
	AC_MSG_ERROR([MySQL libraries not found.]))
AC_CHECK_LIB(mysqlclient, mysql_thread_init, [],
	AC_DEFINE(OLD_MYSQL, 1, [Old MySQL version]))


### Checks for header files.
AC_HEADER_STDC
AC_HEADER_SYS_WAIT
AC_HEADER_TIME
AC_CHECK_HEADERS( \
	cstdio \
	cstdlib \
	string \
	list \
	unistd.h
)


### Checks for typedefs, structures, and compiler characteristics.
#AC_CHECK_TYPES([long long])
AC_C_CONST
AC_TYPE_SIZE_T

### Checks for library functions.
AC_TYPE_SIGNAL
AC_FUNC_MEMCMP
AC_FUNC_STAT
AC_CHECK_FUNCS(memmove)


# Determine UCD or Net-SNMP include path
if test "x$snmp_inc_base_dir" != "x"; then
  for i in / /ucd-snmp /include/ucd-snmp; do
    test -f $snmp_inc_base_dir/$i/snmp.h && SNMP_INCDIR=$snmp_inc_base_dir/$i
  done
  for i in / /net-snmp /include/net-snmp; do
    test -f $snmp_inc_base_dir/$i/net-snmp-config.h && SNMP_INCDIR=$snmp_inc_base_dir/$i
  done
else
  for i in /usr/local/include /usr/include; do
    test -f $i/snmp.h && SNMP_INCDIR=$i
    test -f $i/ucd-snmp/snmp.h && SNMP_INCDIR=$i/ucd-snmp
    test -f $i/net-snmp/net-snmp-config.h && SNMP_INCDIR=$i/net-snmp
    test -f $i/snmp/snmp.h && SNMP_INCDIR=$i/snmp
    test -f $i/snmp/include/ucd-snmp/snmp.h && SNMP_INCDIR=$i/snmp/include/ucd-snmp
    test -f $i/snmp/include/net-snmp/net-snmp-config.h && SNMP_INCDIR=$i/snmp/include/net-snmp
  done
fi

# Determine UCD or Net-SNMP library path
if test "x$snmp_lib_dir" != "x"; then
  test -f $snmp_lib_dir/libsnmp.a -o -f $snmp_lib_dir/libsnmp.so && SNMP_LIBDIR=$snmp_lib_dir
  test -f $snmp_lib_dir/libnetsnmp.a -o -f $snmp_lib_dir/libnetsnmp.so && SNMP_LIBDIR=$snmp_lib_dir
else
  for i in /usr/local/lib /usr/lib; do
    test -f $i/libsnmp.a -o -f $i/libsnmp.so && SNMP_LIBDIR=$i
    test -f $i/libnetsnmp.a -o -f $i/libnetsnmp.so && SNMP_LIBDIR=$i
  done
fi

if test -z "$SNMP_INCDIR"; then
  if test "x$snmp_inc_base_dir" != "x";then
    AC_MSG_ERROR(Cannot find SNMP headers under $snmp_inc_base_dir)
  else
    AC_MSG_ERROR(Cannot find SNMP headers.  Use --with-snmp-include-dir= to specify non-default path.)
  fi
fi

if test -z "$SNMP_LIBDIR"; then
  if test "x$snmp_lib_dir" != "x";then
    AC_MSG_ERROR(Cannot find SNMP libraries under $snmp_lib_dir)
  else
    AC_MSG_ERROR(Cannot find SNMP libraries.  Use --with-snmp-lib-dir= to specify non-default path.)
  fi
fi

LDFLAGS="$LDFLAGS -L$SNMP_LIBDIR"
CPPFLAGS="$CPPFLAGS -I$SNMP_INCDIR"

# Net/UCD-SNMP includes v3 support and insists on crypto unless 
# compiled --without-openssl
SNMP_SSL=no
AC_MSG_CHECKING([if Net-SNMP needs crypto support])
AC_TRY_COMPILE([#include <net-snmp-config.h>],
	[exit(USE_OPENSSL != 1);],
	[AC_MSG_RESULT(yes)
	 SNMP_SSL=yes ],
	AC_MSG_RESULT(no))
AC_MSG_CHECKING([if UCD-SNMP needs crypto support])
AC_TRY_COMPILE([#include <ucd-snmp-config.h>],
	[exit(USE_OPENSSL != 1);],
	[AC_MSG_RESULT(yes)
	 SNMP_SSL=yes ],
	AC_MSG_RESULT(no))

if test "$SNMP_SSL" = "yes"; then
  AC_CHECK_LIB(crypto, CRYPTO_free,,AC_MSG_ERROR(libcrypto not found!))
fi

HAVE_NET_SNMP=no
AC_CHECK_LIB(netsnmp, snmp_timeout,
	[LIBS="-lnetsnmp $LIBS"
	 AC_DEFINE(HAVE_NET_SNMP, 1, Net-SNMP Version) 
	 HAVE_NET_SNMP=yes ],
	AC_MSG_RESULT(Cannot find Net-SNMP libraries...checking for UCD-SNMP))

if test "$HAVE_NET_SNMP" = "no"; then
  AC_CHECK_LIB(snmp, snmp_timeout,,
	AC_MSG_ERROR(Cannot find UCD-SNMP libraries))
fi

AC_CONFIG_FILES(Makefile)
AC_OUTPUT

