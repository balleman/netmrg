/********************************************
* NetMRG Integrator
*
* events.cpp
* NetMRG Gatherer Events Library
*
* see doc/LICENSE for copyright information
********************************************/

#include "events.h"
#include "utils.h"
#include "settings.h"

uint process_events(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES 	*mysql_res;
	MYSQL_ROW 	mysql_row;
	uint		status = 0;

	string query = "SELECT id, trigger_type, last_status, situation, last_triggered FROM events WHERE mon_id=" + inttostr(info.monitor_id) + " AND trigger_type = 1";
	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		info.event_id = strtoint(mysql_row[0]);
		if (process_event(info, mysql, strtoint(mysql_row[1]), strtoint(mysql_row[2]), strtoint(mysql_row[3]), strtoint(mysql_row[4])))
		{
			status = worstof(status, strtoint(mysql_row[3]));
		}
	}

	mysql_free_result(mysql_res);

	return status;
} // end process_events()


uint process_event(DeviceInfo info, MYSQL *mysql, int trigger_type, int last_status, int situation, long int last_triggered)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;
	uint		status = 0;

	string query = "SELECT value, value_type, condition, logic_condition FROM conditions WHERE event_id=" + inttostr(info.event_id) + " ORDER BY id";
	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);

		if (i == 0)
		{
			status = process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
		}
		else
		{
			switch (strtoint(mysql_row[3]))
			{
				case 0:	status = status && process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
					break;
				case 1:	status = status || process_condition(info, strtoint(mysql_row[0]), strtoint(mysql_row[1]), strtoint(mysql_row[2]));
					break;
			}
		}
	}

	mysql_free_result(mysql_res);

	if (status == 0)
	{
		debuglogger(DEBUG_EVENT, LEVEL_INFO, &info, "Not Triggered.");
		db_update(mysql, &info, "UPDATE events SET last_status=0 WHERE id=" + inttostr(info.event_id));
		return 0;
	}
	else
	{
		debuglogger(DEBUG_EVENT, LEVEL_INFO, &info, "Triggered.");

		if ((uint) last_status != status)
		{
			db_update(mysql, &info, "UPDATE events SET last_triggered=UNIX_TIMESTAMP(NOW()), last_status=1 WHERE id=" + inttostr(info.event_id));
			db_update(mysql, &info, "INSERT INTO event_log SET date=UNIX_TIMESTAMP(NOW()), time_since_last_change=UNIX_TIMESTAMP(NOW())-" + inttostr(last_triggered) + ", event_id=" + inttostr(info.event_id));

			process_responses(info, mysql);
		}

		return 1;
	}
} // end process_event()

uint process_condition(DeviceInfo info, long long int compare_value, int value_type, int condition)
{
	long long int actual_value = 0;

	switch (value_type)
	{
		case 0:
			if (info.curr_val == "U")
				return 0;
			actual_value = strtoint(info.curr_val);
			break;

		case 1: 
			if (info.delta_val == "U")
				return 0;
			actual_value = strtoint(info.delta_val);
			break;

		case 2: 
			if (info.rate_val == "U")
				return 0;
			actual_value = strtoint(info.rate_val);
			break;
	}

	switch (condition)
	{
		case 0:	if (actual_value < compare_value)
				return 1;
			break;

		case 1: if (actual_value == compare_value)
				return 1;
			break;

		case 2: if (actual_value > compare_value)
				return 1;
			break;

		case 3: if (actual_value <= compare_value)
				return 1;
			break;

		case 4: if (actual_value != compare_value)
				return 1;
			break;

		case 5: if (actual_value >= compare_value)
				return 1;
			break;
	}

	return 0;
} // end process_condition()

void process_responses(DeviceInfo info, MYSQL *mysql)
{
	MYSQL_RES	*mysql_res;
	MYSQL_ROW	mysql_row;

	string query = 	string("SELECT	notifications.command, responses.parameters, responses.id ") 	+
			string("FROM	responses, notifications ")			+
			string("WHERE	responses.event_id=") + inttostr(info.event_id) + " " +
			string("AND	responses.notification_id=notifications.id");

	mysql_res = db_query(mysql, &info, query);

	for (uint i = 0; i < mysql_num_rows(mysql_res); i++)
	{
		mysql_row = mysql_fetch_row(mysql_res);
		info.response_id = strtoint(mysql_row[2]);
		string command = string(mysql_row[0]) + " " + string(mysql_row[1]);
		if (command[0] != '/')
			command = get_setting(setPathLibexec) + "/" + command;
		debuglogger(DEBUG_RESPONSE, LEVEL_INFO, &info, "Running Response: " + command);
		system(command.c_str());
	}

} // end process_responses()

