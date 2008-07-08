<?PHP
/**
* format.php
*
* Functions used to format various parts of NetMRG
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
*
* @author Brady Alleman <brady@thtech.net>
* @author Douglas E. Warner <silfreed@silfreed.net>
*/


/*********************************************
*
*        Site-Wide Display Functions
*
*********************************************/

/**
* begin_page()
*
* starts the page w/ basic HTML
*
* @param string $pagename    page that this is
* @param string $prettyname  shown in the title bar
* @param boolean $refresh    whether to refresh this page or not
* @param string $bodytags    options for the <body> tag
*/
function begin_page($pagename = "", $prettyname = "", $refresh = false, $bodytags = "", $javascript_files = array())
{
	// gather errors from prerequisits being met or not
	$prereqs_errors = PrereqsMet();
	$display_menu = false;
	
	// always include these javascripts
	$extra_jscript_files = $javascript_files;
	$javascript_files = array();
	$javascript_files[] = 'onload.js';
	$javascript_files[] = 'inputdefault.js';
	$javascript_files[] = 'xajax.js';
	$javascript_files[] = 'select.js';
	$javascript_files = array_merge($javascript_files, $extra_jscript_files);
	
	// determine if we should display the menu or not
	$display_menu = (IsLoggedIn() && !UpdaterNeedsRun() && count($prereqs_errors) == 0);
	
	DisplayPageHeader($pagename, $prettyname, $refresh, $display_menu, $bodytags, $javascript_files);
?>

<div id="content">
<?php
	CheckInstallState($prereqs_errors);
} // end begin_page()


/**
* end_page();
*
* defines things that go at the end of the page
*
*/
function end_page() 
{
?>
</div> <!-- end #content -->

<div id="footer">
	<a href="http://www.netmrg.net/">
	<img src="<?php echo get_image_by_name("logo"); ?>" alt="logo" />
	NetMRG
	</a>
	<a href="about.php">&copy; 2003-2007</a>
</div> <!-- end #footer -->

</body>
</html>
<?php
} // end end_page()


/**
* DisplayPageHeader()
*
* draws the top of the page
*
* @param string $pagename       page that this is
* @param string $prettyname     shown in the title bar
* @param boolean $display_menu  whether to refresh this page or not
* @param boolean $refresh       whether to refresh this page or not
* @param string $bodytags       options for the <body> tag
*/
function DisplayPageHeader($pagename = "", $prettyname = "", $refresh = false, $display_menu = false, $bodytags = "", $javascript_files = array())
{
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php print GetPageTitle($prettyname); ?></title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/include/netmrg.css" />
	
<?php
	$GLOBALS['xajax']->printJavascript("", "xajax_js/xajax.js", "");
	if (count($javascript_files) > 0)
	{
		foreach ($javascript_files as $jsfile)
		{
			echo '	<script language="javascript" type="text/javascript" src="'.$GLOBALS["netmrg"]["webroot"].'/include/'.$jsfile.'"></script>'."\n";
		} // end foreach jsfile
	} // end if we have javascript to load
?>
	
<?php if ($refresh) { ?>
	<meta http-equiv="refresh" content="300" />
<?php } ?>
</head>

<body <?php echo($bodytags); ?>>
<?php if (!empty($pagename)) { ?>
<!-- <?php echo $pagename; ?> -->
<?php } // end if there's a pagename, output it ?>

<div id="header">
	<div id="headerinfo">
		<div id="logindata">
			<?php echo GetLoginInfo(); ?>
		</div>
		<div id="search">
<?php if (IsLoggedIn()) { ?>
			<form name="searchform" action="search.php" method="GET">
				<input type="text" name="query" default="[search]" size="15" />
			</form>
<?php } // end if logged in ?>
		</div>
	</div> <!-- end #headerinfo -->
	<div id="company">
		<a href="<?php echo $GLOBALS["netmrg"]["companylink"]; ?>">
		<?php echo(space_to_nbsp($GLOBALS["netmrg"]["company"])); ?>
		</a>
	</div>
<?php
	// display menu or not
	if ($display_menu)
	{
		DisplayTopMenu();
	} // end if display the menu
?>
</div> <!-- end #header -->

<?php
	echo '<div style="clear: left; height: 0;">&nbsp;</div>'."\n";
} // end DisplayPageHeader();


/**
* DisplayTopMenu();
*
* Displays a menu bar along the top of the page
*/
function DisplayTopMenu()
{
	global $LOCAL_MENU, $LOCAL_MENU_CURTREE, $LOCAL_MENU_CURITEM;
	
	CreateLocalMenu();
	
	echo '<div id="topmenu">'."\n";
	echo "	<ul>\n";
	while (list($menuname, $menuitems) = each($LOCAL_MENU))
	{
		echo '		<li><a '. ($menuname == $LOCAL_MENU_CURTREE ? 'class="navcurrent" ' : '') .'href="'. $menuitems[0]["link"] .'">';
		echo $menuname;
		echo "</a></li>\n";
	} // end while we still have menu items
	echo "	</ul>\n\n";
	echo "	&nbsp;\n";
	echo '	<div style="clear: left;"></div>'."\n";
	echo "</div> <!-- end #topmenu -->\n\n";
	
	echo '<div id="secondmenu">'."\n";
	echo "	<ul>\n";
	foreach ($LOCAL_MENU[$LOCAL_MENU_CURTREE] as $menuitem)
	{
		echo '		<li><a '. ($LOCAL_MENU_CURITEM == $menuitem['link'] ? 'class="navcurrent" ' : '') .'title="'. $menuitem["descr"] .'" href="'. $menuitem["link"] .'">';
		echo $menuitem["name"];
		echo "</a></li>\n";
	} // end while we still have menu items
	echo "	</ul>\n\n";
	echo "	&nbsp;\n";
	echo '	<div style="clear: left;"></div>'."\n";
	echo "</div> <!-- end #secondmenu -->\n\n";
} // end DisplayTopMenu();


