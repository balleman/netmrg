<?php
/**
* format.php
*
* Functions used to format various parts of NetMRG
* see doc/LICENSE for copyright information
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
* begin_page()
*
* starts the page w/ basic HTML
*
* @param string $pagename    page that this is
* @param string $prettyname  shown in the title bar
* @param boolean $refresh    whether to refresh this page or not
* @param string $bodytags    options for the <body> tag
*/
function begin_page($pagename = "", $prettyname = "", $refresh = 0, $bodytags = "")
{
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     SYSTEM "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php
	if (!empty($prettyname))
	{
		echo "$prettyname - ";
	} // end if prettyname
	echo $GLOBALS["netmrg"]["name"];
	if (!empty($GLOBALS["netmrg"]["company"]))
	{
		echo " - {$GLOBALS['netmrg']['company']}";
	} // end if company
	?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/include/netmrg.css">
<?php if ($refresh) { ?>
	<meta http-equiv="refresh" content="300">
<?php } ?>
</head>
<body <?php echo($bodytags); ?>>
<?php
if (!empty($pagename))
{
?>
<!-- <?php echo $pagename; ?> -->
<?php
} // end if there's a pagename, output it
?>
<table class="titletable" cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td class="logo" rowspan="2" width="35">
		<img src="<?php echo get_image_by_name("logo"); ?>" alt="logo" border="0">
	</td>
	<td class="title_name" rowspan="2" width="100%">
		<a href="<?php echo $GLOBALS["netmrg"]["webhost"].$GLOBALS["netmrg"]["webroot"]; ?>" class="title_name">
		<?php echo $GLOBALS["netmrg"]["name"]; ?>
		</a>
	</td>
	<td class="company" align="right" valign="top">
		<a class="company" href="<?php echo $GLOBALS["netmrg"]["companylink"]; ?>">
		<?php echo(space_to_nbsp($GLOBALS["netmrg"]["company"])); ?>
		</a>
	</td>
</tr>
<tr>
	<td class="logindata" align="left" valign="bottom">
	<?php
		if (IsLoggedIn())
		{
			echo '<span class="loggedintext">Logged&nbsp;in&nbsp;as&nbsp;</span>';
			echo '<span class="loggedinuser">';
			echo(space_to_nbsp($_SESSION["netmrgsess"]["username"]));
			echo "</span>\n";
		}
		else
		{
			echo '<span class="loggedouttext">Not&nbsp;Logged&nbsp;In</span>'."\n";
		} // end if logged in or not
	?>
	</td>
</tr>
</table>
<?php // needs some kind of spacing for IE.  A <br> is too much.
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td class="empty" valign="top"><img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="4" height="1" alt="trans gif"></td>
	<td valign="top">
	<?php
		if (IsLoggedIn() && !UpdaterNeedsRun())
		{
			display_menu();
		} // end if is logged in, show the menu
	?>
	<img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="125" height="1" alt="trans gif">
	</td>
	<td class="empty" valign="top"><img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="4" height="1" alt="trans gif"></td>
	<td valign="top" width="100%">

<?php
	// if we need to run the updater, don't do anything else
	if (IsLoggedIn() && UpdaterNeedsRun() && strpos($_SERVER["PHP_SELF"], "updater.php") === false)
	{
		if ($_SESSION["netmrgsess"]["permit"] != 3)
		{
?>
This installation is currently unusable due to a recent upgrade.  Please contact 
your administrator to have the rest of the upgrade performed. <br />
<a href="logout.php">logout</a><br />
<?php		} // end if not admin
		else
		{
?>
Your installation is currently in an unusable state; please proceed to update 
your installation <a href="updater.php">here</a><br />
<a href="logout.php">logout</a><br />
<?php
		} // end if admin
		
		end_page();
		exit();
	} // end if updater needs run
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
	</td>
</tr>
</table>

</body>
</html>
<?php
} // end end_page()


