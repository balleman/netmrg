<?php
/********************************************
* NetMRG Integrator
*
* monitors.php
* Monitors Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action

switch($_REQUEST["action"])
{
	case "doedit":
		check_auth(2);
		do_edit();
		redirect();
		break;
	
	case "dodelete":
		check_auth(2);
		do_delete();
		redirect();
		break;
		
	case "duplicate":
		check_auth(2);
		duplicate_monitor($_REQUEST['mon_id']);
		redirect();
		break;
	
	case "edit":
	case "add":
		edit();
		break;
	
	default:
	case "list":
		do_list();
		break;
} // end switch action



/***** FUNCTIONS *****/
function do_list()
{
	begin_page("monitor.php", "Monitors", 1);
	DrawGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);

	js_confirm_dialog("del", "Are you sure you want to delete monitor ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&mon_id=");
	make_display_table("Monitors for " . get_dev_sub_device_name($_REQUEST["sub_dev_id"]),
		"{$_SERVER['PHP_SELF']}?action=add&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => "Test"),
		array("text" => "Data"),
		array("text" => "Graph")
	); // end make_display_table();

	$mon_results = db_query("SELECT * FROM monitors WHERE sub_dev_id='{$_REQUEST['sub_dev_id']}'");
	$mon_total = db_num_rows($mon_results);

	for ($mon_count = 0; $mon_count < $mon_total; $mon_count++)
	{
		$mon_row = db_fetch_array($mon_results);
		$mon_id  = $mon_row["id"];

		if ($mon_row["data_type"] != -1)
		{
			$graph = "<a href=\"enclose_graph.php?type=mon&id=$mon_id\"><img border='0' src='get_graph.php?type=tinymon&id=$mon_id'></a>";
		}
		else
		{
			$graph = "Not Graphed";
		} // end if data type

		if ((!isset($mon_row['delta_time'])) || ($mon_row["delta_time"] == 0))
		{
			$rate_of_change = "";
		}
		else
		{
			$rate_of_change = sanitize_number($mon_row["delta_val"] / $mon_row["delta_time"],2);
		} // end if delta
		
		if (!isset($mon_row['last_val']))
		{
			$mon_row['last_val'] = "";
		}
		
		if (!isset($mon_row['last_time']))
		{
			$mon_row['last_time'] = "";
		}

		$data = '<table border="0" cellpadding="2" cellspacing="2" align="left" width="100%" height="100%">
			<tr>
				<td bgcolor="#eeeeee" width="50%">Value</td>
				<td>'. sanitize_number($mon_row["last_val"]) .'</td>
			</tr>
			<tr>
				<td bgcolor="#eeeeee">Rate of Change</td>
				<td>'. $rate_of_change .'</td>
			</tr>
			<tr>
				<td bgcolor="#eeeeee">Time Stamp</td>
				<td>'. $mon_row["last_time"] .'</td></tr>
			</table>';

		$short_name = get_short_monitor_name($mon_row["id"]);
		$html_name = htmlspecialchars($short_name);
		$java_name = addslashes($html_name);

		make_display_item("editfield".($mon_count%2),
			array("text" => $html_name, "href" => "events.php?mon_id={$mon_row['id']}&tripid={$_REQUEST['tripid']}"),
			array("text" => $data),
			array("text" => $graph),
			array("text" => formatted_link("Duplicate", "{$_SERVER['PHP_SELF']}?action=duplicate&mon_id=$mon_id&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" . 
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&mon_id=$mon_id&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}") . "&nbsp;" .
				formatted_link("Delete","javascript:del('$java_name', '$mon_id')"))
		); // end make_display_item();

	} // end for each monitor

	?>
	</table>
	<?php

	end_page();

} // end do_list()