/**
* PrepGroupNavHistory($type, $id)
*
* prepares a nav bar that is used along the tops of the pages under 'Groups'
* and keeps a history of where you've been
*
* $type = (group, device, int_snmp_cache_view, disk_snmp_cache_view, 
*         sub_device, monitor, event)
* $id = <id of type you are in>
*/
function PrepGroupNavHistory($type, $id)
{
	global $BC_TYPES;
	
	// default trip id
	if (empty($_REQUEST["tripid"]))
	{
		srand(make_seed());
		$_REQUEST["tripid"] = md5(time()*rand());
	} // end if no trip id
	$tripid = $_REQUEST["tripid"];
	
	// setup array to hold group nav
	if (!isset($_SESSION["netmrgsess"]["grpnav"]))
	{
		$_SESSION["netmrgsess"]["grpnav"] = array();
	} // end if no group nav array
	if (!isset($_SESSION["netmrgsess"]["grpnav"][$tripid]))
	{
		$_SESSION["netmrgsess"]["grpnav"][$tripid] = array();
	} // end if no group nav trip array
	
	// push type onto breadcrumbs
	/**
	* breadcrumbs = array(
	*   "type" => (group|device|sub_device|monitor|event),
	*   "id"   => x
	* );
	*/
	$last_type = "";
	$last_id = "";
	if (count($_SESSION["netmrgsess"]["grpnav"][$tripid]) != 0)
	{
		$last_type = $_SESSION["netmrgsess"]["grpnav"][$tripid][count($_SESSION["netmrgsess"]["grpnav"][$tripid])-1]["type"];
		$last_id = $_SESSION["netmrgsess"]["grpnav"][$tripid][count($_SESSION["netmrgsess"]["grpnav"][$tripid])-1]["id"];
	} // end if we have some bread crumbs already
	if (count($_SESSION["netmrgsess"]["grpnav"][$tripid]) == 0
		|| $BC_TYPES[$last_type] <= $BC_TYPES[$type])
	{
		if (($type == $last_type && $id != $last_id)
			|| ($id == $last_id && $type != $last_type)
			|| ($type != $last_type && $id != $last_id))
		{
			$found = false;
			foreach ($_SESSION["netmrgsess"]["grpnav"][$tripid] as $triparr)
			{
				if ($triparr["type"] == $type && $triparr["id"] == $id)
				{
					$found = true;
					break;
				} // end if found our trip info
			} // end foreach trip breadcrumb
			if (!$found)
			{
				array_push($_SESSION["netmrgsess"]["grpnav"][$tripid], array("type" => $type, "id" => $id));
			} // end if we haven't already pushed this item on
		} // end if type and id are different from last 
	} // end if we can push the breadcrumb onto our history
} // end PrepGroupNavHistory();


/**
* DrawGroupNavHistory($type)
*
* draws a nav bar along the tops of the pages under 'Groups'
* 
* $type = (group, device, int_snmp_cache_view, disk_snmp_cache_view, 
*         sub_device, monitor, event)
* $id = <id of type you are in>
*/
function DrawGroupNavHistory($type, $id)
{
	global $BC_TYPES;
	$tripid = $_REQUEST["tripid"];
	
	// loop through each breadcrumb and display it
?>
	<table style="border-collapse: collapse;" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="editmainheader">
		History
<?php
	$count = 0;
	foreach ($_SESSION["netmrgsess"]["grpnav"][$tripid] as $breadcrumb)
	{
		// skip to the end if we've past where we should be
		if ($BC_TYPES[$breadcrumb["type"]] > $BC_TYPES[$type])
		{
			$_SESSION["netmrgsess"]["grpnav"][$tripid] = array_slice($_SESSION["netmrgsess"]["grpnav"][$tripid], 0, $count);
			continue;
		} // end if we're past our current type
		// cut of extra groups
		if ($type == "group" && $breadcrumb["type"] == "group"
			&& $breadcrumb["id"] != $id
			&& !in_array($breadcrumb["id"], GetGroupParents($id))
			)
		{
			$_SESSION["netmrgsess"]["grpnav"][$tripid] = array_slice($_SESSION["netmrgsess"]["grpnav"][$tripid], 0, $count);
			break;
		} // end if group and not our parent or ourself
		
		// display the proper link
		switch ($breadcrumb["type"])
		{
			case "event" : 
				$t = ' : ';
				$t .= '<a href="responses.php?event_id='.$breadcrumb["id"].'&tripid='.$_REQUEST["tripid"].'">';
				$t .= get_event_name($breadcrumb["id"]);
				$t .= "</a>\n";
				print $t;
				break;
				
			case "monitor" :
				$t = ' : ';
				$t .= '<a href="events.php?mon_id='.$breadcrumb["id"].'&tripid='.$_REQUEST["tripid"].'">';
				$t .= get_short_monitor_name($breadcrumb["id"]);
				$t .= "</a>\n";
				print $t;
				break;
				
			case "int_snmp_cache_view" :
				$t = ' : ';
				$t .= '<a href="snmp_cache_view.php?dev_id='.$breadcrumb["id"].'&action=view&type=interface'.'&tripid='.$_REQUEST["tripid"].'">';
				$t .= "interface cache";
				$t .= "</a>\n";
				print $t;
				break;
				
			case "disk_snmp_cache_view" :
				$t = ' : ';
				$t .= '<a href="snmp_cache_view.php?dev_id='.$breadcrumb["id"].'&action=view&type=disk'.'&tripid='.$_REQUEST["tripid"].'">';
				$t .= "disk cache";
				$t .= "</a>\n";
				print $t;
				break;
				
			case "sub_device" :
				$t = ' : ';
				$t .= '<a href="monitors.php?sub_dev_id='.$breadcrumb["id"].'&tripid='.$_REQUEST["tripid"].'">';
				$t .= get_sub_device_name($breadcrumb["id"]);
				$t .= "</a>\n";
				print $t;
				break;
				
			case "device" :
				$t = ' : ';
				$t .= '<a href="sub_devices.php?dev_id='.$breadcrumb["id"].'&tripid='.$_REQUEST["tripid"].'">';
				$t .= get_device_name($breadcrumb["id"]);
				$t .= "</a>\n";
				print $t;
				break;
				
			case "group" :
				$t = ' : ';
				$t .= '<a href="grpdev_list.php?parent_id='.$breadcrumb["id"].'&tripid='.$_REQUEST["tripid"].'">';
				$t .= ($breadcrumb["id"] == 0) ? "All Groups" : get_group_name($breadcrumb["id"]);
				$t .= "</a>\n";
				print $t;
				break;
		} // end switch type
		$count++;
	} // end foreach breadcrumb
?>
	</td></tr>
	</table>
<?php
} // end DrawGroupNavHistory();


