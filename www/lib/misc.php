<?php
/********************************************
* NetMRG Integrator
*
* misc.php
* Misc Subroutines Module
*
* see doc/LICENSE for copyright information
********************************************/


function store_array_in_cookie($cookiename, $array2store)
{
	$tmpstring = serialize($array2store); 
	setcookie($cookiename, $tmpstring); 
	unset($tmpstring); 
} // end store_array_in_cookie()


function get_img_tag_from_status($status)
{

	$color = get_color_from_situation($status);

	$img = ("<img src=\"" . get_image_by_name($color . "_led_on") . "\" border=\"0\">");

	return $img;
} // end get_img_tag_from_status()


function get_color_from_situation($situation)
{
	switch ($situation) {
		case 0: $color = "blue"; break;
		case 1: $color = "green"; break;
		case 2: $color = "yellow"; break;
		case 3: $color = "red"; break;
		default: $color = "blue"; break;
	} // end switch situation
	
	return $color;

} // end get_color_from_situation()


?>
