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
AC_INIT(NetMRG,0.20)
AC_REVISION($Revision:1281 $)
AC_CONFIG_SRCDIR(src/netmrg.cpp)
AC_CANONICAL_HOST
AM_INIT_AUTOMAKE
AM_CONFIG_HEADER(src/include/config.h)
AC_PROG_MAKE_SET

# We wanna use C++, not that silly C language ;)
AC_LANG(C++)

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

#
# --with configs
#

# rrdtool
AC_ARG_WITH(rrdtool,
        AC_HELP_STRING(
                [--with-rrdtool=PROGRAM],
                [location of the rrdtool program]),
        [rrdtool_prog=$withval])
AC_PATH_PROG(RRDTOOL, rrdtool, $rrdtool_prog)

if test "x$RRDTOOL" = "x"; then
	AC_MSG_ERROR([Couldn't find rrdtool. Please verify that it is installed.])
fi

# mysql
AC_ARG_WITH(mysql_config,
        AC_HELP_STRING(
                [--with-mysql_config=PROGRAM],
                [location of the mysql_config program]),
        [mysql_config_prog=$withval])
AC_PATH_PROG(MYSQL_CONFIG, mysql_config, $mysql_config_prog)

if test "x$MYSQL_CONFIG" = "x"; then
	AC_MSG_ERROR([Couldn't find mysql_config. Please verify that it is installed.])
fi

# snmp
AC_ARG_WITH(snmp-include-dir,
	AC_HELP_STRING(
		[--with-snmp-include-dir],
		[SNMP include base directory [[/usr/(local/)include]]]),
	[snmp_include_dir=$withval])
AC_ARG_WITH(snmp-lib-dir,
	AC_HELP_STRING(
		[--with-snmp-lib-dir],
		[SNMP library directory [[/usr/(local/)lib[64]]]]),
	[snmp_lib_dir=$withval])

# xml2
AC_ARG_WITH(xml2-include-dir,
	AC_HELP_STRING(
		[--with-xml2-include-dir],
		[XML2 include directory]),
	[xml2_include_dir=$withval])
AC_ARG_WITH(xml2-lib-dir,
	AC_HELP_STRING(
		[--with-xml2-lib-dir],
		[XML2 library directory]),
	[xml2_lib_dir=$withval])

# www dir
www_dir="${localstatedir}/www/${PACKAGE}"
AC_ARG_WITH(www-dir,
	AC_HELP_STRING(
		[--with-www-dir],
		[install directory for web files]),
	[www_dir=$withval])
AC_SUBST(WWWDIR, $www_dir)


#
# Special hacks for certain operating systems
#

# if host_alias is empty, ac_cv_host_alias may still have the info
if test -z "$host_alias"; then
    host_alias=$ac_cv_host_alias
fi

# Platform-specific tweaks
case $host_alias in
*solaris*)
    AC_DEFINE(HAVE_LONG_LONG_SCANF, 1, scanf supports long long)
    LDFLAGS="$LDFLAGS -L/usr/local/ssl/lib"
    LIBS="-lkstat $LIBS";;
*linux*)
    AC_DEFINE(HAVE_LONG_LONG_SCANF, 1, scanf supports long long);;
*freebsd*)
    CPPFLAGS="$CPPFLAGS -I/usr/local/include";;
*openbsd*)
    LIBS="-ldes $LIBS";;
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
		AC_MSG_RESULT(adding -Wall to CPPFLAGS.)
		CPPFLAGS="$CPPFLAGS -Wall"
	  fi],
	AC_MSG_RESULT(no))


### Checks for libraries.
AC_CHECK_LIB(intl, bindtextdomain)
AC_CHECK_LIB(dl, dlopen)
AC_CHECK_LIB(z, compress)
AC_CHECK_LIB(pthread, pthread_create,
	[ CPPFLAGS="$CPPFLAGS -D_REENTRANT -D_POSIX_PTHREAD_SEMANTICS"
	  LIBS="-lpthread $LIBS" ],
	AC_CHECK_LIB(c_r, pthread_create,
		[ CPPFLAGS="$CPPFLAGS -D_THREAD_SAFE"
		  LIBS="-pthread $LIBS"],
		AC_MSG_ERROR([POSIX threads not found.])
	)
)
AC_CHECK_LIB(stdc++, main)

## Determine XML2 library path
# try and find libs without any additional libraries
AC_CHECK_LIB(xml2, xmlParseFile,
	XML2_LIBS=" ",
	XML2_LIBS=
)

# hunt through several directories trying to find the libraries
if test "x$XML2_LIBS" = "x"; then
	old_LDFLAGS="$LDFLAGS"
	for try in $xml2_lib_dir /usr/lib64 /usr/lib /usr/local/lib ; do
		LDFLAGS="$old_LDFLAGS -L$try"
		unset ac_cv_lib_xml2_xmlParseFile
		AC_CHECK_LIB(xml2, xmlParseFile,
			XML2_LIBS="-L$try",
			XML2_LIBS=
		)
		if test "x$XML2_LIBS" != "x"; then
			break;
		fi
	done
	LDFLAGS="$old_LDFLAGS $XML2_LIBS"
fi

if test "x$XML2_LIBS" = "x"; then
	AC_MSG_ERROR([XML2 libraries not found.  Use --with-xml2-lib-dir=<path>])