/**
* GetPageTitle()
*
* returns the title for a page
*/
function GetPageTitle($prettyname = "")
{
	$pagetitle = "";
	
	if (!empty($prettyname))
	{
		$pagetitle .= "$prettyname - ";
	} // end if prettyname
	
	$pagetitle .= $GLOBALS["netmrg"]["name"];
	
	if (!empty($GLOBALS["netmrg"]["company"]))
	{
		$pagetitle .= " - {$GLOBALS['netmrg']['company']}";
	} // end if company
	
	return $pagetitle;
} // end GetPageTitle();


/**
* GetLoginInfo()
*
* returns the login information for the page header
*/
function GetLoginInfo()
{
	$logintext = "";
	
	if (IsLoggedIn())
	{
		$logintext .= '<span class="loggedintext">Logged&nbsp;in&nbsp;as&nbsp;</span>';
		$logintext .= '<span class="loggedinuser">';
		$logintext .= space_to_nbsp($_SESSION["netmrgsess"]["prettyname"]);
		$logintext .= "</span>\n";
	}
	else
	{
		$logintext .= '<span class="loggedouttext">Not&nbsp;Logged&nbsp;In</span>'."\n";
	} // end if logged in or not
	
	return $logintext;
} // end GetLoginInfo();


/**
* CheckInstallState()
*
* checks things like whether the updater needs run and the 
* prerequisites are met before allowing you to view the rest
* of the webpage
* 
* @param array array of errors returned by prerequisite checks
*/
function CheckInstallState($prereqs_errors = array())
{
	global $PERMIT;
	
	// if we need to run the updater, don't do anything else
	if (IsLoggedIn() && (UpdaterNeedsRun() || count($prereqs_errors)))
	{
		if (UpdaterNeedsRun())
		{
			if (strpos($_SERVER["PHP_SELF"], "updater.php") !== false)
			{
				echo "<!-- updater needs run -->\n";
				if ($_SESSION["netmrgsess"]["permit"] != $PERMIT['Admin'])
				{
?>
<!-- updater needs run and on updater page -->
This installation is currently unusable due to a recent upgrade.  Please contact 
your administrator to have the rest of the upgrade performed. <br />
<a href="logout.php">logout</a><br />
<?php
					end_page();
					exit();
				} // end if not admin
			} // end if we're at the updater
			else
			{
?>
<!-- updater needs run and not on updater page -->
Your installation is currently in an unusable state; please proceed to update 
your installation <a href="updater.php">here</a><br />
<a href="logout.php">logout</a><br />
<?php
				end_page();
				exit();
			} // end if we're not on the updater page
		} // end if updater needs run
		
		else if (count($prereqs_errors))
		{
			echo "<!-- prereqs not met -->\n";
			foreach ($prereqs_errors as $error)
			{
				echo '<span style="color: red;">'.$error.'</span><br />'."\n";
			} // end foreach prereq error
			
			end_page();
			exit();
		} // end if prereqs weren't met
	} // end if logged in and updater needs run or prereqs weren't met
} // end CheckInstallState();


/**
* MakeNavURI($name, $link, $title, $onclick, $alias=array());
*
* makes a navigation uri link and will include #navcurrent id
* if $link or items in $alias are equal to $_SERVER["SCRIPT_URL"]
*/
function MakeNavURI($name, $link, $title = "", $onclick = "", $alias=array())
{
	$currentid = "";
	$a_title = "";
	$a_onclick = "";
	
	array_push($alias, $link);
	if (in_array(basename($_SERVER["SCRIPT_NAME"]), $alias))
	{
		$currentid = ' class="navcurrent"';
	} // end if we're at the current page
	
	if (!empty($title))
	{
		$a_title = ' title="'.htmlentities($title).'"';
	} // end if a title exists
	
	if (!empty($onclick))
	{
		$a_onclick = ' onclick="'.addslashes($onclick).'"';
	} // end if a title exists
	
	return '<a'.$currentid.$a_title.$a_onclick.' href="'.htmlentities($link).'">'.$name.'</a>';
} // end MakeNavURI();


