/*

   NetMRG SNMP Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

   Much of this code was originally part of net-snmp's application and
   example code.

*/

#define DS_APP_DONT_FIX_PDUS 0

void snmp_init()
{
	debuglogger(DEBUG_GLOBAL + DEBUG_SNMP, NULL, "Initializing SNMP library.");
	init_snmp("snmpapp");
	SOCK_STARTUP;
	struct snmp_session session;
	snmp_sess_init(&session);
	ds_toggle_boolean(DS_LIBRARY_ID, DS_LIB_PRINT_NUMERIC_OIDS);
	ds_toggle_boolean(DS_LIBRARY_ID, DS_LIB_PRINT_NUMERIC_ENUM);
}

void snmp_cleanup()
{
	SOCK_CLEANUP;
	debuglogger(DEBUG_GLOBAL + DEBUG_SNMP, NULL, "Cleaned up SNMP.");
}

string snmp_value(string input)
{
	input = input.erase(0, input.find(":",0) + 1);
	input = input.erase(0, input.find("=",0) + 1);
	input = token_replace(input, " ", "");

	return input;
}

string snmp_oid(string input)
{
	input = input.erase(input.find(" ",0), input.length());

	return input;
}

string snmp_result(variable_list *vars)
{
	u_char         *buf = NULL;
	size_t          buf_len = 256, out_len = 0;
	
	buf = (u_char *) calloc(buf_len, 1);
	sprint_realloc_variable(&buf, &buf_len, &out_len, 1, vars->name, vars->name_length, vars);
	string result = (char *)buf;
	free(buf);

	return result;
}

// snmp_get - perform an snmpget on a host using the provided information
string snmp_get(DeviceInfo info, string oidstring)
{
	struct	snmp_session session;
	void 	*sessp;
	struct 	snmp_pdu *pdu;
	struct 	snmp_pdu *response;

	oid 	anOID[MAX_OID_LEN];
	size_t 	anOID_len = MAX_OID_LEN;

	struct 	variable_list *vars;
	int 	status;

        string 	result;
        char 	temp [250];
        u_char 	u_temp [250];

        char 	tempname[128];

        debuglogger(DEBUG_SNMP, &info, "SNMP Query ('" +
                info.ip + "', '" + info.snmp_read_community + "', '" +
                oidstring + "')");

	snmp_sess_init(&session);

	strcpy(temp, info.ip.c_str());
	session.peername = temp;


	/* set the SNMP version number */
	session.version = SNMP_VERSION_1;

	/* set the SNMPv1 community name used for authentication */
	session.community = u_string(info.snmp_read_community, u_temp);
	session.community_len = info.snmp_read_community.length();

	pthread_mutex_lock(&snmp_lock);
	sessp = snmp_sess_open(&session);	/* establish the session */
	pthread_mutex_unlock(&snmp_lock);

	if (!sessp)
	{
		debuglogger(DEBUG_SNMP, &info, string("SNMP Query Error."));
		return(string("U"));
	}
	else
	{

	/*
	* Create the PDU for the data for our request.
	*/
	pdu = snmp_pdu_create(SNMP_MSG_GET);

	strcpy(tempname, oidstring.c_str());
	if (!snmp_parse_oid(tempname, anOID, &anOID_len))
       	{
		return(string("U"));
	}
	else
        snmp_add_null_var(pdu, anOID, anOID_len);

	status = snmp_sess_synch_response(sessp, pdu, &response);

	/*
	* Process the response.
	*/

	if (status == STAT_SUCCESS && response->errstat == SNMP_ERR_NOERROR)
	{

	/*
	* SUCCESS: Print the result variables
	*/

		if (status == STAT_SUCCESS)
		{
			vars = response->variables;
			result = snmp_result(vars);
                        result = snmp_value(result);
                }
		else
		{
                	result = string("U");
                }

	}

	if (response) snmp_free_pdu(response);
	snmp_sess_close(sessp);

        if (result.length() == 0) { result = "U"; }

	return result;
	}
}

struct SNMPPair
{
	string  oid;
	string  value;

	SNMPPair(string setoid, string setvalue)
	{
		oid   = setoid;
		value = setvalue;
	}
};

list<SNMPPair> snmp_trim_rootoid(list<SNMPPair> input, string rootoid)
{
	for (list<SNMPPair>::iterator current = input.begin(); current != input.end(); current++)
	{
		(*current).oid = token_replace((*current).oid, rootoid, "");
	}

	return input;
}

