<?php
/********************************************
* NetMRG Integrator
*
* event_log.php
* Event Log Viewer
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);
refresh_tag();

if (!isset($_REQUEST['index']))
{
	$_REQUEST['index'] = 0;
}

$query = "
	SELECT
		event_id,
		date,
		time_since_last_change,
		situation,
		dev_id,
		mon_devices.name AS dev_name,
		events.name AS ev_name

	FROM
		event_log,
		events,
		monitors,
		sub_devices,
		mon_devices

	WHERE	event_log.event_id 	= events.id
	AND	events.mon_id 		= monitors.id
	AND	monitors.sub_dev_id	= sub_devices.id
	AND	sub_devices.dev_id	= mon_devices.id

	ORDER BY event_log.id DESC

	LIMIT {$_REQUEST['index']},25";

$sort_href = "{$_SERVER['PHP_SELF']}?";

if (isset($dev_id))
{
	$query .= " WHERE dev_id=$dev_id";
	$sort_href .= "dev_id=$dev_id&";
}

$sort_href .= "order_by";

if (isset($order_by))
{
	$query .= " ORDER BY $order_by";
}

begin_page("event_log.php", "Event Log");

make_plain_display_table("Event Log", "Date/Time", "", "Time Since Last Change", "" , "Event", "");
$handle = do_query($query);
$num = mysql_num_rows($handle);
for ($i = 0; $i < $num; $i++)
{
	$row = mysql_fetch_array($handle);
	make_display_item(date("Y/m/d H:i:s",$row["date"]), "", format_time_elapsed($row["time_since_last_change"]), "", get_img_tag_from_status($row["situation"]) . " " . $row['dev_name'] . ": " . $row['ev_name'],"");
} // end for each row in event log
print("</table>");
echo('<br>');
if ($_REQUEST['index'] >= 25)
{
	echo('<a href="' . $_SERVER["PHP_SELF"] . '?index=' . ($_REQUEST['index'] - 25) . '">Prev</a>&nbsp;&nbsp;');
}
else
{
	echo("Prev&nbsp;&nbsp;");
}

echo('<a href="' . $_SERVER["PHP_SELF"] . '?index=' . ($_REQUEST['index'] + 25) . '">Next</a>');

end_page();

?>
