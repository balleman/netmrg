<?

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

	$menu_q = do_query("SELECT * FROM menu WHERE state=0 ORDER BY groupid,priority");
	
	echo '<table cellpadding="0" cellspacing="0" border="0">'."\n";
	
	for ($i = 0; $i < mysql_num_rows($menu_q); $i++)
	{
		$menu_row = mysql_fetch_array($menu_q);
		
		if ($menu_row["priority"] == 0)
		{
			if ($i != 0) {
				echo "<br />\n";
				echo "</td></tr>\n";
			} // end if this is not the first

			echo '<tr><td class="menuhead">'."\n";
			echo $menu_row["label"];
			echo "</td></tr>\n";
			echo '<tr><td class="menuitem">'."\n";

		} else {
			echo '<a class="menuitem" href="'. $menu_row["link"] .'" name="'. $menu_row["caption"] .'">';
			echo $menu_row["label"];
			echo "</a><br>\n";

		} // end if it's a head item or not
	} // end foreach menu item

	echo "</td></tr>\n";
	echo "</table>\n";

} // end display_menu();


?>
