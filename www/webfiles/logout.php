<?php
/********************************************
* NetMRG Integrator
*
* logout.php
* Site Logout Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");


ResetAuth();
header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");

?>
