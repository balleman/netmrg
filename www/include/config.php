<?php
########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Static Configuration Module                #
#           config.php                                 #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

/***** GLOBAL SETTINGS *****/ 
error_reporting(E_ALL);
//error_reporting(E_WARNING | E_ERROR);
ini_set("REGISTER_GLOBALS", 0);



/***** CONFIG *****/
// Cosmetic Variables
$GLOBALS["netmrg"]["version"] = "0.79.3a";
$GLOBALS["netmrg"]["name"] = "NetMRG";
$GLOBALS["netmrg"]["company"] = "Generic Company";
$GLOBALS["netmrg"]["companylink"] = "http://netmrg.net";

// DB Config
$GLOBALS["netmrg"]["dbhost"] = "localhost";
$GLOBALS["netmrg"]["dbname"] = "netmrg";
$GLOBALS["netmrg"]["dbreaduser"] = "netmrgread";
$GLOBALS["netmrg"]["dbreadpass"] = "netmrgread";
$GLOBALS["netmrg"]["dbwriteuser"] = "netmrgwrite";
$GLOBALS["netmrg"]["dbwritepass"] = "netmrgwrite";

// Path Config
$GLOBALS["netmrg"]["fileroot"] = "/var/www/netmrg";
$GLOBALS["netmrg"]["webhost"] = "http://localhost";
$GLOBALS["netmrg"]["webroot"] = "/netmrg";



/***** SESSION *****/
session_start();
if (!isset($_SESSION["netmrgsess"]) || !is_array($_SESSION["netmrgsess"])) {
	$_SESSION["netmrgsess"] = array();
	$_SESSION["netmrgsess"]["username"] = "";
	$_SESSION["netmrgsess"]["password"] = "";
	$_SESSION["netmrgsess"]["remote_addr"] = "";
	$_SESSION["netmrgsess"]["permit"] = "";
} // end if the netmrg session array doesn't exist yet, make it



/***** INCLUDES *****/
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/database.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/menu.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/stat.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/format.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/auth.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/processing.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/snmp_caching.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/misc.php");
require_once($GLOBALS["netmrg"]["fileroot"]."/lib/graphing.php");



?>
