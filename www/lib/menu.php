<?php
/********************************************
* NetMRG Integrator
*
* menu.php
* Menu Display Module
*
* see doc/LICENSE for copyright information
********************************************/


function display_menu()
{
	global $MENU;

	echo '<table class="menutable" cellpadding="0" cellspacing="0" border="0">'."\n";
	
	while (list($menuname, $menuitems) = each($MENU))
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
