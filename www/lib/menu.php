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

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/database.php");

function display_menu()
{

	$menu_q = do_query("SELECT * FROM menu WHERE state=0 ORDER BY groupid,priority");
	
	echo("<table border='1' width='100%' cellpadding='2' class='Menu' ");
	echo("bgcolor='" . get_color_by_name("menu_background") . "'>");
	
	for ($i = 0; $i < mysql_num_rows($menu_q); $i++)
	{
		$menu_row = mysql_fetch_array($menu_q);
		
		if ($menu_row["priority"] == 0)
		{
			if ($i != 0)
			{
				echo("</td></tr>");
			}
			echo("<tr><td class='menu' bgcolor='" . get_color_by_name("edit_main_header") . "'>");
			echo("<font color='" . get_color_by_name("edit_main_header_text") . "'><strong>");
			echo($menu_row["label"] . "</strong></font></td></tr><tr><td class='menu'>");
		} else {
			echo("<a href='" . $menu_row["link"] . "' name='" . $menu_row["caption"] . "'>");
			echo($menu_row["label"] . "</a><br>");
		}
	}
	echo("</td></tr></table>");

}
		

?>