/**
* space_to_nbsp()
*
* converts all spaces in the string to non-breaking spaces
*
* @param string $input string w/ spaces to have replaced
*/
function space_to_nbsp($input)
{
	return str_replace(" ", "&nbsp;", $input);
} // end space_to_nbsp();


/**
* GetClassList($class_array)
*
* returns a string with the class items passed in $class_array()
*/
function GetClassList($class_array = array())
{
	$class_list = "";
	
	if (count($class_array) > 0)
	{
		$class_list = ' class="'.implode(' ', $class_array).'"';
	} // end if class items
	
	return $class_list;
} // end GetClassList();



/*********************************************
*
*        List Display Functions
*
*********************************************/

/**
* make_display_table()
*
* displays the beginnings of a table with a specific title
*
* @param string $title    the title of the table
* @param string $addlink  the link to use to add something if it doesn't 
*                         refer to this page
* @param array $header_element  an array containing 'text' describing the link
*                         and 'href' that the text points at.  this can be an
*                         arbitrary number of array items, and they can be empty
*/

function make_display_table($title, $addlink = "")
{
	$elements = array();
	for ($item_num = 2; $item_num < func_num_args(); $item_num++)
	{
		$item = func_get_arg($item_num);
		array_push($elements, $item);
	}
	make_display_table_array($title, $elements, $addlink);
}

/**
* make_display_table_array()
*
* displays the beginnings of a table with a specific title
*
* @param string $title    the title of the table
* @param array $elements  an array of arrays containing 'text' describing the link
*                         and 'href' that the text points at.  this can be an
*                         arbitrary number of array items, and they can be empty
* @param string $addlink  the link to use to add something if it doesn't 
*                         refer to this page
*/

function make_display_table_array($title, $elements, $addlink = "")
{

?>
	<table style="border-collapse: collapse;" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="editmainheader" colspan="<?php echo (count($elements) + 1); ?>">
			<?php echo "$title\n"; ?>
		</td>
	</tr>
	<tr>
<?php
	foreach ($elements as $curitem)
	{
?>
		<td class="editheader" width="<?php echo (80 / (count($elements) + 1)); ?>%">
			<?php if (!empty($curitem["href"])) { ?>
			<a class="editheaderlink" href="<?php echo $curitem["href"]; ?>">
			<?php } // end if href ?>
			<?php if (!empty($curitem["text"])) { echo $curitem["text"]; } else { echo "&nbsp;"; } ?>
			<?php if (!empty($curitem["href"])) { ?>
			</a>
			<?php } // end if href ?>
		</td>
<?php
	} // end for
?>
		<td class="editheader" width="5%" align="right">
		<a class="customaddlink" href="<?php
		if (!empty($addlink))
		{
			echo $addlink;
		} // end if empty link, refer to self
		else
		{
			echo "{$_SERVER['PHP_SELF']}?action=add";
		} // end else print link
		?>">Add</a>
		</td>
	</tr>
<?php
} // end make_display_table


/**
* make_display_item
*
* makes an item that is displayed in a table
*
* @param string $style   the current style of this row
* @param array $element  an array containing 'text' describing the link
*                        and 'href' that the text points at.  this can be an
*                        arbitrary number of array items, and they can be empty
*                        'checkboxname' : name of checkboxes
*                        'checkboxid' : id to use in checkbox
*                        'checkdisabled' : true if checkbox is disabled
*/

function make_display_item($style = "editfield0")
{
	$elements = array();
	for ($item_num = 1; $item_num < func_num_args(); $item_num++)
	{
		$item = func_get_arg($item_num);
		array_push($elements, $item);
	}
	make_display_item_array($elements, $style);
}

function make_display_item_array($elements, $style = "editfield0")
{
	if (empty($style))
	{
		$style = "editfield0";
	} // end if no style
?>
	<tr>
<?php
	foreach ($elements as $curitem)
	{
?>
		<td class="<?php echo $style ?>" nowrap="nowrap">
<?php
		if (!empty($curitem["checkboxname"]) && !empty($curitem["checkboxid"]))
		{
?>
		<input type="checkbox" name="<?php echo $curitem["checkboxname"]; ?>[<?php echo $curitem["checkboxid"]; ?>]"<?php
			if (isset($curitem["checkdisabled"]) && $curitem["checkdisabled"]) { echo ' disabled="disabled"'; } ?>>
<?php
		} // end if checkbox
		if (!empty($curitem["href"]))
		{
?>
		<a class="<?php echo $style ?>" href="<?php echo $curitem["href"]; ?>">
<?php
		} // end if href

		if (!empty($curitem["text"]))
		{
			echo $curitem["text"];
		} // end if text
		else
		{
			echo "&nbsp;";
		} // end if no text
		if (!empty($curitem["href"]))
		{
?>
		</a>
<?php
		} // end if href
?>
		</td>
<?php
	} // end for
?>
	</tr>
<?php
} // end make_display_item_array()


