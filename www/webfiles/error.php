<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Error Page                            #
#           error.php                                  #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");


$errorstring = "";

// if we're not even logged in, go back to login page
if (!IsLoggedIn())
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/login.php");
} // end if not logged in

// if we have an action for this error page, output the error message
if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "denied")
	{
		$errorstring = "Your last action was DENIED";
	}

	else if ($_REQUEST["action"] == "invalid")
	{
		$errorstring = "Your last action was INVALID";
	} # end if action was denied or invalid
}
else
{
	view_redirect();
}


// display the page
	begin_page("error.php", "Error");
?>
	<div class="error">
	<?php echo "$errorstring\n"; ?>
	</div>
<?php
	end_page();

?>
