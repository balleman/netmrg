/********************************************
* NetMRG Integrator
*
* snmp.cpp
* NetMRG Gatherer SNMP Library
*
* Copyright (C) 2001-2010
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/

/*

   NetMRG SNMP Functions

*/

#include "utils.h"
#include "locks.h"
#include "snmp.h"

#ifdef HAVE_NET_SNMP
#include <net-snmp-config.h>
#include <net-snmp-includes.h>
#include <config_api.h>
#include <mib_api.h>
#define SNMP_SET_OIDS netsnmp_ds_set_int(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_OID_OUTPUT_FORMAT, NETSNMP_OID_OUTPUT_NUMERIC)
#define SNMP_SET_LIBS netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_PRINT_NUMERIC_ENUM, 1)
#define SNMP_SET_QKPR netsnmp_ds_set_boolean(NETSNMP_DS_LIBRARY_ID, NETSNMP_DS_LIB_QUICK_PRINT, 0)
#else
#define DS_APP_DONT_FIX_PDUS 0
#include <ucd-snmp-config.h>
#include <ucd-snmp-includes.h>
#include <system.h>
#include <mib.h>
#define SNMP_SET_OIDS ds_toggle_boolean(DS_LIBRARY_ID, DS_LIB_PRINT_NUMERIC_OIDS)
#define SNMP_SET_LIBS ds_toggle_boolean(DS_LIBRARY_ID, DS_LIB_PRINT_NUMERIC_ENUM)
#define SNMP_SET_QKPR snmp_set_quick_print(0)
#endif

void snmp_init()
{
	debuglogger(DEBUG_GLOBAL + DEBUG_SNMP, LEVEL_INFO, NULL, "Initializing SNMP library.");
	init_snmp("NetMRG");
	SOCK_STARTUP;
	struct snmp_session session;
	snmp_sess_init(&session);
	SNMP_SET_OIDS;
	SNMP_SET_LIBS;
	SNMP_SET_QKPR;
}

void snmp_cleanup()
{
	SOCK_CLEANUP;
	debuglogger(DEBUG_GLOBAL + DEBUG_SNMP, LEVEL_INFO, NULL, "Cleaned up SNMP.");
}

