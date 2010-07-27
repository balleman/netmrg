<?php
/********************************************
* NetMRG Integrator
*
* stat.php
* Static Data Integration Module
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


/***** CONSTANTS *****/
$GLOBALS["netmrg"]["verhist"] = array(
	"0.10pre1" => 1,
	"0.10pre2" => 2,
	"0.10" => 3,
	"0.12" => 4,
	"0.13" => 5,
	"0.14" => 6,
	"0.15" => 7,
	"0.16" => 8,
	"0.17" => 9,
	"0.18" => 10,
	"0.18.1" => 11,
	"0.18.2" => 12,
	"0.19" => 13,
	"0.19.1" => 14,
	"0.20" => 15,
); // end verhist


$MENU = array(
	"Reporting" => array(
		array("name" => "Tree", "link" => "device_tree.php", "descr" => "", "authLevelRequired" => 0),
//		array("name" => "Device", "link" => "device_centered.php", "descr" => "", "authLevelRequired" => 0),
		array("name" => "Log", "link" => "event_log.php", "descr" => "Display a list of the most recent events.", "authLevelRequired" => 1),
		array("name" => "Slide Show", "link" => "view.php?action=slideshow&type=0", "descr" => "Displays all devices, one page at a time.", "authLevelRequired" => 1),
		array("name" => "Search", "link" => "search.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "View", "link" => "view.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Enclose Graph", "link" => "enclose_graph.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Display Graph", "link" => "get_graph.php", "descr" => "", "authLevelRequired" => 0, "display" => false)
	),
	"Admin" => array(
		array("name" => "Devices", "link" => "grpdev_list.php", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Device Types", "link" => "dev_types.php", "descr" => "", "authLevelRequired" => 2),
		array("name" => "Device Properties", "link" => "dev_props.php", "descr" => "", "authLevelRequired" => 2, "display" => false),
		array("name" => "Notifications", "link" => "notifications.php", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Custom Graphs", "link" => "graphs.php?type=custom", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Template Graphs", "link" => "graphs.php?type=template", "descr" => "", "authLevelRequired" => 1),
		array("name" => "Script", "link" => "tests_script.php", "descr" => "External Programs", "authLevelRequired" => 2),
		array("name" => "SNMP", "link" => "tests_snmp.php", "descr" => "SNMP Queries", "authLevelRequired" => 2),
		array("name" => "SQL", "link" => "tests_sql.php", "descr" => "Database Queries", "authLevelRequired" => 2),
		array("name" => "Users", "link" => "users.php", "descr" => "User Management", "authLevelRequired" => 3),
		array("name" => "Legacy Device Admin", "link" => "devices.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Legacy Group Admin", "link" => "groups.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Color Picker", "link" => "color_dialog.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Conditions", "link" => "conditions.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Events", "link" => "events.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Graph", "link" => "graphs.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Graph Items", "link" => "graph_items.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Monitors", "link" => "monitors.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Recache", "link" => "recache.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Responses", "link" => "responses.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "SNMP Cache View", "link" => "snmp_cache_view.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Sub-Devices", "link" => "sub_devices.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Sub-Device Parameters", "link" => "sub_dev_param.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Web Updater", "link" => "updater.php", "descr" => "", "authLevelRequired" => 0, "display" => false)
	),
	"Prefs" =>  array(
		array("name" => "Prefs", "link" => "user_prefs.php", "descr" => "Personal Preferences", "authLevelRequired" => 0)
		),
	"Logout" => array(
		array("name" => "Logout", "link" => "logout.php", "descr" => "End your NetMRG Session.", "authLevelRequired" => 0),
		array("name" => "Login", "link" => "login.php", "descr" => "", "authLevelRequired" => 0, "display" => false)
		),
	"Help" => array(
		array("name" => "About", "link" => "about.php", "descr" => "", "authLevelRequired" => 0),
		array("name" => "Manual", "link" => "http://wiki.netmrg.net/wiki/Users_Manual", "descr" => "", "authLevelRequired" => 0),
		array("name" => "Forum", "link" => "http://lists.netmrg.net/", "descr" => "Benefit from the NetMRG Community.", "authLevelRequired" => 0),
		array("name" => "Bugs", "link" => "http://bugs.netmrg.net/", "descr" => "Report bugs and request features.", "authLevelRequired" => 0),
		array("name" => "Contributions", "link" => "contributors.php", "descr" => "", "authLevelRequired" => 0, "display" => false),
		array("name" => "Error", "link" => "error.php", "descr" => "", "authLevelRequired" => 0, "display" => false)
	)
); // end $MENU

// add a dynamic 'resume slide show' link
$rss_action = (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "slideshow") ? "slideshow" : "";
if ( !empty($_SESSION["netmrgsess"]["slideshow"])
	&& count($_SESSION["netmrgsess"]["slideshow"]["views"]) != $_SESSION["netmrgsess"]["slideshow"]["current"]
	&& $_SESSION["netmrgsess"]["slideshow"]["current"] != 0
	&& $rss_action != "slideshow")
{
	$rss_jump = $_SESSION["netmrgsess"]["slideshow"]["current"] - 1;
	array_push($MENU["Reporting"], array("name" => "&nbsp&nbsp;Resume Slide Show", "link" => "view.php?action=slideshow&jump=$rss_jump", "descr" => "Resumes slide show in progress.", "authLevelRequired" => 1));
} // end if in the middle of a slide show

// local menu created by CreateLocalMenu()
//   for just this user with their entries they can see
$LOCAL_MENU = array();
// the current main nav group we're under
$LOCAL_MENU_CURTREE;
$LOCAL_MENU_CURITEM;

$PERMIT = array(
	'Disabled'       => -1,
	'SingleViewOnly' => 0,
	'ReadAll'        => 1,
	'ReadWrite'      => 2,
	'Admin'          => 3
); // end $PERMIT

$ALIGN_ARRAY = array(
	1 => "Left",
	2 => "Right",
	3 => "Right Split"
); // end ALIGN_ARRAY

$RRDTOOL_ITEM_TYPES = array(
	1 => "LINE1",
	2 => "LINE2",
	3 => "LINE3",
	4 => "AREA",
	5 => "STACK"
); // end RRDTOOL_ITEM_TYPES

$RRDTOOL_CFS = array(
	1 => "AVERAGE",
	2 => "MAX"
); // end RRDTOOL_CFS

$SPECIAL_MONITORS = array(
	-1 => "-Fixed Value-",
	-2 => "-Sum of all graph items-"
); // end SPECIAL_MONITORS

$TEST_TYPES = array(
	1 => "Script",
	2 => "SNMP",
	3 => "SQL",
	4 => "Internal"
); // end TEST_TYPES

$PERMIT_TYPES = array(
	$PERMIT["SingleViewOnly"] => "Single View Only",
	$PERMIT["ReadAll"]        => "Read All",
	$PERMIT["ReadWrite"]      => "Read/Write",
	$PERMIT["Admin"]          => "Read/Write/User Admin"
); // end PERMIT_TYPES

$SUB_DEVICE_TYPES = array(
	1 => "Group",
	2 => "Network Interface",
	3 => "Disk"
); // end SUB_DEVICE_TYPES

// bread crumb type order
$BC_TYPES = array(
	"group"                => 0,
	"device"               => 1,
	"int_snmp_cache_view"  => 2,
	"disk_snmp_cache_view" => 2,
	"sub_device"           => 3,
	"monitor"              => 4,
	"event"                => 5
); // end BC_TYPES

$TRIGGER_TYPES = array(
	1 => "On Change",
	2 => "Never (disabled)"
); // end TRIGGER_TYPES

$SITUATIONS = array(
	0 => "Disabled",
	1 => "Normal",
	2 => "Warning",
	3 => "Critical"
); // end SITUATIONS

$LOGIC_CONDITIONS = array(
	0 => "AND",
	1 => "OR"
); // end LOGIC_CONDITIONS

$CONDITIONS = array(
	0 => "&lt;",
	1 => "=",
	2 => "&gt;",
	3 => "&le;",
	4 => "&ne;",
	5 => "&ge;"
); // end CONDITIONS

$VALUE_TYPES = array(
	0 => "Current Value",
	1 => "Delta Value",
	2 => "Rate of Change",
	3 => "Last Value"
); // end VALUE_TYPES

$VIEW_ITEM_TYPES = array(
	'graph'     => "Graph",
	'template'  => "Templated Graph",
	'separator' => "Separator"
); // end VIEW_TIME_TYPES

$INTERFACE_STATUS = array(
	1 => "Up",
	2 => "Down",
	3 => "Testing",
	4 => "Unknown",
	5 => "Dormant",
	6 => "Not Present",
	7 => "Lower Layer Down"
); // end INTERFACE_STATUS

$INTERFACE_TYPE = array(
	1   => "Other",
	6   => "Ethernet",
	15  => "FDDI",
	18  => "DS1",
	20  => "BRI",
	21  => "PRI",
	22  => "PTP Serial",
	23  => "PPP",
	24  => "Loopback",
	28  => "SLIP",
	32  => "Frame Relay",
	33  => "RS232",
	37  => "ATM",
	39  => "SDH",
	45  => "V.35",
	46  => "HSSI",
	47  => "HIPPI",
	49  => "AAL5",
	53  => "Virtual",
	71  => "802.11",
	107 => "IMA",
	117 => "Gigabit Ethernet",
	134 => "ATM Subinterface"
); // end INTERFACE_TYPE

$SNMP_VERSIONS = array(
	0 => "No SNMP Support",
	1 => "SNMPv1",
	2 => "SNMPv2c",
	3 => "SNMPv3"
); // end SNMP_VERSIONS

$SNMP_SECLEVS = array(
	0 => "No Authentication, No Privacy",
	1 => "Authentication, No Privacy",
	2 => "Authentication, Privacy"
); // end SNMP_SECLEVS

$SNMP_APROTS = array(
	0 => "MD5",
	1 => "SHA"
); // end SNMP_APROTS

$SNMP_PPROTS = array(
	0 => "DES",
	1 => "AES"
); // end SNMP_PPROTS

$RECACHE_METHODS = array(
	0 => "Never refresh cache",
	1 => "Refresh on SNMP agent restart",
	2 => "Refresh on interface count change",
	3 => "Refresh on interface count mismatch",
	4 => "Always refresh cache"
); // end RECACHE_METHODS

$SCRIPT_DATA_TYPES = array(
	1 => "Error Code",
	2 => "Standard Out"
); // end SCRIPT_DATA_TYPES

// searches through various types looking for |ARG|
// |ARG| should already by escaped before being used in these queries
// SQL statements should return two items; the id and the matched name
$SEARCH_ITEMS = array(
	'group' => array(
		'name' => "Group",
		'sql' => array("SELECT id, name FROM groups 
			WHERE name LIKE '%|ARG|%'")
		),
	'device' => array(
		'name' => "Device",
		'sql' => array("SELECT id, name FROM devices 
			WHERE name LIKE '%|ARG|%' OR ip LIKE '%|ARG|%'",
			"SELECT dev_id AS id, name FROM dev_prop_vals val LEFT JOIN devices dev ON val.dev_id=dev.id WHERE value LIKE '%|ARG|%'")
		),
	'subdevice' => array(
		'name' => "Sub Device",
		'sql' => array("SELECT id, name FROM sub_devices 
			WHERE name LIKE '%|ARG|%
			GROUP BY id'",
			"SELECT sub_dev_id AS id, CONCAT(name, ' - ', value) AS name FROM sub_dev_variables
			WHERE value LIKE '%|ARG|%'
			GROUP BY sub_dev_id")
		)
); // end SEARCH_ITEMS

$TIMEFRAME_DAILY = array(
	'name'       => "Daily",
	'start_time' => "-108000",
	'end_time'   => "-360",
	'break_time' => (time() - (date("s") + date("i") * 60 + date("H") * 3600)),
	'sum_label'  => "24 Hour",
	'sum_time'   => "86400",
	'show_max'   => false
); // end TIMEFRAME_DAILY

$TIMEFRAME_WEEKLY = array(
	'name'       => "Weekly",
	'start_time' => "-777600",
	'end_time'   => "-360",
	'break_time' => (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("w") * 86400)),
	'sum_label'  => "7 Day",
	'sum_time'   => "604800",
	'show_max'   => true
); // end TIMEFRAME_WEEKLY

$TIMEFRAME_MONTHLY = array(
	'name'       => "Monthly",
	'start_time' => "-3628800",
	'end_time'   => "-360",
	'break_time' => (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("d") * 86400)),
	'sum_label'  => "4 Week",
	'sum_time'   => "2419200",
	'show_max'   => true
); // end TIMEFRAME_MONTHLY

$TIMEFRAME_YEARLY = array(
	'name'       => "Yearly",
	'start_time' => "-36720000",
	'end_time'   => "-360",
	'break_time' => (time() - (date("s") + date("i") * 60 + date("H") * 3600 + date("z") * 86400)),
	'sum_label'  => "1 Year",
	'sum_time'   => "31536000",
	'show_max'   => true
); // end TIMEFRAME_YEARLY

$TIMEFRAMES = array( $TIMEFRAME_DAILY, $TIMEFRAME_WEEKLY, $TIMEFRAME_MONTHLY, $TIMEFRAME_YEARLY );



// Return the path to an image based on the internal name of the image.
function get_image_by_name($img_name)
{
	$image = "";
	
	switch ($img_name)
	{
		// graphics
		case "applytemplate" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['applytemplate']}";
			break;
		case "edit" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['edit']}";
			break;
		case "logo" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['logo']}";
			break;
		case "delete" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['delete']}";
			break;
		case "disk" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['disk']}";
			break;
		case "duplicate" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['duplicate']}";
			break;
		case "parameters" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['parameters']}";
			break;
		case "arrow-up" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-up']}";
			break;
		case "arrow-right" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-right']}";
			break;
		case "arrow-down" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-down']}";
			break;
		case "arrow-left" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-left']}";
			break;
		case "arrow-up-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-up-disabled']}";
			break;
		case "arrow-right-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-right-disabled']}";
			break;
		case "arrow-down-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-down-disabled']}";
			break;
		case "arrow-left-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow-left-disabled']}";
			break;
		case "arrow_limit-up" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-up']}";
			break;
		case "arrow_limit-right" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-right']}";
			break;
		case "arrow_limit-down" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-down']}";
			break;
		case "arrow_limit-left" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-left']}";
			break;
		case "arrow_limit-up-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-up-disabled']}";
			break;
		case "arrow_limit-right-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-right-disabled']}";
			break;
		case "arrow_limit-down-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-down-disabled']}";
			break;
		case "arrow_limit-left-disabled" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['arrow_limit-left-disabled']}";
			break;
		case "slideshow" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['slideshow']}";
			break;
		case "recachedisk" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['recachedisk']}";
			break;
		case "recacheproperties" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['recacheproperties']}";
			break;
		case "viewdisk" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['viewdisk']}";
			break;
		case "recacheinterface" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['recacheinterface']}";
			break;
		case "viewinterface" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['viewinterface']}";
			break;
		case "view" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['view']}";
			break;
		case "viewgraph-on" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['viewgraph-on']}";
			break;
		case "viewgraph-off" :
			$image = "{$GLOBALS['netmrg']['imagedir']}/{$GLOBALS['netmrg']['imagespec']['viewgraph-off']}";
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
		case "site_background" : $color = "#EDEBEB"; break;
		case "site_text"       : $color = "#000000"; break;
		case "site_link"       : $color = "#076D07"; break;
		case "site_vlink"      : $color = "#076D07"; break;
		case "site_alink"      : $color = "#FF0000"; break;
		case "edit_header"     : $color = "#000088"; break;
		case "edit_fields"     :
		{
			if ($alt_color == 0)
			{
				$alt_color = 1;
				$color = "#CECECE";
			}
			else
			{
				$alt_color = 0;
				$color = "#E4E4E4";
			}
			break;
		}
		case "edit_header_text"      : $color = "#C0C0C0"; break;
		case "edit_main_header"      : $color = "#005000"; break;
		case "edit_main_header_text" : $color = "#C0C0C0"; break;
		case "menu_background"       : $color = "#D9D9D9"; break;
	}
	
	return $color;
} // end get_color_by_name


?>
