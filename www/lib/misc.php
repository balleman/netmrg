<?php
/********************************************
* NetMRG Integrator
*
* misc.php
* Misc Subroutines Module
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/

function get_img_tag_from_status($status)
{

	$color = get_color_from_situation($status);

	$img = ('<img src="' . get_image_by_name($color . '_led_on') . '" border="0" align="middle" alt="status '.$status.'" />');

	return $img;
} // end get_img_tag_from_status()


function get_color_from_situation($situation)
{
	switch ($situation)
	{
		case 0: 	$color = "blue";	break;
		case 1: 	$color = "green";	break;
		case 2: 	$color = "yellow";	break;
		case 3: 	$color = "red"; 	break;
		default:	$color = "blue";	break;
	} // end switch situation
	
	return $color;

} // end get_color_from_situation()


// seed with microseconds
function make_seed()
{
   list($usec, $sec) = explode(' ', microtime());
   return (float) $sec + ((float) $usec * 100000);
}


function htmlcolor_to_rgb($htmlcolor)
{
	$c = str_replace("#", "", $htmlcolor);
	$r1 = substr($c,0,2);
	$g1 = substr($c,2,2);
	$b1 = substr($c,4,2);
	$r = hexdec($r1);
	$g = hexdec($g1);
	$b = hexdec($b1);
	return array("r" => $r, "g" => $g, "b" => $b);
}


function rgb_to_htmlcolor($r, $g, $b)
{
	return sprintf("#%02x%02x%02x", $r, $g, $b);
}


?>