//   Makes a display table
//	Title: Table's displayed title
//	All others:  format of heading,link,heading,link...
function make_plain_display_table($title)
{
?>

	<table style="border-collapse: collapse;" width="100%" border="0">
	<tr>
		<td class="editmainheader" colspan="<?php print((func_num_args() -1) / 2 + 2); ?>">
		<?php print($title); ?>
		</td>
	</tr>
	<tr>
	<?php

	for ($item_num = 0; $item_num <= ((func_num_args() - 1) / 2 - 1); ++$item_num) {

	?>
		<td class="editheader" width="<?php print(80 / ((func_num_args() -1) / 2 + 2)); ?>%">
			<?php if (func_get_arg($item_num * 2 + 2) != "")
			{
				?><a class="editheaderlink" href="<?php print(func_get_arg($item_num * 2 + 2)); ?>"><?php
			}
			print(func_get_arg($item_num * 2 + 1)); ?>
			<?php if (func_get_arg($item_num * 2 + 2) != "")
			{
				?></a><?php
			}
			?>
		</td>
	<?php

	} // end for

} // end make_display_table

function make_checkbox_command($prefix = "", $columns)
{
	if (empty($columns))
	{
		$columns = "5";
	} // end if no columns
?>
	<tr>
		<td colspan="<?php echo $columns ?>" class="editheader" nowrap="nowrap">
		Checked Items:
<?php
	for ($item_num = 2; $item_num < (func_num_args()); $item_num++)
	{
		$curitem = func_get_arg($item_num);
?>
		&nbsp;&nbsp;
		&lt;<a class="editheaderlink" onclick="document.<?php echo $prefix?>form.action.value='<?php echo $curitem["action"]?>';<?php

		if (!empty($curitem["prompt"]))
		{
?>javascript:if(window.confirm('<?php echo addslashes($curitem["prompt"])?>')){<?php
		} // end if prompt

		// submit form call
?>document.<?php echo $prefix?>form.submit();<?php

		if (!empty($curitem["prompt"]))
		{
?>}<?php
		} // end if prompt

?>" href="#"><?php

		if (!empty($curitem["text"]))
		{
			echo $curitem["text"];
		} // end if text
		else
		{
			echo "&nbsp;";
		} // end if no text

?></a>&gt;
<?php

	} // end for
?>
		</td>
	</tr>
<?php
} // end make_checkbox_command()

// make_status_line
// unit: the singular form of the thing we're counting
// value: the quantity of the thing we're counting
function make_status_line($unit, $value, $units = "")
{
	if ($units == "") $units = $unit . "s";
	if ($value != 1) $unit = $units;
	make_display_item("statusline", array("text" => "$value $unit"));
}

#+++++++++++++++++++++++++++++++++++++++++++++
#
#        Edit Form Creation Functions
#
#+++++++++++++++++++++++++++++++++++++++++++++

function make_table_tag()
{
	?><table style="border-collapse: collapse;" width="100%" border="0" cellspacing="2" cellpadding="2" align="center"><?php
}


/**
* make_edit_table($table, $onsubmit);
*
* makes a form table
*  $title = title of form
*  $onsubmit = optional onSubmit Javascript
*/
function make_edit_table($title, $onsubmit="")
{
	// Makes a table for editing data

	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="editform"<?php if (!empty($onsubmit)) { echo ' onsubmit="'.$onsubmit.'"'; } ?>>

	<?php make_table_tag(); ?>

	<tr>
		<td class="editmainheader">
			<?php print($title); ?>
		</td>
	</tr>
	<?php

} // end make_edit_table


function make_edit_end()
{
	// Finishes an edit table
	?>
	</table>
	</form>
	<?php
} // end make_edit_end


function make_edit_group($title, $options = "")
{
	// Makes a group bar in an edit table
	?>

	<tr <?php print($options); ?>>
	<td class="editheader">
	<?php print($title); ?>
	</td></tr>

	<?php

} // end make_edit_group


function make_edit_label($contents)
{
        // Creates a table row and cell, allowing arbitrary contents to be inserted

        ?>
	<tr><td class="editfield0">
	<?php print ($contents); ?>
	</td></tr>
	<?php
}


// Creates a table row and cell, allowing arbitrary contents to be inserted
function make_edit_section($contents)
{
?>
	<tr><td class="editsection">
	<?php print ($contents); ?>
	</td></tr>
<?php
}


// Creates a form select control
function make_edit_select($header, $name, $select_options = "")
{
?>
	<tr><td class="editfield0">
	<b><?php print($header) ?></b><br />
	<select name="<?php print($name); ?>"<?php echo " $select_options"; ?>>
<?php
} // end make_edit_select


// Creates a form select control
function make_edit_select_end()
{
?>
	</select>
	<br /><br />
	</td></tr>
<?php
} // end make_edit_select_end


/**
* Creates a select control with items named by the table's name field, and id'd by tables id field
*
* $header = select title name
* $name = select box form name
* $table_name = mysql table field
* $selected = row id to be selected (if any)
* $select_options = things like javascript that apply to this select box
* $begin_array_list = array of key=>value pairs to include at the beginning of the option list
* $end_array_list = array of key=>value pairs to include at the end of the option list
* $where = where clause for query
*/
function make_edit_select_from_table($header, $name, $table_name, $selected, $select_options = "", $begin_array_list = array(), $end_array_list = array(), $where = "1")
{
	make_edit_select($header, $name, $select_options);


	// loop through things to put @ the beginning of the select box
	while (list($key, $value) = each($begin_array_list))
	{
		make_edit_select_option($value, $key, ($key == $selected));
	} // end while we have array list
	
	// Draw Select Options from SQL table
	DrawSelectOptionsFromSQL($table_name, $selected, $select_options, $where);
	
	// loop through things to put @ the end of the select box
	while (list($key, $value) = each($end_array_list))
	{
		make_edit_select_option($value, $key, ($key == $selected));
	} // end while we have array list

	make_edit_select_end();
} // end make_edit_select_end


