<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Index Page                            #
#           index.php                                  #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);

if (!empty($action))
{
        begin_page();
?>

	<font size="5" color="#000080">Welcome to the NetMRG Integrator</font>

<?
	if ($action == "denied") {
		echo "<br><b> Your last action was DENIED </b><br>";
	} else if ($action == "invalid") {
		echo "<br><b> Your credentials were REJECTED </b><br>";
	}
	end_page();

} else {
	header("Location: login.php");

} // end if there is an action or not

?>
