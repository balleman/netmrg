/*

   NetMRG Database Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

*/

// exiterr - indicate a mysql problem
void exiterr(int exitcode)
{
         fprintf(stderr , "[MySQL ERROR]\n");
	 pthread_exit( NULL );
}



// do_mysql_update - perform a single mysql update query
void do_mysql_update(string query)
{

        MYSQL mysql;
        MYSQL_RES *res;
        MYSQL_ROW row;

	pthread_mutex_lock(&mysql_lock);
        if (!(mysql_connect(&mysql,"localhost","netmrgwrite","netmrgwrite")))
        exiterr(1);
	pthread_mutex_unlock(&mysql_lock);

        if (mysql_select_db(&mysql,"netmrg"))
        exiterr(2);

        mysql_query(&mysql,query.c_str());

        mysql_close(&mysql);

}


