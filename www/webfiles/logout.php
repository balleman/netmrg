<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Logout Page                           #
#           logout.php                                 #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");


ResetAuth();
header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");

?>