string snmp_value(string input)
{
	input = input.erase(0, input.find(":",0) + 1);
	input = input.erase(0, input.find("=",0) + 1);
	while (!input.empty() && input[0] == ' ')
	{
		input = input.erase(0, 1);
	}
	while (!input.empty() && input[input.length() -1] == ' ')
	{
		input = input.erase(input.length() - 1, input.length());
	}
	//input = token_replace(input, " ", "");

	// handle an "empty" SNMPv2 response.
	input = token_replace(input, "No Such Object available on this agent at this OID", "");

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

void snmp_session_init(DeviceInfo &info)
{
	struct	snmp_session session;
	u_char	u_temp[250], u_temp1[250], u_temp2[250], u_temp3[250];
	char	temp[250], temp1[250];
	void    * sessp;

	debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "Starting SNMP Session.");
	
	// initialize session structure
	snmp_sess_init(&session);

	// set hostname or IP address (and port)
	snprintf(temp, 250, "%s:%d", info.ip.c_str(), info.snmp_port);
	session.peername = temp;

	// set the SNMP version number
	switch (info.snmp_version)
	{
		case 1: session.version = SNMP_VERSION_1;
				session.securityModel = SNMP_SEC_MODEL_SNMPv1;
				debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "SNMPv1");
				break;
		case 2: session.version = SNMP_VERSION_2c;
				session.securityModel = SNMP_SEC_MODEL_SNMPv2c;
				debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "SNMPv2c");
				break;
		case 3: session.version = SNMP_VERSION_3;
				debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "SNMPv3");
				break;
	}
	
	// set timeout/retry parameters
	session.timeout = info.snmp_timeout;
	session.retries = info.snmp_retries;

	char log[255];
	snprintf(log, 255, "Port: %d; Timeout: %ld; Retries: %d.", info.snmp_port, info.snmp_timeout, info.snmp_retries);
	debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, log);
		
	// set the SNMPv1/2c community name used for authentication
	if ( (info.snmp_version == 1) || (info.snmp_version == 2) )
	{
		session.community = u_string(info.snmp_read_community, u_temp);
		session.community_len = info.snmp_read_community.length();
	}
	else if (info.snmp_version == 3)
	{
		strncpy(temp1, info.snmp3_user.c_str(), 250);
		temp1[249] = '\0';
		session.securityName = temp1;
		session.securityNameLen = strlen(temp1);

		session.securityModel = SNMP_SEC_MODEL_USM;

		switch (info.snmp3_seclev)
		{
			case 0:	session.securityLevel = SNMP_SEC_LEVEL_NOAUTH; break;
			case 1: session.securityLevel = SNMP_SEC_LEVEL_AUTHNOPRIV; break;
			case 2: session.securityLevel = SNMP_SEC_LEVEL_AUTHPRIV; break;
		}

		if ( (info.snmp3_seclev == 1) || (info.snmp3_seclev == 2) )
		{
			// We're using authentication
			debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "Setting up SNMPv3 Authentication");
			switch (info.snmp3_aprot)
			{
				case 0: 
					session.securityAuthProto = usmHMACMD5AuthProtocol;
					session.securityAuthProtoLen = sizeof(usmHMACMD5AuthProtocol)/sizeof(oid);
					break;
				case 1:
					session.securityAuthProto = usmHMACSHA1AuthProtocol;
					session.securityAuthProtoLen = sizeof(usmHMACSHA1AuthProtocol)/sizeof(oid);
					break;
			}

			session.securityAuthKeyLen = USM_AUTH_KU_LEN;
			    
			if (generate_Ku(session.securityAuthProto, session.securityAuthProtoLen, u_string(info.snmp3_apass, u_temp2),
				 info.snmp3_apass.length(), session.securityAuthKey, &session.securityAuthKeyLen) != SNMPERR_SUCCESS) 
			{
			       debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, 
					string("Failed to generate Ku from authentication password: {") + info.snmp3_apass + "}");
			}
		}

		if (info.snmp3_seclev == 2)
		{
			// We're using privacy
			debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "Setting up SNMPv3 Privacy");
			switch (info.snmp3_pprot)
			{
				case 0:
					session.securityPrivProto = usmDESPrivProtocol;
					session.securityPrivProtoLen = sizeof(usmDESPrivProtocol)/sizeof(oid);
					break;
				
				case 1:
					session.securityPrivProto = usmAESPrivProtocol;
					session.securityPrivProtoLen = sizeof(usmAESPrivProtocol)/sizeof(oid);
					break;
			} 	
			
			session.securityPrivKeyLen = USM_PRIV_KU_LEN;
			    
			if (generate_Ku(session.securityAuthProto, session.securityAuthProtoLen, u_string(info.snmp3_ppass, u_temp3),
				 info.snmp3_ppass.length(), session.securityPrivKey, &session.securityPrivKeyLen) != SNMPERR_SUCCESS) 
			{
			       debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, string("Failed to generate Ku from privacy password: {") + 
					info.snmp3_ppass + "}");
			}
		}	
	}

	netmrg_mutex_lock(lkSNMP);
	sessp = snmp_sess_open(&session);
	netmrg_mutex_unlock(lkSNMP);
	
	if (!sessp)
	{
		debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, "SNMP Session Error.");
	}
	else
	{
		info.snmp_sess_p = sessp;
	}
		
}

void snmp_session_cleanup(DeviceInfo &info)
{
	debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "Cleaning up SNMP Session.");
	snmp_sess_close(info.snmp_sess_p);
	info.snmp_sess_p = NULL;
}

// snmp_get - perform an snmpget on a host using the provided information
string snmp_get(DeviceInfo info, string oidstring)
{
	struct 	snmp_pdu *pdu;
	struct 	snmp_pdu *response;

	oid 	anOID[MAX_OID_LEN];
	size_t 	anOID_len = MAX_OID_LEN;

	struct 	variable_list *vars;
	int 	status;
	string 	result;
	char 	tempname[128];

	debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "SNMP Query ({'" +
		info.ip + "'}, {'" + info.snmp_read_community + "'}, '" +
		oidstring + "')");

	if (!info.snmp_sess_p)
	{
		debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, "SNMP Session Failure.");
		return string("U");
	}
	else
	{
		// Create the PDU for the data for our request.
		pdu = snmp_pdu_create(SNMP_MSG_GET);

		strcpy(tempname, oidstring.c_str());
		if (!snmp_parse_oid(tempname, anOID, &anOID_len))
		{
			return(string("U"));
		}
		else
		snmp_add_null_var(pdu, anOID, anOID_len);

		status = snmp_sess_synch_response(info.snmp_sess_p, pdu, &response);
		
		if (response == NULL)
			status = STAT_ERROR;

		/*
		* Process the response.
		*/

		if (status == STAT_SUCCESS && response->errstat == SNMP_ERR_NOERROR)
		{
			vars = response->variables;
			if (vars->type == SNMP_NOSUCHINSTANCE || vars->type == SNMP_NOSUCHOBJECT || vars->type == SNMP_ENDOFMIBVIEW)
			{
				result = string("U");
			}
			else
			{
				result = snmp_result(vars);
				result = snmp_value(result);
			}
		}
		else
		{
			result = string("U");
		}

		if (response) snmp_free_pdu(response);

		if (result.length() == 0) { result = "U"; }

		return result;
	}
}

