/*

   NetMRG Misc Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

*/

long long int get_snmp_uptime(DeviceInfo info)
{
        string uptime;
        char unparsed[100];
        char * parsed;

        uptime = snmpget(info, string("system.sysUpTime.0"));

        if (uptime != "")
        {
                strcpy(unparsed,uptime.c_str());
                parsed = strtok(unparsed, "()");
                return strtoint(string(parsed));
        } else {
                return 0;
        }

} // end getuptime

void logevent(string dev_name, string event_text, int situation, long long int time_since_last_change)
{

        do_mysql_update(string("INSERT INTO event_log SET dev_name='") + dev_name +
                        string("', date=") + inttostr(time(NULL)) + string(", event_text='") + event_text + string("'") +
                        string(", since_last_change=") + inttostr(time_since_last_change) +
                        string(", situation=") + inttostr(situation));

} // end logevent 

// snmp_recache - given a device id, perform a recache operation
void snmp_recache(int device_id)
{
        string command;
        command = string(NETMRG_ROOT) + string("bin/recache.php ") + inttostr(device_id) + string(" > /dev/null");
        system(command.c_str());

} // end snmp_recache

// get_snmp_index_value - translate info into index using lookup table
string get_snmp_index_value(string table, string index, string type, string value, string device)
{

        MYSQL mysql;
        MYSQL_RES *res;
        MYSQL_ROW row;
        string query, ret;
        char temp[250];
        ret = "-1";
pthread_mutex_lock(&mysql_lock);
    if (!(mysql_connect(&mysql,"localhost","netmrgwrite","netmrgwrite")))
        exiterr(1);
pthread_mutex_unlock(&mysql_lock);
    if (mysql_select_db(&mysql,"netmrg"))
        exiterr(2);
        query = "SELECT " + index + " FROM " + table + " WHERE dev_id=";
        query += device;
        query += " AND ";
        query += type;
        query += "=\"";
        query += value;
        query += "\"";
        if (!(mysql_query(&mysql,query.c_str()))) {
            if (res = mysql_store_result(&mysql)) {

                                        row = mysql_fetch_row(res);
                                if (mysql_num_rows(res) > 0) {
                                        ret = row[0];
                                        }
                                        mysql_free_result(res);

                                        }
                                        }

            mysql_close(&mysql);
                return ret;

}

