// Function that will swap the display/no display for
// all content within span tags
function NavHide(MenuItem)
{
	if (document.getElementById(MenuItem).style.display == "none")
	{
		document.getElementById(MenuItem).style.display = "";
	}
	else
	{
		document.getElementById(MenuItem).style.display = "none";
	}
} // end NavHide();
