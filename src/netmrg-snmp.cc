/*

   NetMRG SNMP Functions
   Copyright 2001-2002 Brady Alleman, All Rights Reserved.

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

	snmp_sess_init( &session );

	strcpy(temp, info.ip.c_str());
	session.peername = temp;

    /* set up the authentication parameters for talking to the server */


    /* set the SNMP version number */
	session.version = SNMP_VERSION_1;

    /* set the SNMPv1 community name used for authentication */
	session.community = u_string(info.snmp_read_community, u_temp);
	session.community_len = info.snmp_read_community.length();

	pthread_mutex_lock(&snmp_lock);
	sessp = snmp_sess_open(&session);                     /* establish the session */
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
			sprint_value(temp, vars->name, vars->name_length, vars);
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


