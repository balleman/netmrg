<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Static Data Integration Module             #
#           stat.php                                   #
#                                                      #
#     Copyright (C) 2001 Brady Alleman.                #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/database.php");

// Return the path to an image based on the internal name of the image.
function get_image_by_name($img_name) 
{
	$image = "";
	$dir   = "img";

	switch ($img_name)
	{
		// headers
		case "top"			:	$image = "$dir/newtop.png"; 		break;
		case "buffer"			:	$image = "$dir/buffer.jpg"; 		break;
		case "tex_top"			: 	$image = "$dir/tex_top.gif"; 		break;
		case "tex_side"			:	$image = "$dir/tex_side.gif";		break;
		case "tex_blue"			:	$image = "$dir/tex_blue.png";		break;
		case "tex_tan"			:	$image = "$dir/tex_tan.png";		break;

		// LEDs
		case "blue_led_on"		:	$image = "$dir/blue_led_on.gif";	break;
		case "blue_led_off"		: 	$image = "$dir/blue_led_off.gif";	break;
		case "green_led_on"		:	$image = "$dir/green_led_on.gif";	break;
		case "green_led_off"		: 	$image = "$dir/green_led_off.gif";	break;
		case "yellow_led_on"		:	$image = "$dir/yellow_led_on.gif";	break;
		case "yellow_led_off"		:	$image = "$dir/yellow_led_off.gif";	break;
		case "red_led_on"		:	$image = "$dir/red_led_on.gif";		break;
		case "red_led_off"		:	$image = "$dir/red_led_off.gif";	break;

		// Tree Expand/Collapse
		case "hide"			:	$image = "$dir/hide.gif";		break;
		case "show"			: 	$image = "$dir/show.gif";		break;
	}

	return $image;

} // end get_image_by_name


$alt_color = 0;

function get_color_by_name($color_name) {

	GLOBAL $alt_color;

	$color = "#FFFFFF";

	switch ($color_name)
	{
		case "site_background"		:	$color = "#EDEBEB"; break;
		case "site_text"		:	$color = "#000000"; break;
		case "site_link"		:	$color = "#076D07"; break;
		case "site_vlink"		:	$color = "#076D07"; break;
		case "site_alink"		:	$color = "#FF0000"; break;
		case "edit_header"		:	$color = "#000088"; break;
		case "edit_fields"		:
		{
			if ($alt_color == 0)
			{
				$alt_color = 1;
				$color = "#CBD9E7";
			} else {
				$alt_color = 0;
				$color = "#B9C9D9";
			}
			break;
		}
		case "edit_header_text"		:	$color = "#C0C0C0"; break;
		case "edit_main_header"		:	$color = "#005000"; break;
		case "edit_main_header_text"	:	$color = "#C0C0C0"; break;
		case "menu_background"		:	$color = "#D9D9D9"; break;
	}

	return $color;


} # end get_color_by_name

function get_path_by_name($path_name)
{
	// Return the path of the specified object

	$query_handle = do_query("SELECT * FROM static_paths WHERE name=\"$path_name\"");
	$row = mysql_fetch_array($query_handle);

	return $row["path"];

} // end get_path_by_name

function netmrg_root()
{
	return get_path_by_name("root");
}

function get_site_name()
{
	return $GLOBALS["netmrg"]["company"];
}


?>