/**
* DrawGroupNavHistory($type, $id)
*
* draws a nav bar along the tops of the pages under 'Groups'
* and keeps a history of where you've been
*
* $type = (group, device, sub_device, monitor, event)
* $id = <id of type you are in>
*/
function DrawGroupNavHistory($type, $id)
{
	// setup array to hold group nav
	if (!isset($_SESSION["netmrgsess"]["grpnav"]) || !is_array($_SESSION["netmrgsess"]["grpnav"]))
	{
		$_SESSION["netmrgsess"]["grpnav"] = array();
		$_SESSION["netmrgsess"]["grpnav"]["group"] = "";
		$_SESSION["netmrgsess"]["grpnav"]["device"] = "";
		$_SESSION["netmrgsess"]["grpnav"]["sub_device"] = "";
		$_SESSION["netmrgsess"]["grpnav"]["monitor"] = "";
		$_SESSION["netmrgsess"]["grpnav"]["event"] = "";
	} // end if we haven't done this yet
	
	// assign the id to the type
	$_SESSION["netmrgsess"]["grpnav"][$type] = $id;
	
	// for each type, add the history to an array
	$hist = array();
	switch ($type)
	{
		case "event" : 
			$t = ' : ';
			if ($type != "event") $t .= '<a href="responses.php?event_id='.$_SESSION["netmrgsess"]["grpnav"]["event"].'">';
			$t .= get_event_name($_SESSION["netmrgsess"]["grpnav"]["event"]);
			if ($type != "event") $t .= "</a>\n";
			array_push($hist, $t);

		case "monitor" :
			$t = ' : ';
			if ($type != "monitor") $t .= '<a href="events.php?mon_id='.$_SESSION["netmrgsess"]["grpnav"]["monitor"].'">';
			$t .= get_short_monitor_name($_SESSION["netmrgsess"]["grpnav"]["monitor"]);
			if ($type != "monitor") $t .= "</a>\n";
			array_push($hist, $t);

		case "sub_device" :
			$t = ' : ';
			if ($type != "sub_device") $t .= '<a href="monitors.php?sub_dev_id='.$_SESSION["netmrgsess"]["grpnav"]["sub_device"].'">';
			$t .= get_sub_device_name($_SESSION["netmrgsess"]["grpnav"]["sub_device"]);
			if ($type != "sub_device") $t .= "</a>\n";
			array_push($hist, $t);

		case "device" :
			$t = ' : ';
			if ($type != "device") $t .= '<a href="sub_devices.php?dev_id='.$_SESSION["netmrgsess"]["grpnav"]["device"].'">';
			$t .= get_device_name($_SESSION["netmrgsess"]["grpnav"]["device"]);
			if ($type != "device") $t .= "</a>\n";
			array_push($hist, $t);

		case "group" :
			$t = ' : ';
			if ($type != "group") $t .= '<a href="devices.php?grp_id='.$_SESSION["netmrgsess"]["grpnav"]["group"].'">';
			$t .= get_group_name($_SESSION["netmrgsess"]["grpnav"]["group"]);
			if ($type != "group") $t .= "</a>\n";
			array_push($hist, $t);
	} // end switch type
	
?>
	<table style="border-collapse: collapse;" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr><td class="editmainheader">
		History : 
        <a href="groups.php">All Groups</a>
<?php
	// loop through the history backwards
	for($i = count($hist)-1; $i >= 0; $i--)
	{
		echo $hist[$i];
	} // end for history
?>
	</td></tr>
	</table>
<?php
} // end DrawGroupNavHistory();



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

?>
	<table style="border-collapse: collapse;" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="editmainheader" colspan="<?php echo ((func_num_args() - 2) + 1); ?>">
			<?php echo "$title\n"; ?>
		</td>
	</tr>
	<tr>