fi
# don't forget to add xml2 to the list of libraries
LIBS="-lxml2 $LIBS"

## Determine MySQL library path
MYSQL_LIBS=$($MYSQL_CONFIG --libs)
LIBS="$LIBS $MYSQL_LIBS"

# now that we've found the libraries, check for MySQL < 4.0
MYSQL_VERSION=$($MYSQL_CONFIG --version)
if test "x${MYSQL_VERSION//.*/}" = "x3"; then
	AC_DEFINE(OLD_MYSQL, 1, [Old MySQL version])
fi


### Checks for header files.
AC_HEADER_STDC
AC_HEADER_SYS_WAIT
AC_HEADER_TIME
AC_CHECK_HEADERS( \
	cstdio \
	cstdlib \
	string \
	list \
	unistd.h \
	netinet/in.h
)

## Determine XML2 include path
AC_MSG_CHECKING(for libxml/xmlmemory.h)

# Can we include headers using system include dirs?
AC_TRY_COMPILE([#include <libxml/xmlmemory.h>], [int a = 1;],
	XML2_INCLUDE=" ",
	XML2_INCLUDE=
)

# hunt through several directories trying to find the includes
if test "x$XML2_INCLUDE" = "x"; then
	old_CPPFLAGS="$CPPFLAGS"
	for i in $xml2_include_dir /usr/include /usr/local/include /usr/include/libxml2 /usr/local/include/libxml2 ; do
		CPPFLAGS="$old_CPPFLAGS -I$i"
		AC_TRY_COMPILE([#include <libxml/xmlmemory.h>], [int a = 1;],
			XML2_INCLUDE="-I$i",
			XML2_INCLUDE=
		)
		if test "x$XML2_INCLUDE" != "x"; then
			break;
		fi
	done
	CPPFLAGS="$old_CPPFLAGS $XML2_INCLUDE"
fi

if test "x$XML2_INCLUDE" = "x"; then
	AC_MSG_RESULT(no) 
	AC_MSG_ERROR([XML2 headers not found.  Use --with-xml2-include-dir=<path>])
fi
AC_MSG_RESULT(yes) 

## Determine MySQL include path
MYSQL_INCLUDE=$($MYSQL_CONFIG --cflags)
CPPFLAGS="$CPPFLAGS $MYSQL_INCLUDE"


### Checks for typedefs, structures, and compiler characteristics.
#AC_CHECK_TYPES([long long])
AC_C_CONST
AC_TYPE_SIZE_T


### Checks for library functions.
AC_TYPE_SIGNAL
AC_FUNC_MEMCMP
AC_FUNC_STAT
AC_CHECK_FUNCS( \
	memmove \
	llround \
	setenv
)
AC_SEARCH_LIBS(nanosleep, rt posix4,, AC_ERROR([nanosleep function not found.]))

# Determine UCD or Net-SNMP include path
if test "x$snmp_include_dir" != "x"; then
  for i in / /ucd-snmp /include/ucd-snmp; do
    test -f $snmp_include_dir/$i/snmp.h && SNMP_INCDIR=$snmp_include_dir/$i
  done
  for i in / /net-snmp /include/net-snmp; do
    test -f $snmp_include_dir/$i/net-snmp-config.h && SNMP_INCDIR=$snmp_include_dir/$i
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
  test -f $snmp_lib_dir/libsnmp.a -o -f $snmp_lib_dir/libsnmp.so -o -f $snmp_lib_dir/libsnmp.dylib && SNMP_LIBDIR=$snmp_lib_dir
  test -f $snmp_lib_dir/libnetsnmp.a -o -f $snmp_lib_dir/libnetsnmp.so -o -f $snmp_lib_dir/libnetsnmp.dylib && SNMP_LIBDIR=$snmp_lib_dir
else
  for i in /usr/local/lib /usr/sfw/lib /usr/lib /usr/lib64 ; do
    test -f $i/libsnmp.a -o -f $i/libsnmp.so -o -f $i/libsnmp.dylib && SNMP_LIBDIR=$i
    test -f $i/libnetsnmp.a -o -f $i/libnetsnmp.so -o -f $i/libnetsnmp.dylib && SNMP_LIBDIR=$i
  done
fi

if test -z "$SNMP_INCDIR"; then
  if test "x$snmp_include_dir" != "x";then
    AC_MSG_ERROR(Cannot find SNMP headers under $snmp_include_dir)
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

# Internationalization support
AM_GNU_GETTEXT([external])

#
#  Run configure in directories
#
#AC_CONFIG_SUBDIRS(src)

AC_CONFIG_FILES( \
	Makefile \
	subst \
	bin/rrdedit \
	etc/Makefile \
	etc/cron.d-netmrg \
	etc/init.d-netmrg \
	etc/netmrg.conf \
	etc/netmrg.xml \
	m4/Makefile \
	po/Makefile.in \
	src/Makefile \
	src/include/netmrg.h \
	var/Makefile \
	var/lib/Makefile \
	var/lib/rrd/Makefile \
	www/Makefile \
	www/include/config.php

)

AC_OUTPUT

perl subst etc/cron.d-netmrg
perl subst etc/init.d-netmrg
perl subst etc/netmrg.conf
perl subst etc/netmrg.xml
perl subst src/include/netmrg.h
perl subst src/include/config.h
perl subst www/Makefile