/**
* Creates a select options with items named by the table's name field, and id'd by tables id field
*
* $table_name = mysql table field
* $selected = row id to be selected (if any)
* $select_options = things like javascript that apply to this select box
* $where = where clause for query
*/
function DrawSelectOptionsFromSQL($table_name, $selected, $select_options="", $where="1")
{
	$item_results = db_query("SELECT * FROM $table_name WHERE $where ORDER BY name,id");
	$item_total = db_num_rows($item_results);
	
	for ($item_count = 1; $item_count <= $item_total; ++$item_count)
	{
		$item_row  = db_fetch_array($item_results);
		$item_name = $item_row["name"];
		$item_id   = $item_row["id"];
		$item_selected = ($item_id == $selected);
		
		make_edit_select_option($item_name, $item_id, $item_selected);
	} // end for
} // end DrawSelectOptionsFromSQL();


/**
* Makes edit control from an array
*
* $header	= displayed header of the control
* $name		= name of the control within the form
* $array_list	= array of items to place in the control
*/

function make_edit_select_from_array($header, $name, $array_list, $selected, $select_options = "")
{
	make_edit_select($header, $name, $select_options);

	// loop through things to put @ the end of the select box
	while (list($key, $value) = each($array_list))
	{
		make_edit_select_option($value, $key, ($key == $selected));

	} // end while we have array list

	make_edit_select_end();

}

// Creates a form select control
function make_edit_select_option($name, $value, $selected)
{
	if ($selected)
	{
?>
		<option value="<?php print($value); ?>" SELECTED><?php print($name); ?></option>
<?php
	}
	else
	{
?>
		<option value="<?php print($value); ?>"><?php print($name); ?></option>
<?php
	} // end else
} // end make_edit_select_option


function make_edit_text($header, $name, $size, $maxlength, $value)
{
	// Creates a form text edit control
	?>

	<tr><td class="editfield0">

	<b><?php print($header) ?></b><br />

	<input type="text" name="<?php print($name) ?>" size="<?php print($size) ?>" maxlength="<?php print($maxlength) ?>" value="<?php print(htmlspecialchars($value)); ?>"><br><br>

	</td></tr>

	<?php

} // end make_edit_text

function make_edit_textarea($header, $name, $rows, $columns, $value)
{
	// Creates a form text edit control
	?>

	<tr><td class="editfield0">

	<b><?php print($header) ?></b><br />

	<textarea name="<?php print($name) ?>" rows="<?php print($rows) ?>" cols="<?php print($columns) ?>"><?php print(htmlspecialchars($value)); ?></textarea><br><br>

	</td></tr>

	<?php

} // end make_edit_text

function make_edit_color($header, $name, $value)
{

	?>

	<tr><td class="editfield0">

	<?php print('<b>' . $header . '</b><br />'); ?>

	<input id="<?php print($name); ?>field" type="text" name="<?php print($name) ?>" size="10" maxlength="7" value="<?php print(htmlspecialchars($value)); ?>">

        <input type="button" name="<?php print($name . 'cbtn'); ?>" value="Choose" onClick="colorDialog('<?php print($name); ?>')">
	<br /><br />

	</td></tr>

	<?php

} // end make_edit_color


function make_edit_password($header, $name, $size, $maxlength, $value, $options = "")
{
	// Creates a form text edit control
	?>

	<tr <?php print($options); ?>>
	<td class="editfield0">

	<b><?php print($header) ?></b><br>

	<input type="password" name="<?php print($name) ?>" size="<?php print($size) ?>" maxlength="<?php print($maxlength) ?>" value="<?php print(htmlspecialchars($value)); ?>"><br><br>

	</td></tr>

	<?php

} // end make_edit_password


function make_edit_hidden($name, $value)
{
	// Creates a form hidden text control
	?>

	<input type="hidden" name="<?php print($name) ?>" value="<?php print(htmlspecialchars($value)); ?>">

	<?php

} // end make_edit_text


function make_edit_submit_button()
{
	// Creates a form submit button
	?>

	<tr><td class="editfield0" align="right">

	<input type="submit" name="Submit" value="Save Changes" OnClick="document.editform.Submit.disabled = true; document.editform.Cancel.disabled = true; document.editform.submit();">
	<input type="button" name="Cancel" value="Cancel Changes" OnClick="document.editform.Submit.disabled = true; document.editform.Cancel.disabled = true; history.back(1);">

	</td></tr>

	<?php

} // end make_edit_submit_button

function make_edit_checkbox($header, $name, $checked=false)
{
	// Creates a form checkbox
	?><tr><td class="editfield0"><?php

	if ($checked) {
	?>
	<input type="checkbox" name="<?php print($name); ?>" value="1" checked><?php print($header);
	} else {
	?>
	<input type="checkbox" name="<?php print($name); ?>" value="1"><?php print($header);
	}

	?></td></tr><?php

} // end make_edit_checkbox

