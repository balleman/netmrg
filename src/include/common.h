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

#ifndef HAVE_LIBPTHREAD
# define HAVE_LIBPTHREAD 0
#else
# include <pthread.h>
#endif

#if !HAVE_MEMMOVE
#  define memmove(d, s, n) memcpy ((d), (s), (n))
#endif

#endif /* NETMRG_COMMON_H */

