<?php
/********************************************
* NetMRG Integrator
*
* stat.php
* Static Data Integration Module
*
* see doc/LICENSE for copyright information
********************************************/


/***** CONSTANTS *****/
$ALIGN_ARRAY = array(
			1	=>	"Left",
			2	=>	"Right",
			3	=>	"Right Split");

$RRDTOOL_ITEM_TYPES = array(

			1	=>	"LINE1",
			2	=>	"LINE2",
			3	=>	"LINE3",
			4	=>	"AREA",
			5	=>	"STACK");

$SPECIAL_MONITORS = array(
			-1	=>	"-Fixed Value-",
			-2	=>	"-Sum of all graph items-");

$TEST_TYPES = array(
			1	=>	"Script",
			2	=>	"SNMP",
			3	=>	"SQL",
			4	=>	"Internal");

$PERMIT_TYPES = array(
			0	=>	"Single View Only",
			1	=>	"Read All",
			2	=>	"Read/Write",
			3	=>	"Read/Write/User Admin");

$SUB_DEVICE_TYPES = array(
			1	=>	"Group",
			2	=>	"Interface",
			3	=>	"Disk");

$TRIGGER_TYPES = array(
			1	=>	"On Change",
			2	=>	"Never (disabled)");

$SITUATIONS = array(
			0	=>	"Disabled",
			1	=>	"Normal",
			2	=>	"Warning",
			3	=>	"Critical");

$LOGIC_CONDITIONS = array(
			0	=>	"AND",
			1	=>	"OR");

$CONDITIONS = array(
			0	=>	"&lt;",
			1	=>	"=",
			2	=>	"&gt;",
			3	=>	"&le;",
			4	=>	"&ne;",
			5	=>	"&ge;");

$VALUE_TYPES = array(
			0	=>	"Current Value",
			1	=>	"Delta Value",
			2	=>	"Rate of Change");
			
$VIEW_ITEM_TYPES = array(
		'graph'		=>	"Graph",
		'template'      =>	"Templated Graph",
		'separator'	=>	"Separator");

$INTERFACE_STATUS = array(
			1	=>	"Up",
			2	=>	"Down",
			3	=>	"Testing",
			4	=>	"Unknown",
			5	=>	"Dormant",
			6	=>	"Lower Layer Down"
			);
			
$INTERFACE_TYPE = array(
                        6	=>	"Ethernet",
			15	=>	"FDDI",
			18	=>	"DS1",
			20	=>	"BRI",
			21	=>	"PRI",
			22	=>	"PTP Serial",
			23	=>	"PPP",
			24	=>	"Loopback",
			28	=>	"SLIP",
			32	=>	"Frame Relay",
			37	=>	"ATM",
			39	=>	"SDH",
			45	=>	"V.35",
			46	=>	"HSSI",
			47	=>	"HIPPI",
			49	=>	"AAL5",
			53	=>	"Virtual",
			71	=>	"802.11",
			107	=>	"IMA",
			117	=>	"Gigabit Ethernet"
			);


$MENU = array(
	"Monitoring" => array(
		array("name" => "Groups", "link" => "groups.php", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Device Types", "link" => "dev_types.php", "descr" => "", "authLevelRequired" => 2),
		array("name" => "Notifications", "link" => "notifications.php", "descr" => "", "authLevelRequired" => 2)
	),
	"Reporting" => array(
		array("name" => "Device Tree", "link" => "device_tree.php", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Event Log", "link" => "event_log.php", "descr" => "Display a list of the most recent events.", "authLevelRequired" => 1)
	),
	"Graphing" => array(
		array("name" => "Custom Graphs", "link" => "graphs.php?type=custom", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Template Graphs", "link" => "graphs.php?type=template", "descr" => "", "authLevelRequired" => 1)
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
); // end $MENU


// Return the path to an image based on the internal name of the image.
function get_image_by_name($img_name)
{
	$image = "";

	switch ($img_name)
	{
		// graphics
		case "logo" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['logo']}";
			break;
		case "disk" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['disk']}";
			break;
		case "arrow" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow']}";
			break;
		case "viewgraph" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['viewgraph']}";
			break;

		// LEDs
		case "blue_led_on" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-unknown-trig']}";
			break;
		case "blue_led_off" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-unknown-untrig']}";
			break;
		case "green_led_on" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-good-trig']}";
			break;
		case "green_led_off" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-good-untrig']}";
			break;
		case "yellow_led_on" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-warning-trig']}";
			break;
		case "yellow_led_off" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-warning-untrig']}";
			break;
		case "red_led_on" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-critical-trig']}";
			break;
		case "red_led_off" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['status-critical-untrig']}";
			break;

		// Tree Expand/Collapse
		case "hide"  :  $image = "{$GLOBALS['netmrg']['staticimagedir']}/hide.gif"; break;
		case "show"  :  $image = "{$GLOBALS['netmrg']['staticimagedir']}/show.gif"; break;
	}

	return $image;
} // end get_image_by_name();


$alt_color = 0;

function get_color_by_name($color_name)
{
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
				//$color = "#CBD9E7";
				$color = "#CECECE";
			}
			else
			{
				$alt_color = 0;
				//$color = "#B9C9D9";
				$color = "#E4E4E4";
			}
			break;
		}
		case "edit_header_text"		:	$color = "#C0C0C0"; break;
		case "edit_main_header"		:	$color = "#005000"; break;
		case "edit_main_header_text"	:	$color = "#C0C0C0"; break;
		case "menu_background"		:	$color = "#D9D9D9"; break;
	}

	return $color;
} // end get_color_by_name


?>
