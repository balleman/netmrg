<?php
/********************************************
* NetMRG Integrator
*
* misc.php
* Misc Subroutines Module
*
* see doc/LICENSE for copyright information
********************************************/

function fix_magic_quotes($astr)
{
	if (get_magic_quotes_gpc())
	{
    	$res = stripslashes($astr);
	}
	else
    {
		$res = $astr;
	}

	return $res;
}

function store_array_in_cookie($cookiename, $array2store)
{
	$tmpstring = serialize($array2store); 
	setcookie($cookiename, $tmpstring); 
	unset($tmpstring); 
} // end store_array_in_cookie()


function get_img_tag_from_status($status)
{

	$color = get_color_from_situation($status);

	$img = ('<img src="' . get_image_by_name($color . '_led_on') . '" border="0" align="center">');

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
