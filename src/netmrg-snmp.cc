/*

   NetMRG SNMP Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.
   
   Much of this code was originally part of net-snmp's application and
   example code.  

*/

#define DS_APP_DONT_FIX_PDUS 0

// snmpget - perform an snmpget on a host using the provided information
string snmpget(DeviceInfo info, string oidstring)
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
			snprint_value(temp, sizeof(temp), vars->name, vars->name_length, vars);
			result = temp;
			result = result.erase(0, result.find(":",0) + 1);
			result = result.erase(0, result.find("=",0) + 1);
			result = token_replace(result, " ", "");
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

	SNMPPair(int setoid, string setvalue)
	{
		oid   = setoid;
		value = setvalue;
	}
};

int numprinted = 0;

void snmp_get_and_print(netsnmp_session * ss, oid * theoid, size_t theoid_len)
{
	netsnmp_pdu		*pdu, *response;
	netsnmp_variable_list	*vars;
	int			status;

	pdu = snmp_pdu_create(SNMP_MSG_GET);
	snmp_add_null_var(pdu, theoid, theoid_len);

	status = snmp_synch_response(ss, pdu, &response);

	if (status == STAT_SUCCESS && response->errstat == SNMP_ERR_NOERROR)
	{
		for (vars = response->variables; vars; vars = vars->next_variable)
		{
			numprinted++;
			print_variable(vars->name, vars->name_length, vars);
        	}
	}

	if (response)
	{
		snmp_free_pdu(response);
	}
}

void snmp_walk(DeviceInfo info, string oidstring)
{

	struct snmp_session	session;
	void			*ss;
	struct snmp_pdu		*pdu, *response;
	variable_list		*vars;
	oid			name[MAX_OID_LEN];
	size_t			name_length = MAX_OID_LEN;
	oid			root[MAX_OID_LEN];
	size_t			rootlen = MAX_OID_LEN;
	int			count;
	int			running;
	int			status;
	int			check;
	int			exitval = 0;

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
		// error
	}

        char tempoid[128];
	strcpy(tempoid, oidstring.c_str());
	if (!snmp_parse_oid(tempoid, root, &rootlen))
	{
		debuglogger(DEBUG_SNMP, &info, string("SNMP OID Parse Failure (") + tempoid + ")");
		//return(string("U"));
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
					numprinted++;
					print_variable(vars->name, vars->name_length, vars);
					if ((vars->type != SNMP_ENDOFMIBVIEW) &&
						(vars->type != SNMP_NOSUCHOBJECT) &&
						(vars->type != SNMP_NOSUCHINSTANCE))
					{
			                        if (check && snmp_oid_compare(name, name_length, vars->name, vars->name_length) >= 0)
						{
							fprintf(stderr, "Error: OID not increasing: ");
							fprint_objid(stderr, name, name_length);
							fprintf(stderr, " >= ");
							fprint_objid(stderr, vars->name, vars->name_length);
							fprintf(stderr, "\n");
							running = 0;
							exitval = 1;
						}
						memmove((char *) name, (char *) vars->name, vars->name_length * sizeof(oid));
						name_length = vars->name_length;
					}
					else
		                        running = 0;
				}
			}
			else
			{
				running = 0;
				if (response->errstat == SNMP_ERR_NOSUCHNAME)
				{
					printf("End of MIB\n");
				}
				else
				{
					fprintf(stderr, "Error in packet.\nReason: %s\n", snmp_errstring(response->errstat));
					if (response->errindex != 0)
					{
						fprintf(stderr, "Failed object: ");
						for (count = 1, vars = response->variables; vars && count != response->errindex; vars = vars->next_variable, count++)
						if (vars)
						fprint_objid(stderr, vars->name, vars->name_length);
						fprintf(stderr, "\n");
					}
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
				//snmp_sess_perror("snmpwalk", &session);
				running = 0;
				exitval = 1;
        		}
		}

		if (response)
			snmp_free_pdu(response);
	}

	if (numprinted == 0 && status == STAT_SUCCESS)
	{
		/*
		* no printed successful results, which may mean we were
		* pointed at an only existing instance.  Attempt a GET, just
		* for get measure.
		*/
		//snmp_get_and_print(ss, root, rootlen);
	}

	snmp_sess_close(ss);

	//return exitval;
}


