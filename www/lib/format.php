<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Format Module                         #
#           format.php                                 #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################


#+++++++++++++++++++++++++++++++++++++++++++++
#
#        Site-Wide Display Functions
#
#+++++++++++++++++++++++++++++++++++++++++++++


function begin_page($pagename = "", $prettyname = "")
{
	// Define the initial formating for the page
	global $menu_id, $menu_stat, $user_name;

	if (isset($menu_id))
	{
        	// If there is a change to the menu, apply it
        	change_menu_status($menu_id, $menu_stat);
	} // end if

	?>
	<html>
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
	</head>
	<body>
<?php
if (!empty($pagename)) {
?>
	<!-- <?php echo $pagename; ?> -->
<?php
} // end if there's a pagename, output it
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td class="title_name" valign="top" rowspan="2">
		<a href="<?php echo $GLOBALS["netmrg"]["webhost"].$GLOBALS["netmrg"]["webroot"]; ?>" class="title_name">
		<?php echo $GLOBALS["netmrg"]["name"]; ?>
		</a>
	</td>
	<td class="company" align="right" valign="top">
		<a href="<?php echo $GLOBALS["netmrg"]["companylink"]; ?>" class="company">
		<?php echo $GLOBALS["netmrg"]["company"]; ?>
		</a>
	</td>
</tr>
<tr>
	<td class="logindata" align="right" valign="bottom">
	<?php
		if (IsLoggedIn())
		{
			echo '<span class="loggedintext">Logged in as </span>';
			echo '<span class="loggedinuser">';
			echo $_SESSION["netmrgsess"]["username"];
			echo "</span>\n";
		}
		else
		{
			echo '<span class="loggedouttext">Not Logged In</span>'."\n";
		} // end if logged in or not
	?>
	</td>
</tr>
</table>
<br>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td class="empty" valign="top"><img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="4" height="1" alt="trans gif"></td>
	<td valign="top">
	<?php
		if (IsLoggedIn())
		{
			display_menu();
		} // end if is logged in, show the menu
	?>
	<img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="125" height="1" alt="trans gif">
	</td>
	<td class="empty" valign="top"><img src="<?php echo $GLOBALS["netmrg"]["webroot"]; ?>/img/trans.gif" width="4" height="1" alt="trans gif"></td>
	<td valign="top" width="100%">

<?php
} // end begin_page()


// Define the final formatting for the page
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




#+++++++++++++++++++++++++++++++++++++++++++++
#
#        List Display Functions
#
#+++++++++++++++++++++++++++++++++++++++++++++

function make_display_table($title)
{
	// Makes a display table
	// Title: Table's displayed title
	// All others:  format of heading,link,heading,link...

	global $custom_add_link, $uplink;
?>

	<table width="100%" border="0" cellspacing="2" cellpadding="2" align="center">
	<tr>
		<td colspan="<?php print((func_num_args() -1) / 2 + 2); ?>" bgcolor="<?php print(get_color_by_name("edit_main_header")); ?>">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<tr><td>

				<font color="<?php print(get_color_by_name("edit_main_header_text")); ?>">
				<big><strong><?php print($title); ?></strong></big>
				</font>

				</td><td align="right">

				<font color="<?php print(get_color_by_name("edit_main_header_text")); ?>">
                                <big><strong><?php print($uplink); ?></strong></big>
                                </font>

				</td></tr>
			</table>
		</td>

	</tr>
	<tr bgcolor="<?php print(get_color_by_name("edit_header")); ?>">

<?php
	for ($item_num = 0; $item_num <= ((func_num_args() - 1) / 2 - 1); ++$item_num) {
?>
		<td width="<?php print(80 / ((func_num_args() -1) / 2 + 2)); ?>%">
			<a href="<?php print(func_get_arg($item_num * 2 + 2)); ?>">
			<font color="<?php print(get_color_by_name("edit_header_text")); ?>">
			<strong>
			<?php
				$text = func_get_arg($item_num * 2 + 1);
				if ($text != "") { print($text); } else { print("&nbsp;"); }
			?>
			</strong>
			</font>
			</a>
		</td>
<?php
	} // end for
?>
		<td width="5%" align="right">
		<a href="<?php
		if (!isset($custom_add_link)) {
			echo "{$_SERVER['PHP_SELF']}?action=add";
		} else {
			echo $custom_add_link;
		}
		?>">
		<font color="#FFFF00"><b>Add</b></font></a>&nbsp;
		</td>
	</tr>


	<?php

} // end make_display_table

