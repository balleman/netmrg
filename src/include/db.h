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

#include "common.h"
#include "types.h"
#include <mysql/mysql.h>

int				db_connect(MYSQL *connection);
MYSQL_RES *		db_query(MYSQL *mysql, DeviceInfo *info, string query);
void			db_update(MYSQL *mysql, DeviceInfo *info, string query);
string			db_escape(const string & input);

#endif
