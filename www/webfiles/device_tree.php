<?php

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Device Tree                                #
#           device_tree.php                            #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@netmrg.net http://netmrg.net               #
#                                                      #
########################################################

require_once("../include/config.php");

// require at least read
check_auth(1);


// setup cookies
if (!isset($_COOKIE["netmrgDevTree"]) || !is_array($_COOKIE["netmrgDevTree"]))
{
	$_COOKIE["netmrgDevTree"] = array();
	$_COOKIE["netmrgDevTree"]["group"] = array();
	$_COOKIE["netmrgDevTree"]["device"] = array();
	$_COOKIE["netmrgDevTree"]["monitor"] = array();
} // end if no cookies
else
{
	if (!empty($_COOKIE["netmrgDevTree"]["group"]))
	{
		$_COOKIE["netmrgDevTree"]["group"] = explode(",", $_COOKIE["netmrgDevTree"]["group"]);
	}
	else
	{
		$_COOKIE["netmrgDevTree"]["group"] = array();
	} // end if group not empty

	if (!empty($_COOKIE["netmrgDevTree"]["device"]))
	{
		$_COOKIE["netmrgDevTree"]["device"] = explode(",", $_COOKIE["netmrgDevTree"]["device"]);
	}
	else
	{
		$_COOKIE["netmrgDevTree"]["device"] = array();
	} // end if device not empty

	if (!empty($_COOKIE["netmrgDevTree"]["monitor"]))
	{
		$_COOKIE["netmrgDevTree"]["monitor"] = explode(",", $_COOKIE["netmrgDevTree"]["monitor"]);
	}
	else
	{
		$_COOKIE["netmrgDevTree"]["monitor"] = array();
	} // end if monitor not empty
}

// if we need to do something
if (!empty($_REQUEST["action"]))
{
	if ($_REQUEST["action"] == "expand")
	{
		if (!empty($_REQUEST["groupid"]))
		{
			array_push($_COOKIE["netmrgDevTree"]["group"], $_REQUEST["groupid"]);
		} // end if group id
	
		else if (!empty($_REQUEST["deviceid"]))
		{
			array_push($_COOKIE["netmrgDevTree"]["device"], $_REQUEST["deviceid"]);
		} // end if device id

		else if (!empty($_REQUEST["monid"]))
		{
			array_push($_COOKIE["netmrgDevTree"]["monitor"], $_REQUEST["monid"]);
		} // end if monitor id
	} // end if we need to expand something

	else if ($_REQUEST["action"] == "collapse")
	{
		if (!empty($_REQUEST["groupid"]))
		{
			if (in_array($_REQUEST["groupid"], $_COOKIE["netmrgDevTree"]["group"]))
			{
				unset($_COOKIE["netmrgDevTree"]["group"][array_search($_REQUEST["groupid"], $_COOKIE["netmrgDevTree"]["group"])]);
			} // end if group is currently expanded
		} // end if group id
	
		else if (!empty($_REQUEST["deviceid"]))
		{
			if (in_array($_REQUEST["deviceid"], $_COOKIE["netmrgDevTree"]["device"]))
			{
				unset($_COOKIE["netmrgDevTree"]["device"][array_search($_REQUEST["deviceid"], $_COOKIE["netmrgDevTree"]["device"])]);
			} // end if device is currently expanded
		} // end if device id

		else if (!empty($_REQUEST["monid"]))
		{
			if (in_array($_REQUEST["monid"], $_COOKIE["netmrgDevTree"]["monitor"]))
			{
				unset($_COOKIE["netmrgDevTree"]["monitor"][array_search($_REQUEST["monid"], $_COOKIE["netmrgDevTree"]["monitor"])]);
			} // end if monitor is currently expanded
		} // end if monitor id
	} // end if we need to colapse something
} // end if we need to do something

// set our new cookie to last for a week (7days * 24hrs * 60min * 60sec)
setcookie("netmrgDevTree[group]", implode(",", $_COOKIE["netmrgDevTree"]["group"]), time()+604800);
setcookie("netmrgDevTree[device]", implode(",", $_COOKIE["netmrgDevTree"]["device"]), time()+604800);
setcookie("netmrgDevTree[monitor]", implode(",", $_COOKIE["netmrgDevTree"]["monitor"]), time()+604800);
?>


<?
refresh_tag();
begin_page("device_tree.php", "Device Tree");
?>
<table width="100%" border="0" cellspacing="2" cellpadding="2" align="center">
	<tr>
		<td colspan="5" bgcolor="<?php print(get_color_by_name("edit_main_header")); ?>">
		<font color="<?php print(get_color_by_name("edit_main_header_text")); ?>">
		<b>Device Tree</b>
		</font>
		</td>
	</tr>
	<tr bgcolor="<?php print(get_color_by_name("edit_header")); ?>">

	<td width=""><b><font color="<?php print(get_color_by_name("edit_header_text")); ?>">Group</font></b></td>
	<td width=""><b><font color="<?php print(get_color_by_name("edit_header_text")); ?>">Device</font></b></td>
	<td width=""><b><font color="<?php print(get_color_by_name("edit_header_text")); ?>">Monitors</font></b></td>
	<td width=""><b><font color="<?php print(get_color_by_name("edit_header_text")); ?>">Events</font></b></td>
	<td width=""><b><font color="<?php print(get_color_by_name("edit_header_text")); ?>">Situation</font></b></td>


