/*

   NetMRG Misc Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

*/

void U_to_NULL(string *input)
{
	if ((*input) == "U")
	{
		(*input) = "NULL";
	}
	else
	{
		(*input) = string("'") + (*input) + string("'");
	}
}

long long int get_snmp_uptime(DeviceInfo info)
{
        string uptime;
        char unparsed[100];
        char * parsed;

        uptime = snmp_get(info, string("system.sysUpTime.0"));

        if (uptime != "")
        {
                strcpy(unparsed,uptime.c_str());
                parsed = strtok(unparsed, "()");
                return strtoint(string(parsed));
        }
	else
	{
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
