#ifndef NETMRG_COMMON_H
#define NETMRG_COMMON_H 1

#include <netmrg.h>

#if HAVE_CONFIG_H
#  include "config.h"
#endif

#include <stdio.h>

#if HAVE_UNISTD_H
#  include <sys/types.h>
#  include <unistd.h>
#endif

#if TIME_WITH_SYS_TIME
# include <sys/time.h>
# include <time.h>
#else
# if HAVE_SYS_TIME_H
#  include <sys/time.h>
# else
#  include <time.h>
# endif
#endif

#include <pthread.h>

#if !HAVE_MEMMOVE
#  define memmove(d, s, n) memcpy ((d), (s), (n))
#endif

#if HAVE_NETINET_IN_H
#  include <netinet/in.h>
#endif

#include "gettext.h"
#define _(String) gettext(String)

#if HAVE_SYS_WAIT_H
# include <sys/wait.h>
#endif

#endif /* NETMRG_COMMON_H */

