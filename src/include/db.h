/********************************************
* NetMRG Integrator
*
* db.h
* NetMRG Gatherer Database Library Header
*
* see doc/LICENSE for copyright information
********************************************/

#ifndef NETMRG_DB
#define NETMRG_DB

#include "config.h"
#include <mysql.h>
#include "types.h"

int				db_connect(MYSQL *connection);
MYSQL_RES *		db_query(MYSQL *mysql, DeviceInfo *info, string query);
void			db_update(MYSQL *mysql, DeviceInfo *info, string query);

#endif