<?php
draw_group(0);

function draw_group($grp_id, $depth = 0)
{
	// for each group
	$grp_results = do_query("SELECT * FROM mon_groups WHERE parent_id=$grp_id ORDER BY name");
	while ($grp_row = mysql_fetch_array($grp_results))
	{
		$grp_id = $grp_row["id"];
		$grp_action = "";

		// draw +- and create link for group to expand/collapse
		if (in_array($grp_id, $_COOKIE["netmrgDevTree"]["group"]))
		{
			$img = get_image_by_name("hide");
			$grp_action = "collapse";
		}
		else
		{
			$img = get_image_by_name("show");
			$grp_action = "expand";
		} // end if this group is expanded
		make_display_item("<img border=0 height=1 width=" . ($depth * 8) . "><img src=\"" . $img . "\" border=\"0\"> " . $grp_row["name"], $_SERVER["PHP_SELF"] . "?action=$grp_action&groupid=$grp_id","[<a href=\"./view.php?pos_id_type=0&pos_id=$grp_id\">View</a>]","","","","","",get_img_tag_from_status(get_group_status($grp_id)),"");


		// if group is expanded, show the devices
		if (in_array($grp_id, $_COOKIE["netmrgDevTree"]["group"]))
		{
			draw_group($grp_id, $depth + 1);
			$dev_results = do_query("
				SELECT dev_parents.dev_id AS id, mon_devices.name AS name
				FROM dev_parents
				LEFT JOIN mon_devices ON dev_parents.dev_id=mon_devices.id
				WHERE grp_id = '$grp_id'
				ORDER BY name");
			// while we still have devices
			while ($dev_row = mysql_fetch_array($dev_results))
			{
				$device_id = $dev_row["id"];
				$device_action = "";

				// draw +- and create link for device to expand/collapse
				if (in_array($device_id, $_COOKIE["netmrgDevTree"]["device"]))
				{
					$img = get_image_by_name("hide");
					$device_action = "collapse";
				}
				else
				{
					$img = get_image_by_name("show");
					$device_action = "expand";
				} // end if D tree
				make_display_item("","","<img src=\"" . $img . "\" border=\"0\"> " . $dev_row["name"], $_SERVER["PHP_SELF"] . "?action=$device_action&deviceid=$device_id","[<a href=\"./view.php?pos_id_type=1&pos_id=$device_id\">View</a>]","","","",get_img_tag_from_status(get_device_status($device_id)),"");

				// if this device is expanded, show the monitors
				if (in_array($device_id, $_COOKIE["netmrgDevTree"]["device"]))
				{
			        $mon_results = do_query("
				        SELECT mon_monitors.id, mon_test.name as test_name, mon_devices.ip AS ip, 
						mon_test.cmd AS cmd, mon_monitors.params AS params,
						mon_devices.name AS dev_name, mon_test.name as test_name
				        FROM mon_monitors
				        LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
				        LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
				        WHERE mon_monitors.device_id=$device_id");
					// while we have monitors
					while ($mon_row = mysql_fetch_array($mon_results))
					{
						$mon_id = $mon_row["id"];
						$monitor_action = "";

						// draw +- and create link for monitor expand/collapse
						if (in_array($mon_id, $_COOKIE["netmrgDevTree"]["monitor"]))
						{
							$img = get_image_by_name("hide");
							$monitor_action = "collapse";
						}
						else
						{
							$img = get_image_by_name("show");
							$monitor_action = "expand";
						} // end if M tree
						make_display_item("","","","","<img src=\"" . $img . "\" border=\"0\"> " . get_short_monitor_name($mon_row["id"]), $_SERVER["PHP_SELF"] . "?action=$monitor_action&monid=$mon_id","","",get_img_tag_from_status(get_monitor_status($mon_id)) . " [Graph]","./enclose_graph.php?type=mon&id=$mon_id");

						// if this monitor is expanded, show the events
						if (in_array($mon_id, $_COOKIE["netmrgDevTree"]["monitor"]))
						{
	       					$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id");
							$event_total = mysql_num_rows($event_results);

							// For each event
							for ($event_count = 1; $event_count <= $event_total; ++$event_count)
							{
								$event_row = mysql_fetch_array($event_results);
								$event_id = $event_row["id"];
								$color = get_color_from_situation($event_row["situation"]);

								if ($event_row["last_status"] == 1)
								{
									$img = ("<img src=\"" . get_image_by_name($color . "_led_on") . "\" border=\"0\">");
								}
								else
								{
									$img = ("<img src=\"" . get_image_by_name($color . "_led_off") . "\" border=\"0\">");
								} // end if last status
								make_display_item("","","","","","",$event_row["display_name"],"",$img,"");
							} // end event for

						} // end if monitor expanded

					} // end while each monitor

				} // end if device expanded

			} // end while each device

		} // end if group expanded

	} // end while each group

} // end draw_group()
?>


</table>
<?php
end_page();
?>