<?php
	for ($item_num = 2; $item_num < func_num_args(); $item_num++)
	{
		$curitem = func_get_arg($item_num);
?>
		<td class="editheader" width="<?php echo (80 / (func_num_args() - 2)); ?>%">
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
*/
function make_display_item($style = "editfield0")
{
	if (empty($style))
	{
		$style = "editfield0";
	} // end if no style
?>
	<tr>
<?php
	for ($item_num = 1; $item_num < (func_num_args()); $item_num++)
	{
		$curitem = func_get_arg($item_num);
?>
		<td class="<?php echo $style ?>" nowrap="nowrap">
<?php
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
} // end make_display_item()


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
			<a class="editheaderlink" href="<?php print(func_get_arg($item_num * 2 + 2)); ?>">
			<?php print(func_get_arg($item_num * 2 + 1)); ?>
			</a>
		</td>
	<?php

	} // end for

} // end make_display_table



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
	<tr><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">
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

	// loop through things to put @ the end of the select box
	while (list($key, $value) = each($end_array_list))
	{
		make_edit_select_option($value, $key, ($key == $selected));
	} // end while we have array list

	make_edit_select_end();
} // end make_edit_select_end

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

	<input type="text" name="<?php print($name) ?>" size="<?php print($size) ?>" maxlength="<?php print($maxlength) ?>" value="<?php print($value); ?>"><br><br>

	</td></tr>

	<?php

} // end make_edit_text

function make_edit_color($header, $name, $value)
{

        ?>

	<tr><td class="editfield0">

	<?php print('<b>' . $header . '</b><br />'); ?>

	<input id="<?php print($name); ?>field" type="text" name="<?php print($name) ?>" size="10" maxlength="7" value="<?php print($value); ?>">

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

        <input type="password" name="<?php print($name) ?>" size="<?php print($size) ?>" maxlength="<?php print($maxlength) ?>" value="<?php print($value); ?>"><br><br>

        </td></tr>

        <?php

} // end make_edit_password


function make_edit_hidden($name, $value)
{
	// Creates a form hidden text control
	?>

	<input type="hidden" name="<?php print($name) ?>" value="<?php print($value); ?>">

	<?php

} // end make_edit_text


function make_edit_submit_button()
{
	// Creates a form submit button
	?>

	<tr><td class="editfield0" align="right">

	<input type="submit" name="Submit" value="Save Changes">
        <input type="button" name="Cancel" value="Cancel Changes" OnClick="history.back(1);">

	</td></tr>

	<?php

} // end make_edit_submit_button

function make_edit_checkbox($header, $name, $checked)
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

	ORDER BY dev_name, sub_name, id

        ");

        $mon_total = db_num_rows($mon_results);

	for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count)
	{

		$mon_row = db_fetch_array($mon_results);
		$mon_id = $mon_row["id"];
		$mon_name = get_monitor_name($mon_id);
	        make_edit_select_option($mon_name, $mon_id, $mon_id_cur == $mon_id);
	}

	make_edit_select_end();


} // end make_edit_select_monitor

function make_edit_select_subdevice($subdev_id_cur, $prepended_array = array())
{
	// Creates an edit select box for the selection of "subdevices"

	make_edit_select("Subdevice:", "subdev_id");

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

// Special Functions

function color_block($color)
{
	return "<b class='colorbox' style='border:thin solid black;background-color:$color'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>";
}

function cond_formatted_link($enabled, $text, $link = "", $caption = "")
{
	if ($enabled)
	{
		return formatted_link($text, $link, $caption);
	}
	else
	{
		return formatted_link_disabled($text);
	}
}

function formatted_link($text, $link, $caption = "")
{

	$text = space_to_nbsp($text);

	if ($caption != "")
	{
		return "&lt;<a href=\"$link\" title=\"$caption\">$text</a>&gt;";
	}
	else
	{
		return "&lt;<a href=\"$link\">$text</a>&gt;";
	}

} // end formatted_link

function formatted_link_disabled($text)
{
	return "&lt;$text&gt;";  

}  // end formatted_link_disabled

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
