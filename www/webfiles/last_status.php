<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Monitors Status Page                       #
#           last_status.php                            #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/database.php");
require_once(netmrg_root() . "lib/processing.php");
require_once(netmrg_root() . "lib/misc.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);
$G_tree = get_array_from_cookie("G_tree");
$D_tree = get_array_from_cookie("D_tree");
$M_tree = get_array_from_cookie("M_tree");

if (isset($expand)) {

	if ($type == "G") {
	$G_tree[$expand] = (1 - $G_tree[$expand]);
	}

	if ($type == "D") {
	$D_tree[$expand] = (1 - $D_tree[$expand]);
	}

	if ($type == "M") {
	$M_tree[$expand] = (1 - $M_tree[$expand]);
	}

}

store_array_in_cookie("G_tree",$G_tree);
store_array_in_cookie("D_tree",$D_tree);
store_array_in_cookie("M_tree",$M_tree);

refresh_tag();
begin_page();

?>
<table width="100%" border="0" cellspacing="2" cellpadding="2" align="center">
	<tr>
		<td colspan="5" bgcolor="<? print(get_color_by_name("edit_main_header")); ?>">
		<font color="<? print(get_color_by_name("edit_main_header_text")); ?>">
		<b>Device Tree</b>
		</font>
		</td>
	</tr>
	<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>">

	<td width=""><b><font color="<? print(get_color_by_name("edit_header_text")); ?>">Group</font></b></td>
        <td width=""><b><font color="<? print(get_color_by_name("edit_header_text")); ?>">Device</font></b></td>
	<td width=""><b><font color="<? print(get_color_by_name("edit_header_text")); ?>">Monitors</font></b></td>
	<td width=""><b><font color="<? print(get_color_by_name("edit_header_text")); ?>">Events</font></b></td>
	<td width=""><b><font color="<? print(get_color_by_name("edit_header_text")); ?>">Situation</font></b></td>
<?

draw_group(0);

function draw_group($grp_id, $depth = 0)
{

GLOBAL $G_tree;
GLOBAL $D_tree;
GLOBAL $M_tree;

$grp_results = do_query("SELECT * FROM mon_groups WHERE parent_id=$grp_id ORDER BY name");
$grp_total = mysql_num_rows($grp_results);
for ($grp_count = 1; $grp_count <= $grp_total; ++$grp_count) {
# For each group

	$grp_row = mysql_fetch_array($grp_results);
	$grp_id = $grp_row["id"];

	if ($G_tree[$grp_id] == 0) {
	$img = get_image_by_name("show");
	} else {
	$img = get_image_by_name("hide");
	}

	make_display_item("<img border=0 height=1 width=" . ($depth * 8) . "><img src=\"" . $img . "\" border=\"0\"> " . $grp_row["name"],$SCRIPT_NAME . "?expand=$grp_id&type=G","[<a href=\"./view.php?pos_id_type=0&pos_id=$grp_id\">View</a>]","","","","","",get_img_tag_from_status(get_group_status($grp_id)),"");

	if ($G_tree[$grp_id] == 1) {
		draw_group($grp_id, $depth + 1);
		$dev_results = do_query("
		SELECT dev_parents.dev_id AS id, mon_devices.name AS name
		FROM dev_parents
		LEFT JOIN mon_devices ON dev_parents.dev_id=mon_devices.id
		WHERE grp_id=$grp_id
		ORDER BY name");
        	$dev_total = mysql_num_rows($dev_results);
		for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count) {
		# For each device

			$dev_row = mysql_fetch_array($dev_results);
			$device_id = $dev_row["id"];

			if ($D_tree[$device_id] == 0) {
			$img = get_image_by_name("show");
			} else {
			$img = get_image_by_name("hide");
			}

			make_display_item("","","<img src=\"" . $img . "\" border=\"0\"> " . $dev_row["name"],$SCRIPT_NAME . "?expand=$device_id&type=D","[<a href=\"./view.php?pos_id_type=1&pos_id=$device_id\">View</a>]","","","",get_img_tag_from_status(get_device_status($device_id)),"");
			if ($D_tree[$device_id] == 1) {
		        $mon_results = do_query("
			        SELECT mon_monitors.id, mon_test.name as test_name, mon_devices.ip AS ip, mon_test.cmd AS cmd, mon_monitors.params AS params,
				   mon_devices.name AS dev_name, mon_test.name as test_name
			        FROM mon_monitors
			        LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id
			        LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id
			        WHERE mon_monitors.device_id=$device_id");
			$mon_total = mysql_num_rows($mon_results);
				for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count) {
				# For each monitor, do test

					$mon_row = mysql_fetch_array($mon_results);
					$mon_id = $mon_row["id"];

					if ($M_tree[$mon_id] == 0) {
					$img = get_image_by_name("show");
					} else {
					$img = get_image_by_name("hide");
					}

					make_display_item("","","","","<img src=\"" . $img . "\" border=\"0\"> " . get_short_monitor_name($mon_row["id"]),$SCRIPT_NAME . "?expand=$mon_id&type=M","","",get_img_tag_from_status(get_monitor_status($mon_id)) . " [Graph]","./enclose_graph.php?type=mon&id=$mon_id");
					if ($M_tree[$mon_id] == 1) {
       					$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id");
					$event_total = mysql_num_rows($event_results);

						for ($event_count = 1; $event_count <= $event_total; ++$event_count) {
						# For each event

							$event_row = mysql_fetch_array($event_results);
							$event_id = $event_row["id"];
							$color = get_color_from_situation($event_row["situation"]);

							if ($event_row["last_status"] == 1) {
								$img = ("<img src=\"" . get_image_by_name($color . "_led_on") . "\" border=\"0\">");
								} else {
								$img = ("<img src=\"" . get_image_by_name($color . "_led_off") . "\" border=\"0\">");
								}
							make_display_item("","","","","","",$event_row["display_name"],"",$img,"");
						} # end event for
					} # end monitor for
				} # end monitor expand if
			} # end device for
		} # end device expand if
	} # end group for
} # end group expand if
}
?></table><?

end_page();

?>