function make_edit_select_monitor($mon_id_cur, $prepended_array = array())
{
?>
	<tr><td class="editfield0">
	<b>Monitor:</b><br />
	<select name="device" id="device" onchange="xajax_redraw_subdevice('device', document.getElementById('device').options[document.getElementById('device').selectedIndex].value);">
<?php
	if ($mon_id_cur > 0)
	{

		$q = db_query("SELECT devices.id AS devid, sub_devices.id AS subdevid FROM monitors LEFT JOIN sub_devices ON monitors.sub_dev_id=sub_devices.id LEFT JOIN devices ON sub_devices.dev_id=devices.id WHERE monitors.id = $mon_id_cur");
		$info = db_fetch_array($q);
	}
	else
	{
		$info['devid'] = -1;
	}

	$q = db_query("SELECT id, name FROM devices ORDER BY name");

	// "Special Monitors"
	make_edit_select_option("-Internal-", "-1", true);

	while ($r = db_fetch_array($q))
	{
		echo("<option value='{$r['id']}'>{$r['name']}</option>");
	}
?>
	</select>
	<select name="subdevice" id="subdevice" onchange="xajax_redraw_monitor('subdevice', document.getElementById('subdevice').options[document.getElementById('subdevice').selectedIndex].value);">
	</select>
	<select name="mon_id" id="monitor">
	</select>
	<script type="text/javascript">
	pickSelect("device", <?php echo($info['devid']);?>);
	xajax_redraw_monitor('monitor', <?php echo($mon_id_cur);?>);
	</script>
<?php
}

