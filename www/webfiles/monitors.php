<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Monitors Editing Page                      #
#           monitors.php                               #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/processing.php");
require_once(netmrg_root() . "lib/auth.php");
check_auth(1);

function redirect()
{
	GLOBAL $sub_dev_id;
	header("Location: ./monitors.php?sub_dev_id=$sub_dev_id");
}

function do_edit()
{
	GLOBAL $mon_id, $sub_dev_id, $test_type, $test_id, $test_params, $data_type, $min_val, $max_val;

	check_auth(2);

	if ($mon_id == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	} else {
		$db_cmd = "UPDATE";
		$db_end = "WHERE id=$mon_id";
	}

        if ($min_val == "U") { $min_val = "NULL"; }
        if ($max_val == "U") { $max_val = "NULL"; }

        //do_update("$db_cmd monitors SET sub_dev_id=$sub_dev_id, test_id=$test_id, params=\"$cmd_params\", rrd_id=$rrd_id, graphed=$graphed, snmp_test=$snmp_test, snmp_index_type=$snmp_index_type, snmp_index_value=\"$snmp_index_value\", snmp_data=$snmp_data, disk_index_type=$disk_index_type, disk_index_value=\"$disk_index_value\", disk_data=$disk_data, mon_type=$mon_type, max_val=\"$max_val\", tuned=0 $db_end");
        do_update("$db_cmd monitors SET
                        sub_dev_id=$sub_dev_id,
                        test_type=$test_type,
                        test_id=$test_id,
                        test_params=\"$test_params\",
                        data_type=$data_type,
                        min_val=$min_val,
                        max_val=$max_val,
                        tuned=0 $db_end");
}

function do_delete()
{
	GLOBAL $mon_id;
	check_auth(2);
	delete_monitor($mon_id);
}

if ($action == "doedit")
{
	do_edit();
	redirect();
} 
elseif ($action == "dodelete")
{
	do_delete();
	redirect();
}
elseif (($action == "edit") || ($action == "add"))
{
	edit();
}
else
{
	do_list();
}

function do_list()
{
	refresh_tag();
	begin_page();

	GLOBAL $sub_dev_id;

	js_confirm_dialog("del", "Are you sure you want to delete monitor ", " and all associated items?", "$SCRIPT_NAME?action=dodelete&dev_id=$dev_id&mon_id=");
	GLOBAL $custom_add_link;
        $custom_add_link = "$SCRIPT_NAME?action=add&sub_dev_id=$sub_dev_id";
        make_display_table("Monitors for " . get_sub_device_name($sub_dev_id),"Test","", "Data", "", "Graph","");

	$mon_results = do_query("SELECT * FROM monitors WHERE sub_dev_id=$sub_dev_id");

	$mon_total = mysql_num_rows($mon_results);

	for ($mon_count = 0; $mon_count < $mon_total; $mon_count++) 
	{

		$mon_row = mysql_fetch_array($mon_results);
		$mon_id  = $mon_row[0];

		if ($mon_row["data_type"] != -1)
		{
			//$graph = formatted_link("View Graph", "./enclose_graph.php?type=mon&id=$mon_id");
			$graph = "<a href='./enclose_graph.php?type=mon&id=$mon_id'><img border='0' src='./get_graph.php?type=tinymon&id=$mon_id'></a>";
		} else {
			$graph = "Not Graphed";
		}

                if ($mon_row["delta_time"] == 0)
		{
		        $rate_of_change = "";
		}
		else
		{
		        $rate_of_change = sanitize_number($mon_row["delta_val"] / $mon_row["delta_time"],2);
		}

                $data =
		"<table border='0' cellpadding='2' cellspacing='2' align='left' width='100%' height='100%'>
		 <tr><td bgcolor='#eeeeee' width='50%'>Value</td>
		 <td>" . sanitize_number($mon_row["last_val"]) . "</td></tr>
		 <tr><td bgcolor='#eeeeee'>Rate of Change</td>
		 <td>" . $rate_of_change . "</td></tr>
		 <tr><td bgcolor='#eeeeee'>Time Stamp</td>
		 <td>" . $mon_row["last_time"] . "</td></tr>
		 </table>
		 ";

		make_display_item(      get_short_monitor_name($mon_row["id"]),                         "",
			$data,						"",
                        $graph,                        					"",
			formatted_link("Edit", "$SCRIPT_NAME?action=edit&mon_id=$mon_id&sub_dev_id=$sub_dev_id") . "&nbsp;" .
                        formatted_link("Delete","javascript:del('" . "" . "', '$mon_id')"), "");

	}

	?>
	</table>
	<?

	end_page();

} 

function edit()
{

	begin_page();

	GLOBAL $action, $mon_id, $type, $sub_dev_id;

	if ($action == "edit")
	{
		make_edit_table("Edit Monitor");
	} else {
		make_edit_table("Add Monitor");
	}

	if ($action == "edit") { 

	$mon_results = do_query("       SELECT
                	                monitors.id                     AS id,
        	                        monitors.sub_dev_id             AS sub_dev_id,
                        	        monitors.data_type              AS data_type,
                                	monitors.min_val                AS min_val,
	                                monitors.max_val                AS max_val,
        	                        monitors.test_type              AS test_type,
                	                monitors.test_id                AS test_id,
                        	        monitors.test_params            AS test_params
                                	FROM monitors
	                                WHERE monitors.id=$mon_id
       		                ");

	$mon_row = mysql_fetch_array($mon_results);

	}
	else
	{

		$mon_id = 0;

		$mon_row["data_type"] = 1;
		$mon_row["test_id"] = 1;
                if ($mon_row["min_val"] == "") { $mon_row["min_val"] = "U"; }
                if ($mon_row["max_val"] == "") { $mon_row["max_val"] = "U"; }

	}

	if (isset($sub_dev_id))
	{
		$mon_row["sub_dev_id"] = $sub_dev_id;
		$dev_thingy = "&sub_dev_id=$sub_dev_id";
	} else {
		$dev_thingy = "";
	}

	make_edit_group("General Parameters");

	echo("
		<script type='text/javascript'>

		function redisplay(selectedIndex)
		{
       	         window.location = '$SCRIPT_NAME?mon_id=$mon_id&action=$action" . $dev_thingy . "&type=' + selectedIndex;
		}
		</script>
		");

	if (isset($type))
	{
		if ($type == 0) { $type = 4; }
		$mon_row["test_type"] = $type;
	}
	make_edit_select_from_table("Monitoring Type:","test_type", "test_types",$mon_row["test_type"], "", "onChange='redisplay(this.selectedIndex);'");

	if ($mon_row["test_type"] == 1)
	{
		make_edit_group("Script Options");
		make_edit_select_from_table("Script Test:", "test_id", "tests_script", $mon_row["test_id"]);
	}

	if ($mon_row["test_type"] == 2)
	{
	        make_edit_group("SNMP Options");
	        make_edit_select_from_table("SNMP Test:", "test_id", "tests_snmp", $mon_row["test_id"]);
	}

	if ($mon_row["test_type"] == 3)
	{
		make_edit_group("SQL Options");
		make_edit_select_from_table("SQL Test:", "test_id", "tests_sql", $mon_row["test_id"]);
	}

	if ($mon_row["test_type"] == 4)
	{
		make_edit_group("Internal Test Options");
		make_edit_select_from_table("Internal Test:", "test_id", "tests_internal", $mon_row["test_id"]);
	}

	make_edit_text("Parameters:", "test_params", 50, 100, $mon_row["test_params"]);

	make_edit_group("Graphing Options");
	make_edit_select_from_table("Data Type:", "data_type", "data_types", $mon_row["data_type"]);
	if ($mon_row["min_val"] == "")
	{
	        $mon_row["min_val"] = "U";
	}
	make_edit_text("Minimum Value:", "min_val", "10", "20", $mon_row["min_val"]);
	if ($mon_row["max_val"] == "")
	{
	        $mon_row["max_val"] = "U";
	}
	make_edit_text("Maximum Value:", "max_val", "10", "20", $mon_row["max_val"]);

	make_edit_hidden("action","doedit");
	make_edit_hidden("mon_id",$mon_id);
        make_edit_hidden("sub_dev_id",$sub_dev_id);

	make_edit_submit_button();
	make_edit_end();

	end_page();

}

?>