list<SNMPPair> snmp_swap_index_value(list<SNMPPair> input)
{
       	for (list<SNMPPair>::iterator current = input.begin(); current != input.end(); current++)
	{
		string oid   = (*current).oid;
		string value = (*current).value;

		(*current).oid   = value;
		(*current).value = oid;
	}

	return input;
}

list<SNMPPair> snmp_walk(DeviceInfo info, string oidstring)
{

	struct snmp_session	session;
	void			*ss;
	struct snmp_pdu		*pdu, *response;
	variable_list		*vars;
	oid			name[MAX_OID_LEN];
	size_t			name_length = MAX_OID_LEN;
	oid			root[MAX_OID_LEN];
	size_t			rootlen = MAX_OID_LEN;
	int			running;
	int			status;
	int			check;
	int			exitval = 0;
	list<SNMPPair>		results;

	snmp_sess_init(&session);

	char temp[250];
	strcpy(temp, info.ip.c_str());
	session.peername = temp;

	/* set the SNMP version number */
	session.version = SNMP_VERSION_1;

	/* set the SNMPv1 community name used for authentication */
	u_char u_temp[250];
	session.community = u_string(info.snmp_read_community, u_temp);
	session.community_len = info.snmp_read_community.length();

	pthread_mutex_lock(&snmp_lock);
	ss = snmp_sess_open(&session);
	pthread_mutex_unlock(&snmp_lock);


	if (ss == NULL)
	{
		debuglogger(DEBUG_SNMP, &info, "SNMP Session Failure.");
	}

        char tempoid[128];
	strcpy(tempoid, oidstring.c_str());
	if (!snmp_parse_oid(tempoid, root, &rootlen))
	{
		debuglogger(DEBUG_SNMP, &info, string("SNMP OID Parse Failure (") + tempoid + ")");
	}

	memmove(name, root, rootlen * sizeof(oid));
	name_length = rootlen;

	running = 1;

	while (running)
	{
		pdu = snmp_pdu_create(SNMP_MSG_GETNEXT);
		snmp_add_null_var(pdu, name, name_length);

		status = snmp_sess_synch_response(ss, pdu, &response);
		if (status == STAT_SUCCESS)
		{
			if (response->errstat == SNMP_ERR_NOERROR)
			{
				for (vars = response->variables; vars; vars = vars->next_variable)
				{
					if ((vars->name_length < rootlen) || (memcmp(root, vars->name, rootlen * sizeof(oid)) != 0))
					{
						running = 0;
						continue;
					}
					string result = snmp_result(vars);
					debuglogger(DEBUG_SNMP, &info, "OID: '" + snmp_oid(result) + "' VALUE: '" + snmp_value(result) + "'");
					results.push_front(SNMPPair(snmp_oid(result), snmp_value(result)));
					if ((vars->type != SNMP_ENDOFMIBVIEW) && (vars->type != SNMP_NOSUCHOBJECT) && (vars->type != SNMP_NOSUCHINSTANCE))
					{
						if (check && snmp_oid_compare(name, name_length, vars->name, vars->name_length) >= 0)
						{
							debuglogger(DEBUG_SNMP, &info, "SNMP Error: OID not increasing");
							running = 0;
							exitval = 1;
						}
						memmove((char *) name, (char *) vars->name, vars->name_length * sizeof(oid));
						name_length = vars->name_length;
					}
					else
					{
						running = 0;
					}
				}
			}
			else
			{
				running = 0;
				if (response->errstat == SNMP_ERR_NOSUCHNAME)
				{
					debuglogger(DEBUG_SNMP, &info, "End of MIB");
				}
				else
				{
					debuglogger(DEBUG_SNMP, &info, string("SNMP Packet Error: ") + snmp_errstring(response->errstat));
					exitval = 2;
				}
			}
		}
		else
		{
			if (status == STAT_TIMEOUT)
			{
				debuglogger(DEBUG_SNMP, &info, string("Timeout: No Response from ") + session.peername);
				running = 0;
				exitval = 1;
			}
			else
			{
				debuglogger(DEBUG_SNMP, &info, string("SNMP Walk Error (") + inttostr(status) + ")");
				running = 0;
				exitval = 1;
        		}
		}

		if (response)
			snmp_free_pdu(response);
	}

	snmp_sess_close(ss);

	return results;
}

