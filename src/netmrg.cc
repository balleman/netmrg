/*
   
   NetMRG Monitoring Procedure 
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

   MySQL examples from http://mysql.turbolift.com/mysql/chapter4.php3
   pthreads examples from http://www.math.arizona.edu/swig/pthreads/threads.html
   net-snmp examples from http://net-snmp.sf.net/
   Thanks to Patrick Haller (http://haller.ws) for helping to debug threading.

*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <mysql.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <pthread.h>
#include <string.h>
#include <string>
#include <ucd-snmp/ucd-snmp-config.h>
#include <ucd-snmp/ucd-snmp-includes.h>
#include <ucd-snmp/system.h>
#include <getopt.h>
#include <snmp_parse_args.h>
#include <errno.h>
#include <ucd-snmp/mib.h>
#include <slist.h>

#ifdef __linux__
	#define _REENTRANT
	#define _POSIX_SOURCE
	#define _P __P
#endif

#define NETMRG_ROOT 		"/var/www/netmrg/"
#define RRDTOOL 		"/usr/bin/rrdtool - "

#define NTHREADS 		8 			// number of simultaneous threads
#define THREAD_SLEEP 		150000			// number of microseconds between thread checks

// MySQL Credentials
#define MYSQL_HOST		"localhost"
#define MYSQL_USER		"netmrgwrite"
#define MYSQL_PASS		"netmrgwrite"
#define MYSQL_DB		"netmrg"

// Define the command that distributes reports
#define DISTRIB_CMD "/var/www/netmrg/bin/distrib.sh"

// RRDTOOL Pipe
FILE *rrdtool_pipe;

int active_threads = 0;

// Create mutex locks
pthread_mutex_t active_threads_lock 	= PTHREAD_MUTEX_INITIALIZER;
pthread_mutex_t mysql_lock 		= PTHREAD_MUTEX_INITIALIZER;
pthread_mutex_t snmp_lock 		= PTHREAD_MUTEX_INITIALIZER;
pthread_mutex_t rrdtool_lock 		= PTHREAD_MUTEX_INITIALIZER;

// structs

struct ValuePair
{
        string name;
	string value;
	
	ValuePair(string setname, string setvalue)
	{
		name 	= setname;
		value   = setvalue;
	}
};

struct DeviceInfo
{
	int device_id;
	int subdevice_id;
	int monitor_id;
	int event_id;
	int response_id;

	int snmp_avoid;
	int snmp_recache;
	int snmp_ifnumber;
	long long int snmp_uptime;

	int subdevice_type;

	int test_type;
	int test_id;

	string name;
       	string ip;
	string snmp_read_community;
	string test_params;
	string curr_val;
	string last_val;
	string delta_val;

	slist<ValuePair> parameters;

	DeviceInfo()
	{
		device_id 		= -1;
		subdevice_id		= -1;
		monitor_id		= -1;
		event_id		= -1;
		response_id		= -1;

		snmp_avoid		=  0;
		snmp_recache		=  0;
		snmp_ifnumber		=  0;
		snmp_uptime		=  0;

		subdevice_type		=  0;

		test_type		= -1;
		test_id			= -1;
		//test_params		= "";

		curr_val		= "U";
		last_val		= "U";
		delta_val		= "U";

	}
};

struct RRDInfo
{
	string max_val;
	string min_val;
	
	int tuned;
	
	string value;

	string data_type;

	RRDInfo()
	{
		max_val 	= "U";
		min_val 	= "U";
		tuned   	=   0;
		data_type 	= "";
	}
};
		
	
// Include the NetMRG Libraries
#include <netmrg-utils.cc>
#include <netmrg-snmp.cc>
#include <netmrg-db.cc>
#include <netmrg-misc.cc>

int file_exists(string filename)
{
	struct stat file_stat;

	stat(filename.c_str(), &file_stat);

	return S_ISREG(file_stat.st_mode);
}

void rrd_cmd(DeviceInfo info, string cmd)
{
	debuglogger(DEBUG_RRD, &info, "RRD: '" + cmd + "'");
	cmd = " " + cmd + "\n";

	pthread_mutex_lock(&rrdtool_lock);
	fprintf(rrdtool_pipe, cmd.c_str());
	pthread_mutex_unlock(&rrdtool_lock);
}

string get_rrd_file(string mon_id)
{
	string filename = string(NETMRG_ROOT) + "rrd/mon_" + mon_id + ".rrd";
	return filename;
}

void create_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command;

	command = "create " + get_rrd_file(inttostr(info.monitor_id)) + 
			" DS:mon_" + inttostr(info.monitor_id) + ":" + rrd.data_type + 
			":600:" + rrd.min_val + ":" + rrd.max_val + " " +
			"RRA:AVERAGE:0.5:1:600 " +
			"RRA:AVERAGE:0.5:6:700 " 	+
			"RRA:AVERAGE:0.5:24:775 " 	+
			"RRA:AVERAGE:0.5:288:797 " 	+
			"RRA:LAST:0.5:1:600 " 		+
			"RRA:LAST:0.5:6:700 " 		+
			"RRA:LAST:0.5:24:775 " 		+
			"RRA:LAST:0.5:288:797 " 	+
			"RRA:MAX:0.5:1:600 " 		+ 
			"RRA:MAX:0.5:6:700 "		+
			"RRA:MAX:0.5:24:775 "		+
			"RRA:MAX:0.5:288:797";
	rrd_cmd(info, command);
}

void tune_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "tune " + get_rrd_file(inttostr(info.monitor_id)) + " -a mon_" +
	       			inttostr(info.monitor_id) + ":" + rrd.max_val +
				" -i mon_" + inttostr(info.monitor_id) + ":" + rrd.min_val;
	rrd_cmd(info, command);
}

void update_rrd(DeviceInfo info, RRDInfo rrd)
{
	string command = "update " + get_rrd_file(inttostr(info.monitor_id)) + " N:" + stripnl(info.curr_val);
	rrd_cmd(info, command);
}	
		
// update_monitor_rrd - given information, update a round robin database
void update_monitor_rrd(DeviceInfo info, RRDInfo rrd) 
{
	if (!(file_exists(get_rrd_file(inttostr(info.monitor_id)))))
	{
		create_rrd(info, rrd);
	}

	if (rrd.tuned == 0)
	{
		tune_rrd(info, rrd);
	}

	update_rrd(info, rrd);
}

MYSQL db_connect(MYSQL connection)
{
	pthread_mutex_lock(&mysql_lock);

	if (!(mysql_connect(&connection,MYSQL_HOST,MYSQL_USER,MYSQL_PASS)))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Connection Failure.");
		pthread_exit(NULL);
	}
	pthread_mutex_unlock(&mysql_lock);

	if (mysql_select_db(&connection,MYSQL_DB))
	{
		debuglogger(DEBUG_MYSQL, NULL, "MySQL Database Selection Failure.");
		pthread_exit(NULL);
	}

	return connection;
}

MYSQL_RES *db_query(MYSQL *mysql, DeviceInfo *info, string query)
{
	MYSQL_RES *mysql_res;

	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, info, "MySQL Query Failed (" + query + ")");
		pthread_exit(NULL);
	}

	if (!(mysql_res = mysql_store_result(mysql)))
	{
		debuglogger(DEBUG_MYSQL, info, "MySQL Store Result failed.");
		pthread_exit(NULL);
	}

	return mysql_res;
}

void db_update(MYSQL *mysql, DeviceInfo *info, string query)
{
	if (mysql_query(mysql, query.c_str()))
	{
		debuglogger(DEBUG_MYSQL, info, "MySQL Query Failed (" + query + ")");
	}
}
	
void do_snmp_recache(int dev_id)
{

}

void update_monitor_db(DeviceInfo info, MYSQL *mysql, RRDInfo rrd)
{

	if (info.curr_val == "U")
	{
		info.curr_val = "NULL";
	}

	if (info.delta_val == "U")
	{
		info.delta_val = "NULL";
	}


	db_update(mysql, &info, "UPDATE monitors SET tuned=1, last_val=" + info.curr_val +
		", delta_val=" + info.delta_val + ", delta_time=UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_time), last_time=NOW() WHERE id="
		+ inttostr(info.monitor_id));

}

void setup_interface_parameters(DeviceInfo *info, MYSQL *mysql)
{

        // This function examines the parameters for the subdevice and determines if any
	// are to be used as SNMP index values.  If so, it adds parameters with all available
	// information from the snmp_cache, so that things like %ifIndex% and %ifName% in monitors
	// will get expanded into the correct values when the monitors are processed.

	string          index   = "";
	string          value   = "";

	MYSQL_RES       *mysql_res;
	MYSQL_ROW       mysql_row;

	for (slist<ValuePair>::iterator current = (*info).parameters.begin(); current != (*info).parameters.end(); current++)
	{
		value = (*current).value;

		if ((*current).name == "ifIndex")
		{
			index = "if_index";
                	break;
		}
		else
		if ((*current).name == "ifName")
		{
			index = "if_name";
			break;
		}
		else
		if ((*current).name == "ifDescr")
		{
			index = "if_desc";
			break;
		}
		else
		if ((*current).name == "ifAlias")
		{
			index = "if_alias";
			break;
		}
		else
		if ((*current).name == "ifIP")
		{
			index = "if_ip";
			break;
		}

	} // end for each parameter

	if (index == "")
	{
		debuglogger(DEBUG_SUBDEVICE, info, "Interface subdevice has no interface parameters.");
	}
	else
	{
                string query =
		string("SELECT if_index, if_name, if_ip, if_desc, if_alias FROM snmp_cache WHERE dev_id=") +
		inttostr((*info).device_id) + string(" AND ") + index + "=\"" + value + "\"";

                mysql_res = db_query(mysql, info, query);

		if (mysql_num_rows(mysql_res) > 0)
		{
		        mysql_row = mysql_fetch_row(mysql_res);

		        if ((mysql_row[0] != NULL) && (index != "if_index"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifIndex", mysql_row[0]));
		        }

		        if ((mysql_row[1] != NULL) && (index != "if_name"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifName", mysql_row[1]));
		        }

		        if ((mysql_row[2] != NULL) && (index != "if_ip"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifIP", mysql_row[2]));
		        }

		        if ((mysql_row[3] != NULL) && (index != "if_desc"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifDescr", mysql_row[3]));
		        }

		        if ((mysql_row[4] != NULL) && (index != "if_alias"))
		        {
        		        (*info).parameters.push_front(ValuePair("ifAlias", mysql_row[4]));
		        }
		}
		else
		{
		        debuglogger(DEBUG_SUBDEVICE, info, "Interface index not found.");
		}

	 	mysql_free_result(mysql_res);

	}

}

void setup_disk_parameters(DeviceInfo *info, MYSQL *mysql)
{
}

string expand_parameters(DeviceInfo info, string input)
{
	for (slist<ValuePair>::iterator current = info.parameters.begin(); current != info.parameters.end(); current++)
	{
		input = token_replace(input, "%" + (*current).name + "%", (*current).value);
	}

	return input;
}

string process_sql_monitor(DeviceInfo info, MYSQL *mysql)
{
        MYSQL           test_mysql;
        MYSQL_RES       *mysql_res, *test_res;
        MYSQL_ROW       mysql_row, test_row;
        string          value = "U";

        string query =
                string("SELECT host, user, password, query, column_num FROM tests_sql WHERE id = ") +
                inttostr(info.test_id);

        mysql_res = db_query(mysql, &info, query);
        mysql_row = mysql_fetch_row(mysql_res);

        // if the sql test exists
        if (mysql_row[0] != NULL)
        {

		string test_query = expand_parameters(info, mysql_row[3]);

                debuglogger(DEBUG_GATHERER, &info, "MySQL Query Test ('" +
                        string(mysql_row[0]) + "', '" + string(mysql_row[1]) + "', '" +
                        string(mysql_row[2]) + "', '" + test_query + "', '" +
                        string(mysql_row[4]) + "')");

                pthread_mutex_lock(&mysql_lock);

	        if (!(mysql_connect(&test_mysql,mysql_row[0],mysql_row[1],mysql_row[2])))
	        {
        		pthread_mutex_unlock(&mysql_lock);
                        debuglogger(DEBUG_GATHERER, &info, "Test MySQL Connection Failure.");
        	}
                else
                {
                        pthread_mutex_unlock(&mysql_lock);

                        if (mysql_query(&test_mysql, test_query.c_str()))
	                {
		                debuglogger(DEBUG_GATHERER, &info, "Test MySQL Query Failed (" + test_query + ")");
			}
                        else
                	if (!(test_res = mysql_store_result(&test_mysql)))
	                {
		                debuglogger(DEBUG_GATHERER, &info, "Test MySQL Store Result failed.");
                	}
                        else
                        {
                                test_row = mysql_fetch_row(test_res);
                                //debuglogger(DEBUG_GATHERER, &info, "Res: " + string(test_row[0]));
                                value = string(test_row[strtoint(mysql_row[4])]);
                                mysql_free_result(test_res);
                                mysql_close(&test_mysql);
                        }
                }
         }
         else
         {
                debuglogger(DEBUG_MONITOR, &info, "Unknown SQL Test (" +
		        inttostr(info.test_id) + ").");
         }

         mysql_free_result(mysql_res);
         return value;
}



string process_script_monitor(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	string		value;

	string query =
		string("SELECT cmd, data_type FROM tests_script WHERE id = ") +
		inttostr(info.test_id);
	mysql_res = db_query(mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	// if the script test exists
	if (mysql_row[0] != NULL)
	{
		string command = expand_parameters(info, string(mysql_row[0]) + " " + info.test_params);

		debuglogger(DEBUG_GATHERER, &info, "Sending '" + command + "' to shell.");

		// if error code is desired
		if (strtoint(mysql_row[1]) == 1)
		{
			value = inttostr(system(command.c_str()));
		}
		else
		{
			FILE *p_handle;
			char temp [100] = "";

			p_handle = popen(command.c_str(), "r");
			fgets(temp, 100, p_handle);
			pclose(p_handle);

			value = string(temp);

			if (value == "")
			{
				debuglogger(DEBUG_GATHERER, &info, "No data returned from script test.");
				value = "U";
			}
		}
	}
	else
	{
		debuglogger(DEBUG_MONITOR, &info, "Unknown Script Test (" +
			inttostr(info.test_id) + ").");
		value = "U";
	}

	mysql_free_result(mysql_res);

	return value;

}

string process_snmp_monitor(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;
	string 		value;

	string query =
		string("SELECT oid FROM tests_snmp WHERE id = ") +
		inttostr(info.test_id);

	mysql_res = db_query(mysql, &info, query);
	mysql_row = mysql_fetch_row(mysql_res);

	// if the snmp test exists
	if (mysql_row[0] != NULL)
	{
		string oid = expand_parameters(info, mysql_row[0]);

		if (info.snmp_avoid == 0)
		{
			value = snmpget(info, oid);
		}
		else
		{
			value = "U";
			debuglogger(DEBUG_MONITOR, &info, "Avoided.");
		}
	}
	else
	{
		value = "U";
		debuglogger(DEBUG_MONITOR, &info, "Unknown SNMP Test (" +
			inttostr(info.test_id) + ").");
	}

	mysql_free_result(mysql_res);

	return value;
}


void process_monitor(DeviceInfo info, MYSQL *mysql, RRDInfo rrd)
{
	debuglogger(DEBUG_MONITOR, &info, "Starting Monitor.");

	switch (info.test_type)
	{
		case  1:	info.curr_val = process_script_monitor(info, mysql);
				break;

		case  2:	info.curr_val = process_snmp_monitor(info, mysql);
				break;

                case  3:        info.curr_val = process_sql_monitor(info, mysql);
                                break;

		default:	{
					debuglogger(DEBUG_MONITOR, &info, "Unknown test type (" +
						inttostr(info.test_type) + ").");
					info.curr_val = "U";
				}
	} // end switch

	debuglogger(DEBUG_MONITOR, &info, "Value: " + info.curr_val);

        if (rrd.data_type != "")
	{
		update_monitor_rrd(info, rrd);
	}

        // destroy anything non-integer; we don't want it here.
	if (info.curr_val != inttostr(strtoint(info.curr_val)))
	{
		info.curr_val = "U";
	}


	if ((info.curr_val == "U") || (info.last_val == "U"))
	{
		info.delta_val = "U";
	}
	else
	{
		info.delta_val = inttostr(strtoint(info.curr_val) - strtoint(info.last_val));
	}

	update_monitor_db(info, mysql, rrd);

}

void process_sub_device(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;

	debuglogger(DEBUG_SUBDEVICE, &info, "Starting Subdevice.");


	// create an array containing the parameters for the subdevice

	string query =
	        string("SELECT name, value FROM sub_dev_variables WHERE sub_dev_id = ") +
		inttostr(info.subdevice_id);

        mysql_res = db_query(mysql, &info, query);

	for (int i = 0; i < mysql_num_rows(mysql_res); i++)
	{
	        mysql_row = mysql_fetch_row(mysql_res);

		info.parameters.push_front(ValuePair(mysql_row[0], mysql_row[1]));
	}

	mysql_free_result(mysql_res);

	// depending on the subdevice type, assign values to more parameters
	switch (info.subdevice_type)
	{
		case 1:			break; // group

		case 2:			setup_interface_parameters(&info, mysql);
					break; // interface

		case 3:			setup_disk_parameters(&info, mysql);
					break; // disk

		default:                debuglogger(DEBUG_SUBDEVICE, &info, "Unknown subdevice type (" +
					inttostr(info.subdevice_type) + ")");

	}  // end subdevice type switch

	// query the monitors for this subdevice

	query =
		string("SELECT ") 				+
		string("monitors.data_type, 	") 		+ // 0
		string("data_types.rrd_type, 	")		+ // 1
		string("monitors.min_val,	")		+ // 2
		string("monitors.max_val,	")		+ // 3
		string("monitors.tuned,		")		+ // 4
		string("monitors.test_type,	")		+ // 5
		string("monitors.test_id,	") 		+ // 6
		string("monitors.test_params,	")		+ // 7
		string("monitors.last_val,	")		+ // 8
		string("monitors.id		")		+ // 9
		string("FROM monitors ")			+
		string("LEFT JOIN data_types ON monitors.data_type=data_types.id ") +
		string("WHERE sub_dev_id = ") + inttostr(info.subdevice_id);


	mysql_res = db_query(mysql, &info, query);

	for (int i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		info.monitor_id = strtoint(mysql_row[9]);
		info.test_type  = strtoint(mysql_row[5]);
		info.test_id	= strtoint(mysql_row[6]);
		info.test_params= mysql_row[7];

		if (mysql_row[8] != NULL)
		{
			info.last_val = mysql_row[8];
		}

		RRDInfo rrd;

		// if we're using RRD
		if (strtoint(mysql_row[0]) > 0)
		{
			if (mysql_row[1] != NULL)
			{
				rrd.data_type   = mysql_row[1];
			}
			if (mysql_row[2] != NULL)
			{
				rrd.min_val	= mysql_row[2];
			}
			if (mysql_row[3] != NULL)
			{
				rrd.max_val	= mysql_row[3];
			}

			rrd.tuned		= strtoint(mysql_row[4]);
		} // end using rrd

		// process each monitor
		process_monitor(info, mysql, rrd);

	} // end for each monitor

        mysql_free_result(mysql_res);


} // end process subdevice


void process_sub_devices(DeviceInfo info, MYSQL *mysql)
{

	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	
	string query = string("SELECT id, type FROM sub_devices WHERE dev_id=") + inttostr(info.device_id);
	
	mysql_res = db_query(mysql, &info, query);
	
	for (int i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		info.subdevice_id 	= strtoint(mysql_row[0]);
		info.subdevice_type	= strtoint(mysql_row[1]);
		process_sub_device(info, mysql);
	}

	mysql_free_result(mysql_res);
}

void process_device(int dev_id)
{
	MYSQL 		mysql;
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;

	DeviceInfo info;

	info.device_id = dev_id;	
	
	// connect to db, get info for this device
	
	debuglogger(DEBUG_DEVICE, &info, "Starting device thread.");
	mysql = db_connect(mysql);
	debuglogger(DEBUG_DEVICE, &info, "MySQL connection established.");
	
	string query = 	string("SELECT ") 		+ 
			string("name, ")  		+	// 0
			string("ip, ")			+ 	// 1
			string("snmp_enabled, ")	+	// 2
			string("snmp_read_community, ")	+	// 3
			string("snmp_recache, ")	+	// 4
			string("snmp_uptime, ")		+	// 5
			string("snmp_ifnumber, ")	+	// 6 
			string("snmp_check_ifnumber ")	+	// 7
			string("FROM mon_devices ")	+
			string("WHERE id=") + inttostr(dev_id);

	mysql_res = db_query(&mysql, &info, query);	
	mysql_row = mysql_fetch_row(mysql_res);

	info.name 			= mysql_row[0];
	info.ip				= mysql_row[1];
	info.snmp_read_community 	= mysql_row[3];

	// get SNMP-level info, if SNMP is used.

	if (strtoint(mysql_row[2]) == 1)
	{
		// get uptime
		info.snmp_uptime = get_snmp_uptime(info);
		debuglogger(DEBUG_SNMP, &info, "SNMP Uptime is " + inttostr(info.snmp_uptime));	
		
		// store new uptime
		do_mysql_update("UPDATE mon_devices SET snmp_uptime=" + inttostr(info.snmp_uptime) + 
				" WHERE id=" + inttostr(dev_id));
		
		if (info.snmp_uptime == 0)
		{
			// device is snmp-dead
			info.snmp_avoid = 1;
			debuglogger(DEBUG_DEVICE, &info, "Device is SNMP-dead.  Avoiding SNMP tests.");
		} 
		else 
		{
			if (strtoint(mysql_row[5]) == 0)
			{
				// device came back from the dead
				info.snmp_recache = 1;
				debuglogger(DEBUG_DEVICE, &info, "Device has returned from SNMP-death.");
			}
				
			if (info.snmp_uptime < strtoint(mysql_row[5]))
			{
				// uptime went backwards
				info.snmp_recache = 1;
				debuglogger(DEBUG_SNMP, &info, "SNMP Agent Restart.");
			}

		
			if (strtoint(mysql_row[7]) == 1)
			{
				// we care about ifNumber

				info.snmp_ifnumber =  strtoint(snmpget(info, string("interfaces.ifNumber.0")));
			
				debuglogger(DEBUG_SNMP, &info, 
					"Number of Interfaces is " + inttostr(info.snmp_ifnumber));
				
				if (info.snmp_ifnumber != strtoint(mysql_row[6]))
				{
					// ifNumber changed
					info.snmp_recache = 1;
					do_mysql_update("UPDATE mon_devices SET snmp_ifnumber = " + 
						inttostr(info.snmp_ifnumber) + string(" WHERE id = ") + 
						inttostr(dev_id));
					debuglogger(DEBUG_SNMP, &info, 
						"Number of interfaces changed from " + string(mysql_row[6]));
				}

			}

			if (strtoint(mysql_row[4]) == 1)
			{
				// we recache this one every time.
				info.snmp_recache = 1;
			}

		} // end snmp_uptime > 0

		if (info.snmp_recache)
		{
			// we need to recache.	
			do_snmp_recache(dev_id);
		}	

	} // end snmp-enabled
	
	mysql_free_result(mysql_res);

	// process sub-devices
	process_sub_devices(info, &mysql);

	mysql_close(&mysql);


} // end process_device


// child - the thread spawned to process each device
void *child(void * arg)
{

	int device_id = *(int *) arg;

	process_device(device_id);

	debuglogger(DEBUG_THREAD, NULL, "Closing Thread...");

	pthread_mutex_lock(&active_threads_lock);
	active_threads--;
	pthread_mutex_unlock(&active_threads_lock);

	debuglogger(DEBUG_THREAD, NULL, "Thread Ended.");

	pthread_exit(0);

} // end child

// main - the body of the program
int main()
{

	MYSQL			mysql;
	MYSQL_RES		*mysql_res;
	MYSQL_ROW		mysql_row;
	int			i		= 0;
	time_t			start_time;
	time_t			run_time;
	FILE			*lockfile;
	long int		num_rows	= 0;
	int			thread_counter	= 0;
	int temp;
	int worker;
	pthread_t*		threads		= NULL;
	int*			ids		= NULL;
	int			errcode;
	int			*status;
	string			temp_string;
	char			temp_cstr [100];

	start_time = time( NULL );
	debuglogger(DEBUG_GLOBAL, NULL, "NetMRG starting.");
	debuglogger(DEBUG_GLOBAL, NULL, "Start time is " + inttostr(start_time));

	if (file_exists("/var/www/netmrg/dat/lockfile"))
       	{
		printf("ERROR:  My lockfile exists!  Is another netmrg running?\n  If not, remove the lockfile and try again\n");
		exit(254);
	}

	// create lockfile
	debuglogger(DEBUG_GLOBAL, NULL, "Creating Lockfile.");
	lockfile = fopen("/var/www/netmrg/dat/lockfile","w+");
	fprintf(lockfile, "%d", start_time);
	fclose(lockfile);

	// SNMP library initialization
	debuglogger(DEBUG_GLOBAL + DEBUG_SNMP, NULL, "Initializing SNMP library.");
	init_snmp("snmpapp");
	SOCK_STARTUP;
	struct snmp_session Session;
	snmp_sess_init(&Session);

	// RRDTOOL command pipe setup
	debuglogger(DEBUG_GLOBAL + DEBUG_RRD, NULL, "Initializing RRDTOOL pipe.");
	rrdtool_pipe = popen(RRDTOOL, "w");
	// sets buffering to one line
	setlinebuf(rrdtool_pipe);


	// open mysql connection for initial queries

	mysql = db_connect(mysql);

	// request list of devices to process

	mysql_res = db_query(&mysql, NULL, "SELECT id FROM mon_devices WHERE disabled=0 ORDER BY id");

	num_rows 	= mysql_num_rows(mysql_res);
        threads 	= new pthread_t[num_rows];
	ids             = new int[num_rows];

	int dev_counter = 0;

	// deploy more threads as needed
	int last_active_threads = 0;
	while (dev_counter < num_rows)
	{
		if (pthread_mutex_trylock(&active_threads_lock) != EBUSY)
		{
			if (last_active_threads != active_threads)
			{
				debuglogger(DEBUG_THREAD, NULL, "[ACTIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
				last_active_threads = active_threads;
			}
			while ((active_threads < NTHREADS) && (dev_counter < num_rows))
			{
				mysql_row = mysql_fetch_row(mysql_res);
				int dev_id = strtoint(string(mysql_row[0]));
				ids[dev_counter] = dev_id;
				pthread_create(&threads[dev_counter], NULL, child, &ids[dev_counter]);
				pthread_detach(threads[dev_counter]);
				dev_counter++;
				active_threads++;
			}
			pthread_mutex_unlock(&active_threads_lock);
		}
		else
		{
			debuglogger(DEBUG_THREAD, NULL, "[ACTIVE] Sorry, can't lock thread counter.");
		}
		usleep(THREAD_SLEEP);
	}

	// wait until all threads exit
	int canexit = 0;
	while (canexit == 0)
	{
		if (pthread_mutex_trylock(&active_threads_lock) != EBUSY)
		{
	                if (last_active_threads != active_threads)
        	        {
                	        debuglogger(DEBUG_THREAD, NULL, "[PASSIVE] Last: " +
						inttostr(last_active_threads) + ", Now: " +
						inttostr(active_threads));
	                        last_active_threads = active_threads;
        	        }

			if (active_threads == 0) canexit = 1;

			pthread_mutex_unlock(&active_threads_lock);
		} 
		else 
		{
			debuglogger(DEBUG_THREAD, NULL, "[PASSIVE] Sorry, can't lock thread counter.");
		}	
		usleep(THREAD_SLEEP);
	}

		
	// free active devices results
	mysql_free_result(mysql_res);
	
	// generate change of status report
	mysql_res = db_query(&mysql, NULL, 
			string("SELECT date, dev_name, situation, event_text, since_last_change ") + 
			string("FROM event_log WHERE date >= ") + 
			inttostr(start_time) + string(" ORDER BY situation"));
	
	num_rows = mysql_num_rows(mysql_res);
	
	if (num_rows > 0)
	{
		printf("ATTENTION: Creating Status Report.\n");
		lockfile = fopen("/var/www/netmrg/dat/status_report","w+");
		for (i = 0; i < mysql_num_rows(mysql_res); i++)
		{
			mysql_row = mysql_fetch_row(mysql_res);
			fprintf(lockfile, 
				"DATE/TIME: %s\nDevice: %s\nSituation: %s\nEvent: %s\nTime Since Last Change: %s\n\n",
				mysql_row[0], mysql_row[1], mysql_row[2], mysql_row[3], mysql_row[4]);
		}
		fclose(lockfile);
		system(DISTRIB_CMD);
	}

	// free report results
	mysql_free_result(mysql_res);
	
	delete [] threads;
	delete [] ids;
	
	// clean up mysql
	mysql_close(&mysql);
	debuglogger(DEBUG_GLOBAL, NULL, "Closed MySQL connection.");

	// clean up RRDTOOL command pipe
	pclose(rrdtool_pipe);
	debuglogger(DEBUG_GLOBAL, NULL, "Closed RRDTOOL pipe.");

	// clean up SNMP
	SOCK_CLEANUP;
	debuglogger(DEBUG_GLOBAL, NULL, "Cleaned up SNMP.");
	
	// determine runtime and store it
	run_time = time( NULL ) - start_time;
	fprintf( stdout , "Runtime: %d\n", run_time);
	lockfile = fopen("/var/www/netmrg/dat/runtime","w+");
	fprintf(lockfile, "%d", run_time);
	fclose(lockfile);
	
	// remove lock file
	unlink("/var/www/netmrg/dat/lockfile");

}


