<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Menu Display Module                        #
#           menu.php                                   #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################


function display_menu()
{

	$menu = array(
		"Monitoring" => array(
			array("name" => "Groups", "link" => "mon_groups.php", "descr" => "", "authLevelRequired" => 1),
			array("name" => "Device Types", "link" => "mon_device_types.php", "descr" => "", "authLevelRequired" => 2),
			array("name" => "Notifications", "link" => "mon_notify.php", "descr" => "", "authLevelRequired" => 2)
		),
		"Reporting" => array(
			array("name" => "Device Tree", "link" => "device_tree.php", "descr" => "", "authLevelRequired" => 1),
			array("name" => "Event Log", "link" => "event_log.php", "descr" => "Display a list of the most recent events.", "authLevelRequired" => 1)
		),
		"Graphing" => array(
			array("name" => "Custom Graphs", "link" => "custom_graphs.php", "descr" => "", "authLevelRequired" => 1)
		),
		"Tests" => array(
			array("name" => "Scripts", "link" => "tests_script.php", "descr" => "External Programs", "authLevelRequired" => 2),
			array("name" => "SNMP", "link" => "tests_snmp.php", "descr" => "SNMP Queries", "authLevelRequired" => 2),
			array("name" => "SQL", "link" => "tests_sql.php", "descr" => "Database Queries", "authLevelRequired" => 2)
		),
		"Admin" => array(
			array("name" => "Users", "link" => "users.php", "descr" => "User Management", "authLevelRequired" => 3),
			array("name" => "Logout", "link" => "logout.php", "descr" => "End your NetMRG Session.", "authLevelRequired" => 0)
		),
		"Help" => array(
			array("name" => "About", "link" => "about.php", "descr" => "", "authLevelRequired" => 0)
		)
	); // end $menu

	
	echo '<table class="menutable" cellpadding="0" cellspacing="0" border="0">'."\n";
	
	while (list($menuname, $menuitems) = each($menu))
	{
		// foreach menu item
		$item_output = "";
		foreach ($menuitems as $menuitem)
		{
			if (get_permit() >= $menuitem["authLevelRequired"])
			{
				$item_output .= '<tr><td class="menuitem">'."\n";
				$item_output .= '<a class="menuitem" href="'. $menuitem["link"] .'" name="'. $menuitem["descr"] .'">';
				$item_output .= $menuitem["name"];
				$item_output .= "</a><br />\n";
				$item_output .= "</td></tr>\n";
			} // end if we have enough permissions to view this link
		} // end foreach menu item

		// if we had some item output (ie, we had auth to view at least ONE item in this submenu)
		if (!empty($item_output))
		{
			// output the head
			echo '<tr><td class="menuhead">'."\n";
			echo $menuname;
			echo "</td></tr>\n";

			// echo the items
			echo $item_output;

			// echo the bottom part
			echo '<tr><td class="menuitem">'."\n";
			echo "<br />\n";
			echo "</td></tr>\n";
		} // end if item output wasn't empty

	} // end while we still have menu items

	echo "</table>\n";

} // end display_menu();


?>