function make_edit_select_monitor_old($mon_id_cur, $prepended_array = array())
{
	// Creates an edit select box for the selection of "monitors"

	make_edit_select("Monitor:", "mon_id");

	// loop through things to put @ the end of the select box
	while (list($key, $value) = each($prepended_array))
	{
		make_edit_select_option($value, $key, ($key == $mon_id_cur));

	} // end while we have array list


	$mon_results = db_query("
	SELECT	monitors.id			AS id,
			devices.name		AS dev_name,
			sub_devices.name	AS sub_name

	FROM	monitors

	LEFT JOIN sub_devices ON monitors.sub_dev_id=sub_devices.id
	LEFT JOIN devices ON sub_devices.dev_id=devices.id
        ");

	$mons = array();
	while ($arow = mysql_fetch_array($mon_results))
	{
		$arow['mon_name'] = get_monitor_name($arow['id']);
		array_push($mons, $arow);
	}

	function mon_sort($a, $b)
	{
		if ($a['dev_name'] == $b['dev_name'])
		{
			if ($a['sub_name'] == $b['sub_name'])
			{
				return strcmp($a['mon_name'], $b['mon_name']);
			}
			else
			{
				return strcmp($a['sub_name'], $b['sub_name']);
			}
		}
		else
		{
			return strcmp($a['dev_name'], $b['dev_name']);
		}
	}

	usort($mons, mon_sort);

	foreach ($mons as $mon_row)
	{
		$mon_id = $mon_row["id"];
	        make_edit_select_option($mon_row['mon_name'], $mon_id, $mon_id_cur == $mon_id);
	}

	make_edit_select_end();


} // end make_edit_select_monitor

function make_edit_select_subdevice($subdev_id_cur, $prepended_array = array(), $select_options = "")
{
	// Creates an edit select box for the selection of "subdevices"

	make_edit_select("Subdevice:", "subdev_id[]", $select_options);

	// loop through things to put @ the end of the select box
	while (list($key, $value) = each($prepended_array))
	{
		make_edit_select_option($value, $key, ($key == $subdev_id_cur));

	} // end while we have array list


	$subdev_results = db_query("
	SELECT	devices.name AS dev_name,
			sub_devices.name AS sub_name,
			sub_devices.id AS id

	FROM	sub_devices

	LEFT JOIN devices ON sub_devices.dev_id=devices.id

	ORDER BY dev_name, sub_name, id

        ");

	$subdev_total = db_num_rows($subdev_results);

	for ($subdev_count = 1; $subdev_count <= $subdev_total; ++$subdev_count)
	{

		$subdev_row = db_fetch_array($subdev_results);
		$subdev_id = $subdev_row["id"];
		$subdev_name = get_dev_sub_device_name($subdev_id);
	        make_edit_select_option($subdev_name, $subdev_id, $subdev_id_cur == $subdev_id);
	}

	make_edit_select_end();


} // end make_edit_select_subdevice

/**
* make_edit_select_test()
*
* draws forms for the selection of a test type, test, and parameters for the test
*
*/

function make_edit_select_test($test_type, $test_id, $test_params)
{
	echo
	"
		<script type='text/javascript'>
		
		function redisplay(selectedIndex)
		{
			window.location = '{$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&sub_dev_id={$_REQUEST['sub_dev_id']}&dev_type={$_REQUEST['dev_type']}&tripid={$_REQUEST['tripid']}&action={$_REQUEST['action']}" . $dev_thingy . "&type=' + selectedIndex;
		}

		</script>
	";

	// if we've been passed a test type
	if (!empty($_REQUEST["type"]))
	{
		$test_type = $_REQUEST["type"];
	} // end if test type is set
	// else default to test type 1 (script)
	else if (empty($test_type))
	{
		$test_type = 1;
	} // end if no test type
	GLOBAL $TEST_TYPES;
	make_edit_select_from_array("Monitoring Type:", "test_type", $TEST_TYPES, $test_type, "onChange='redisplay(form.test_type.options[selectedIndex].value);'");
	
	if ($test_type == 1)
	{
		make_edit_group("Script Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("Script Test:", "test_id", "tests_script", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("Script Test:", "test_id", "tests_script", $test_id);
		} // end if editing
	}
	
	if ($test_type == 2)
	{
		make_edit_group("SNMP Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("SNMP Test:", "test_id", "tests_snmp", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("SNMP Test:", "test_id", "tests_snmp", $test_id);
		} // end if editing
	}
	
	if ($test_type == 3)
	{
		make_edit_group("SQL Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("SQL Test:", "test_id", "tests_sql", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("SQL Test:", "test_id", "tests_sql", $test_id);
		} // end if editing
	}
	
	if ($test_type == 4)
	{
		make_edit_group("Internal Test Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("Internal Test:", "test_id", "tests_internal", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("Internal Test:", "test_id", "tests_internal", $test_id);
		} // end if editing
	}
	
	make_edit_text("Parameters:", "test_params", 50, 100, htmlspecialchars($test_params));

} // end make_test_selection

// Special Functions

function color_block($color)
{
	return "<b class='colorbox' style='border:thin solid black;background-color:$color'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>";
}

function cond_formatted_link($enabled, $text, $link = "", $caption = "", $img = "")
{
	if ($enabled)
	{
		return formatted_link($text, $link, $caption, $img);
	}
	else
	{
		return formatted_link_disabled($text, $img);
	}
}

function formatted_link($text, $link, $caption = "", $img = "")
{
	$text = space_to_nbsp($text);
	$returnstr = "";

	if (!empty($img))
	{
		$titletext = $text;
		$titletext .= empty($caption) ? "" : " - $caption";

		$returnstr = '<a href="'.$link.'">';
		$returnstr .= '<img src="'.get_image_by_name($img).'" border="0" title="'.$titletext.'"alt="'.$titletext.'" />';
		$returnstr .= '</a>';
	} # end if image
	else
	{
		$returnstr = '&lt;<a href="'.$link.'"';
		if (!empty($caption))
		{
			$returnstr .= ' title="'.$caption.'"';
		} # end if caption
		$returnstr .= '>'.$text.'</a>&gt;';
	} # no image

	return $returnstr;
} // end formatted_link

function formatted_link_disabled($text, $img = "")
{
	$returnstr = "";
	if (!empty($img))
	{
		$returnstr = '<img src="'.get_image_by_name($img).'" border="0" alt="'.$text.'" />';
	} # end if image
	else
	{
		$returnstr = "&lt;$text&gt;";
	} # end if no image

	return $returnstr;
}  // end formatted_link_disabled

function image_link($img_name, $title, $link)
{
	$returnval = '<a href="' . $link . '">';
	$returnval .= '<img src="'.get_image_by_name("$img_name").'" width="15" height="15" border="0" alt="' . $title . '" title="' . $title . '" />'."</a>\n";
	return $returnval;
}

function image_link_disabled($img_name, $title)
{
	return '<img src="'.get_image_by_name($img_name . "-disabled").'" width="15" height="15" border="0" alt="' . $title . '" title="' . $title . '" />'."\n";
}


/*
	JavaScript Code Blocks
*/

function js_confirm_dialog($function_name, $before = "", $after = "", $url_base = "")
{

?>
	<script type="text/javascript">
		function <?php echo($function_name); ?>(prompt, url)
		{
			if (window.confirm('<?php print($before); ?>' + prompt + '<?php print($after); ?>'))
			{
				window.location = '<?php print($url_base); ?>' + url;
			}
		}
	</script>
<?php

}

function js_checkbox_utils($prefix="")
{

?>
	<script type="text/javascript">
		function <?php echo $prefix; ?>checkbox_utils(method)
		{
			for (i = 0; i < document.<?php echo $prefix; ?>form.elements.length; i++)
			{
				if ( (document.<?php echo $prefix; ?>form.elements[i].type == 'checkbox') && (document.<?php echo $prefix; ?>form.elements[i].disabled == false) )
				{
					if (method == 0)
					{
						document.<?php echo $prefix; ?>form.elements[i].checked = false;
					}
					else if (method == 1)
					{
						document.<?php echo $prefix; ?>form.elements[i].checked = true;
					}
					else
					{
						document.<?php echo $prefix; ?>form.elements[i].checked =
							! document.<?php echo $prefix; ?>form.elements[i].checked;
					}
				}
			}
		}
	</script>
<?php

}

function checkbox_toolbar($prefix="")
{
	return
		'<a href="#" class="editheaderlink" title="all" onClick="'.$prefix.'checkbox_utils(1);">[*]</a> ' .
		'<a href="#" class="editheaderlink" title="none" onClick="'.$prefix.'checkbox_utils(0);">[0]</a> ' .
		'<a href="#" class="editheaderlink" title="invert" onClick="'.$prefix.'checkbox_utils(-1);">[-]</a>';
}

function js_color_dialog()
{
	?>
	<script language="javascript">
	function colorDialog(fieldName)
	{
                newwin=window.open('color_dialog.php?field=' + fieldName, 'ColorChooser','height=50,width=305,scrollbars=no,resizable=no,dependent');
	}
        </script>
	<?php
} // end js_color_dialog();


/**
* DisplayErrors($errors)
*
* display array of errors
*/
function DisplayErrors($errors)
{
	if (count($errors) > 0)
	{
		echo "<div>The following errors were encountered:</div>";
		// foreach error
		foreach ($errors as $error)
		{
			echo '<div class="error-text">'."\n";
			echo $error;
			echo "</div>\n";
		} // end foreach error
	} // end if we have errors
} // end DisplayErrors();


/**
* DisplayResults($Results)
*
* display array of results
*/
function DisplayResults($results)
{
	if (count($results) > 0)
	{
		echo "<div>The following results occured:</div>";
		// foreach error
		foreach ($results as $result)
		{
			echo '<div class="result-text">'."\n";
			echo $result;
			echo "</div>\n";
		} // end foreach result
	} // end if we have results
} // end DisplayResults();


?>
