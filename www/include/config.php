<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Static Configuration Module                #
#           config.php                                 #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

/** error_reporting **/
error_reporting(E_ALL);
//error_reporting(E_WARNING | E_ERROR);


/***** CONFIG *****/
// DB Config
$_GLOBALS["netmrg"]["dbhost"] = "localhost";
$_GLOBALS["netmrg"]["dbname"] = "netmrg";
$_GLOBALS["netmrg"]["dbreaduser"] = "netmrgread";
$_GLOBALS["netmrg"]["dbreadpass"] = "netmrgread";
$_GLOBALS["netmrg"]["dbwriteuser"] = "netmrgwrite";
$_GLOBALS["netmrg"]["dbwritepass"] = "netmrgwrite";

// Path Config
$_GLOBALS["netmrg"]["fileroot"] = "/var/www/netmrg";
$_GLOBALS["netmrg"]["webroot"] = "/netmrg";

// Cosmetic Variables
$_GLOBALS["netmrg"]["name"] = "NetMRG";
$_GLOBALS["netmrg"]["company"] = "Generic Company";

?>