string snmp_diff(DeviceInfo info, string oid1, string oid2)
{
	string val1 = snmp_get(info, oid1);
	string val2 = snmp_get(info, oid2);

	if ( (val1 == "U") || (val2 == "U") )
	{
		return "U";
	}
	
	return inttostr( strtoint(val1) - strtoint(val2) );
}

list<SNMPPair> snmp_trim_rootoid(list<SNMPPair> input, string rootoid)
{
	for (list<SNMPPair>::iterator current = input.begin(); current != input.end(); current++)
	{
		current->oid = token_replace(current->oid, rootoid, "");
	}

	return input;
}

list<SNMPPair> snmp_swap_index_value(list<SNMPPair> input)
{
	for (list<SNMPPair>::iterator current = input.begin(); current != input.end(); current++)
	{
		string oid   = current->oid;
		string value = current->value;

		current->oid   = value;
		current->value = oid;
	}

	return input;
}

list<SNMPPair> snmp_walk(DeviceInfo info, string oidstring)
{
	struct snmp_pdu	*pdu, *response;
	variable_list	*vars;
	oid				name[MAX_OID_LEN];
	size_t			name_length = MAX_OID_LEN;
	oid				root[MAX_OID_LEN];
	size_t			rootlen = MAX_OID_LEN;
	int				running;
	int				status;
	int				check = 0;
	int				exitval = 0;
	list<SNMPPair>	results;

	if (!info.snmp_sess_p)
	{
		debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, "SNMP Session Failure.");
	}

	char tempoid[128];
	strcpy(tempoid, oidstring.c_str());
	if (!snmp_parse_oid(tempoid, root, &rootlen))
	{
		debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, string("SNMP OID Parse Failure (") + tempoid + ")");
	}

	memmove(name, root, rootlen * sizeof(oid));
	name_length = rootlen;

	running = 1;

	while (running)
	{
		pdu = snmp_pdu_create(SNMP_MSG_GETNEXT);
		snmp_add_null_var(pdu, name, name_length);

		status = snmp_sess_synch_response(info.snmp_sess_p, pdu, &response);
		
		if (response == NULL)
			status = STAT_ERROR;
		
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
					debuglogger(DEBUG_SNMP, LEVEL_DEBUG, &info, "OID: '" + snmp_oid(result) + "' VALUE: '" + snmp_value(result) + "'");
					results.push_front(SNMPPair(snmp_oid(result), snmp_value(result)));
					if ((vars->type != SNMP_ENDOFMIBVIEW) && (vars->type != SNMP_NOSUCHOBJECT) && (vars->type != SNMP_NOSUCHINSTANCE))
					{
						if (check && snmp_oid_compare(name, name_length, vars->name, vars->name_length) >= 0)
						{
							debuglogger(DEBUG_SNMP, LEVEL_WARNING, &info, "SNMP Error: OID not increasing");
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
					debuglogger(DEBUG_SNMP, LEVEL_NOTICE, &info, "End of MIB");
				}
				else
				{
					debuglogger(DEBUG_SNMP, LEVEL_WARNING, &info, string("SNMP Packet Error: ") + snmp_errstring(response->errstat));
					exitval = 2;
				}
			}
		}
		else
		{
			if (status == STAT_TIMEOUT)
			{
				debuglogger(DEBUG_SNMP, LEVEL_WARNING, &info, string("Timeout: No Response from ") + info.ip);
				running = 0;
				exitval = 1;
			}
			else
			{
				debuglogger(DEBUG_SNMP, LEVEL_ERROR, &info, string("SNMP Walk Error (") + inttostr(status) + ")");
				running = 0;
				exitval = 1;
        	}
		}

		if (response)
			snmp_free_pdu(response);
	}

	return results;
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

} // end get_snmp_uptime()

