// Function that will swap the display/no display for
// all content within span tags
function NavHide(MenuItem)
{
	var MenuItems = new Array('topmenu_Reporting', 'topmenu_Admin', 'topmenu_Prefs', 'topmenu_Logout', 'topmenu_Help');
	for (var i = 0; i < MenuItems.length; i++)
	{
		document.getElementById(MenuItems[i]).style.display = "none";
	} // end foreach menu item
	
	if (document.getElementById(MenuItem).style.display == "none")
	{
		document.getElementById(MenuItem).style.display = "";
	}
	else
	{
		document.getElementById(MenuItem).style.display = "none";
	}
} // end NavHide();
