<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           SNMP Cache Viewer                          #
#           snmp_cache_view.php   		       #
#                                                      #
#                                                      #
#                                                      #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/processing.php");
require_once(netmrg_root() . "lib/auth.php");

if (isset($graph)) { 
check_auth(2);

# get some data to play with
$q_dev = do_query("SELECT * FROM mon_devices WHERE id=$dev_id");
$r_dev = mysql_fetch_array($q_dev);

$q_snmp = do_query("SELECT * FROM snmp_cache WHERE dev_id=$dev_id AND if_index='$index'");
$r_snmp = mysql_fetch_array($q_snmp);

# add two monitors, if ifName defined, use it, otherwise use ifIndex

if ($r_snmp["if_name"] != "")
{
$snmp_val = $r_snmp["if_name"];
$snmp_type = 3;
} else {
$snmp_val = $index;
$snmp_type = 2;
}
do_update("INSERT INTO mon_monitors SET device_id=$dev_id, test_id=0, params=\"\", rrd_id=2, graphed=1, snmp_test=0, snmp_index_type=" . $snmp_type . ", snmp_index_value=\"$snmp_val\", snmp_data=3, disk_index_type=0, disk_index_value=\"\", disk_data=0, mon_type=3");
$mon_id_1 = mysql_insert_id();
do_update("INSERT INTO mon_monitors SET device_id=$dev_id, test_id=0, params=\"\", rrd_id=2, graphed=1, snmp_test=0, snmp_index_type=" . $snmp_type . ", snmp_index_value=\"$snmp_val\", snmp_data=8, disk_index_type=0, disk_index_value=\"\", disk_data=0, mon_type=3");
$mon_id_2 = mysql_insert_id();


# add graph
do_update("INSERT INTO graphs SET name=\"" . $r_dev["name"] . " - " . $r_snmp["if_alias"]  . "\", 
	comment=\"Interface: " . $r_snmp["if_name"] . "\"," . ' xsize=400, ysize=100' .
	', vert_label="' . 'Bytes Per Second", show_legend=1');
$graph_id = mysql_insert_id();

# add graph DS's
		do_update("INSERT INTO graph_ds SET " .
		'src_type=' . "0" . ',' .
		'src_id=' . $mon_id_1 . ',' .
		'color="' . '#00EE00' . '",' .
		'type=' . "4" . ',' .
		'graph_id=' . $graph_id . ',' .
		'label="' . 'Inbound' . '"');
		
		do_update("INSERT INTO graph_ds SET " .
		'src_type=' . "0" . ',' .
		'src_id=' . $mon_id_2 . ',' .
		'color="' . '#0000EE' . '",' .
		'type=' . "2" . ',' .
		'graph_id=' . $graph_id . ',' .
		'label="' . 'Outbound' . '"');

# add graph to view for the device

do_update("INSERT INTO view SET pos_id = $dev_id, pos_id_type = 1, graph_id = $graph_id, graph_id_type = \"custom\", pos = $index");

# Redirect to Graph Edit page (it will redirect back when done)
Header("Location: ./custom_graphs.php?action=edit&graph_id=$graph_id&return_type=traffic&return_id=$dev_id");
exit(0);

}
check_auth(1);
$query = "SELECT * FROM snmp_cache";
$sort_href = "$SCRIPT_NAME?";
if (isset($dev_id)) { $query .= " WHERE dev_id=$dev_id ORDER BY if_index"; $sort_href .= "dev_id=$dev_id&"; }
$sort_href .= "order_by";
if (isset($order_by)) { $query .= " ORDER BY $order_by"; }
begin_page();

make_plain_display_table(get_device_name($dev_id) . " - SNMP Interface Cache","Interface #","$sort_href=if_index","Interface Name","$sort_href=if_name","Interface Description","$sort_href=if_desc","IP Address","$sort_href=if_ip","MAC Address","$sort_href=if_mac","Alias","$sort_href=if_alias","Commands","");
$handle = do_query($query);
$num = mysql_num_rows($handle);
for ($i = 0; $i < $num; $i++) {
$row = mysql_fetch_array($handle);
make_display_item($row["if_index"],"",$row["if_name"],"",$row["if_desc"],"",$row["if_ip"],"",$row["if_mac"],"",$row["if_alias"],"",formatted_link("Graph Traffic","./snmp_cache_view.php?graph=1&dev_id=" . $row["dev_id"] . "&index=" . $row["if_index"]), "");
} 
print("</table>");
end_page();

?>