function make_display_item()
{
	// Makes an item for a displayed table
	?>

	<tr bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">

	<?php

	for ($item_num = 0; $item_num <= ((func_num_args() / 2) - 1); ++$item_num) {

		if (func_get_arg($item_num * 2 + 1) != "") {
			// We have a link
			?>
				<td><a href="<?php print(func_get_arg($item_num * 2 + 1));?>"><?php print(func_get_arg($item_num * 2));?></a></td>
			<?php
		} else {
			// We don't have a link
			?>
				<td>
					<?php
					$text = func_get_arg($item_num * 2);
					if ($text != "") { print($text); } else { print("&nbsp;"); }
					?></td>
			<?php
		} //end if
	} // end for

	?> </tr> <?php

} // end make_display_item


//   Makes a display table
//	Title: Table's displayed title
//	All others:  format of heading,link,heading,link...
function make_plain_display_table($title)
{
?>

	<table width="100%" border="0" cellspacing="2" cellpadding="2" align="center">
	<tr>
		<td colspan="<?php print((func_num_args() -1) / 2 + 2); ?>" bgcolor="<?php print(get_color_by_name("edit_main_header")); ?>">
		<font color="<?php print(get_color_by_name("edit_main_header_text")); ?>">
		<b><?php print($title); ?></b>
		</font>
		</td>
	</tr>
	<tr bgcolor="<?php print(get_color_by_name("edit_header")); ?>">

	<?php

	for ($item_num = 0; $item_num <= ((func_num_args() - 1) / 2 - 1); ++$item_num) {

	?>
		<td width="<?php print(80 / ((func_num_args() -1) / 2 + 2)); ?>%">
			<b><a href="<?php print(func_get_arg($item_num * 2 + 2)); ?>">
			<font color="<?php print(get_color_by_name("edit_header_text")); ?>">
			<?php print(func_get_arg($item_num * 2 + 1)); ?>
			</font>
			</a></b>
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
	?><table width="100%" border="0" cellspacing="2" cellpadding="2" align="center"><?php
}


function make_edit_table($title)
{
	// Makes a table for editing data
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="editform">

	<?php make_table_tag(); ?>

	<tr>
		<td bgcolor="<?php print(get_color_by_name("edit_main_header")); ?>">
			<font color="<?php print(get_color_by_name("edit_main_header_text")); ?>">
			<big><strong><?php print($title); ?></strong></big>
			</font>
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

	<tr bgcolor="<?php print(get_color_by_name("edit_header")); ?>" <?php print($options); ?>><td>
	<font color="<?php print(get_color_by_name("edit_header_text")); ?>">
	<strong><?php print($title); ?></strong></font></td></tr>

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


function make_edit_select($header, $name, $options = "", $select_options = "")
{
	// Creates a form select control
	?>

	<tr <?php print($options); ?>><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">

	<b><?php print($header) ?></b><br>

	<select name="<?php print($name) ?>" <?php print($select_options); ?>>

	<?php
} // end make_edit_select


function make_edit_select_end()
{
	// Creates a form select control
	?>

	</select><br><br></td></tr>

	<?php
} // end make_edit_select_end


function make_edit_select_from_table($header, $name, $table_name, $selected, $options = "", $select_options = "")
{
	// Creates a select control with items named by the table's name field, and id'd by tables id field

	make_edit_select($header, $name, $options, $select_options);

	$item_results = do_query("SELECT * FROM $table_name ORDER BY name,id");
	$item_total = mysql_num_rows($item_results);

	for ($item_count = 1; $item_count <= $item_total; ++$item_count) {

	$item_row  = mysql_fetch_array($item_results);
	$item_name = $item_row["name"];
	$item_id   = $item_row["id"];
	$item_selected = ($item_id == $selected);

	make_edit_select_option($item_name, $item_id, $item_selected);

	} // end for

	make_edit_select_end();

} // end make_edit_select_end


function make_edit_select_option($name, $value, $selected)
{
	// Creates a form select control

	if ($selected)
	{

        	?>

        	<option value="<?php print($value) ?>" SELECTED><?php print($name); ?></option>

        	<?php

	}
	else
	{

        	?>

        	<option value="<?php print($value) ?>"><?php print($name); ?></option>

        	<?php

	} // end else

} // end make_edit_select_option

function make_edit_text($header, $name, $size, $maxlength, $value, $options = "")
{
	// Creates a form text edit control
	?>

	<tr <?php print($options); ?>><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">

	<b><?php print($header) ?></b><br>

	<input type="text" name="<?php print($name) ?>" size="<?php print($size) ?>" maxlength="<?php print($maxlength) ?>" value="<?php print($value); ?>"><br><br>

	</td></tr>

	<?php

} // end make_edit_text

function make_edit_color($header, $name, $value)
{

        ?>

	<tr><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">

	<b><?php print($header) ?></b><br>

        <input id="<?php print($name); ?>field" type="text" name="<?php print($name) ?>" size="10" maxlength="7" value="<?php print($value); ?>">

        <input type="button" name="<?php print($name . 'cbtn'); ?>" value="Choose" onClick="colorDialog('<?php print($name); ?>')">
	<br><br>

	</td></tr>

	<?php

} // end make_edit_color


function make_edit_password($header, $name, $size, $maxlength, $value, $options = "")
{
        // Creates a form text edit control
        ?>

        <tr <?php print($options); ?>><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>">

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

	<tr><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>" align="right">

	<input type="submit" name="Submit" value="Save Changes">
        <input type="button" name="Cancel" value="Cancel Changes" OnClick="history.back(1);">

	</td></tr>

	<?php

} // end make_edit_submit_button

function make_edit_checkbox($header, $name, $checked, $options = "")
{
	// Creates a form checkbox
	?><tr <?php print($options); ?>><td bgcolor="<?php print(get_color_by_name("edit_fields")); ?>"><?php

	if ($checked) {
	?>
	<input type="checkbox" name="<?php print($name); ?>" value="1" checked><?php print($header);
	} else {
	?>
	<input type="checkbox" name="<?php print($name); ?>" value="1"><?php print($header);
	}

	?></td></tr><?php

} // end make_edit_checkbox

function make_edit_select_monitor($mon_id_cur)
{
	// Creates an edit select box for the selection of "monitors"

	make_edit_select("Monitor:","mon_id");

	$mon_results = do_query("
	SELECT  monitors.id AS id,
                mon_devices.name AS dev_name,
                sub_devices.name AS sub_name

	FROM monitors

        LEFT JOIN sub_devices ON monitors.sub_dev_id=sub_devices.id
        LEFT JOIN mon_devices ON sub_devices.dev_id=mon_devices.id

        ORDER BY dev_name, sub_name, id

        ");

        $mon_total = mysql_num_rows($mon_results);

	for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count)
	{

		$mon_row = mysql_fetch_array($mon_results);
		$mon_id = $mon_row["id"];
		$mon_name = get_monitor_name($mon_id);

		if ($mon_id_cur != $mon_id)
		{
		        make_edit_select_option($mon_name,$mon_id,0);
		} 
		else
		{
		        make_edit_select_option($mon_name,$mon_id,1);
		}

	} // end for

	make_edit_select_end();


} // end make_edit_select_monitor


// Special Functions

function formatted_link($text, $link, $caption = "")
{

	$text = str_replace(" ", "&nbsp;", $text);

	if ($caption != "")
	{
		return "&lt;<a href=\"$link\" title=\"$caption\">$text</a>&gt;";
	}
	else
	{
		return "&lt;<a href=\"$link\">$text</a>&gt;";
	}

} // end formatted_link

function refresh_tag()
{
        echo("<META HTTP-EQUIV=\"refresh\" CONTENT=\"300\">");
}

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
}