function edit()
{
	begin_page("monitor.php", "Monitors");
	
	// if we're editing a monitor
	if ($_REQUEST["action"] == "edit")
	{
		make_edit_table("Edit Monitor", "return validateform();");
	} // end if edit
	// if we're adding a monitor
	else
	{
		make_edit_table("Add Monitor", "return validateform();");
	} // end else add
	
	// if we're editing a monitor
	if ($_REQUEST["action"] == "edit")
	{
		$mon_results = db_query("
			SELECT
			monitors.id                     AS id,
			monitors.sub_dev_id             AS sub_dev_id,
			monitors.data_type              AS data_type,
			monitors.min_val                AS min_val,
			monitors.max_val                AS max_val,
			monitors.test_type              AS test_type,
			monitors.test_id                AS test_id,
			monitors.test_params            AS test_params
			FROM monitors
			WHERE monitors.id='{$_REQUEST['mon_id']}'
			");
		$mon_row = db_fetch_array($mon_results);
		if (empty($mon_row["min_val"])) { $mon_row["min_val"] = "U"; }
		if (empty($mon_row["max_val"])) { $mon_row["max_val"] = "U"; }

	
	} // end if editing a monitor
	// if we're adding a monitor
	else
	{
		$mon_id = 0;
		$mon_row["data_type"] = 1;
		$mon_row["test_id"] = 1;
		$mon_row["min_val"] = "U";
		$mon_row["max_val"] = "U";
		if (!empty($_REQUEST["type"]))
		{
			$mon_row["test_type"] = $_REQUEST["type"];
		}
		else
		{
			$mon_row["test_type"] = 0;
		}
		$mon_row["test_params"] = "";
		$_REQUEST["mon_id"] = 0;
	} // end if adding a monitor
	
	// TODO
	if (isset($_REQUEST["sub_dev_id"]))
	{
		$mon_row["sub_dev_id"] = $_REQUEST["sub_dev_id"];
		$dev_thingy = "&sub_dev_id={$_REQUEST['sub_dev_id']}";
	}
	else
	{
		$dev_thingy = "";
	}
	
	make_edit_group("General Parameters");
	
	echo "
		<script type='text/javascript'>
		
		function redisplay(selectedIndex)
		{
			window.location = '{$_SERVER['PHP_SELF']}?mon_id={$_REQUEST['mon_id']}&tripid={$_REQUEST['tripid']}&action={$_REQUEST['action']}" . $dev_thingy . "&type=' + selectedIndex;
		}
		
		function validateform()
		{
			if (document.editform.min_val.value != 'U'
				&& document.editform.max_val.value != 'U'
				&& document.editform.min_val.value >= document.editform.max_val.value)
			{
				alert('Minimum not allowed to be greater than or equal to Maximum');
				return false;
			}
			
			return true;
		}
		
		function make_min_undefined()
		{
			document.editform.min_val.value = 'U';
		}
		function make_max_undefined()
		{
			document.editform.max_val.value = 'U';
		}
		</script>
		";
	
	// if we've been passed a test type
	if (!empty($_REQUEST["type"]))
	{
		$mon_row["test_type"] = $_REQUEST["type"];
	} // end if test type is set
	// else default to test type 1 (script)
	else if (empty($mon_row["test_type"]))
	{
		$mon_row["test_type"] = 1;
	} // end if no test type
	GLOBAL $TEST_TYPES;
	make_edit_select_from_array("Monitoring Type:", "test_type", $TEST_TYPES, $mon_row["test_type"], "onChange='redisplay(form.test_type.options[selectedIndex].value);'");
	
	if ($mon_row["test_type"] == 1)
	{
		make_edit_group("Script Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("Script Test:", "test_id", "tests_script", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("Script Test:", "test_id", "tests_script", $mon_row["test_id"]);
		} // end if editing
	}
	
	if ($mon_row["test_type"] == 2)
	{
		make_edit_group("SNMP Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("SNMP Test:", "test_id", "tests_snmp", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("SNMP Test:", "test_id", "tests_snmp", $mon_row["test_id"]);
		} // end if editing
	}
	
	if ($mon_row["test_type"] == 3)
	{
		make_edit_group("SQL Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("SQL Test:", "test_id", "tests_sql", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("SQL Test:", "test_id", "tests_sql", $mon_row["test_id"]);
		} // end if editing
	}
	
	if ($mon_row["test_type"] == 4)
	{
		make_edit_group("Internal Test Options");
		if ($_REQUEST["action"] == "add")
		{
			make_edit_select_from_table("Internal Test:", "test_id", "tests_internal", "dl4392l234");
		} // end if adding
		else
		{
			make_edit_select_from_table("Internal Test:", "test_id", "tests_internal", $mon_row["test_id"]);
		} // end if editing
	}
	
	make_edit_text("Parameters:", "test_params", 50, 100, htmlspecialchars($mon_row["test_params"]));
	make_edit_group("Graphing Options");
	make_edit_select_from_table("Data Type:", "data_type", "data_types", $mon_row["data_type"]);
	make_edit_text("Minimum Value:", "min_val", "10", "20", $mon_row["min_val"]);
	make_edit_text("Maximum Value:", "max_val", "10", "20", $mon_row["max_val"]);
	make_edit_label('[<a href="javascript:make_min_undefined();">make minimum undefined</a>]
		[<a href="javascript:make_max_undefined();">make maximum undefined</a>]');

	make_edit_hidden("action","doedit");
	make_edit_hidden("mon_id",$_REQUEST["mon_id"]);
	make_edit_hidden("sub_dev_id",$_REQUEST["sub_dev_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	
	make_edit_submit_button();
	make_edit_end();
	
	end_page();
} // end edit();

function redirect()
{
	header("Location: monitors.php?sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}");
} // end redirect()


function do_edit()
{
	if ($_REQUEST["mon_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id='{$_REQUEST['mon_id']}'";
	}
	
	if ($_REQUEST["min_val"] == "U") { $_REQUEST["min_val"] = "NULL"; }
	if ($_REQUEST["max_val"] == "U") { $_REQUEST["max_val"] = "NULL"; }
	
	
	db_update("$db_cmd monitors SET
		sub_dev_id='{$_REQUEST['sub_dev_id']}',
		test_type='{$_REQUEST['test_type']}',
		test_id='{$_REQUEST['test_id']}',
		test_params='" . db_escape_string(fix_magic_quotes($_REQUEST['test_params'])) ."',
		data_type='{$_REQUEST['data_type']}',
		min_val={$_REQUEST['min_val']},
		max_val={$_REQUEST['max_val']},
		tuned=0 $db_end");
	
} // end do_edit()


function do_delete()
{
	delete_monitor($_REQUEST["mon_id"]);
} // end do_delete()

?>
