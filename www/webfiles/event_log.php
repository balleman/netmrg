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

if (!isset($index)) { $index = 0; }

$query = "SELECT * FROM event_log ORDER BY id DESC LIMIT $index,25";
$sort_href = "{$_SERVER['PHP_SELF']}?";
if (isset($dev_id)) { $query .= " WHERE dev_id=$dev_id"; $sort_href .= "dev_id=$dev_id&"; }
$sort_href .= "order_by";
if (isset($order_by)) { $query .= " ORDER BY $order_by"; }

begin_page("event_log.php", "Event Log");

make_plain_display_table("Event Log","Device","","Date/Time","","Time Since Last Change","","Event","");
$handle = do_query($query);
$num = mysql_num_rows($handle);
for ($i = 0; $i < $num; $i++)
{
	$row = mysql_fetch_array($handle);
	make_display_item($row["dev_name"],"",date("Y/m/d H:i:s",$row["date"]),"",format_time_elapsed($row["since_last_change"]),"",get_img_tag_from_status($row["situation"]) . " " . str_replace(" ", "&nbsp;", $row["event_text"]),"");
} // end for each row in event log
print("</table>");
echo('<br>');
if ($index >= 25)
{
	echo('<a href="' . $_SERVER["PHP_SELF"] . '?index=' . ($index - 25) . '">Prev</a>&nbsp;&nbsp;');
}
else
{
	echo("Prev&nbsp;&nbsp;");
}

echo('<a href="' . $_SERVER["PHP_SELF"] . '?index=' . ($index + 25) . '">Next</a>');

end_page();

?>
