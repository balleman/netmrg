<?php
/********************************************
* NetMRG Integrator
*
* monitors.php
* Monitors Editing Page
*
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
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["ReadAll"]);

// set default action
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action

switch($_REQUEST["action"])
{
	case "doedit":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		do_edit();
		redirect();
		break;
	
	case "dodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		delete_monitor($_REQUEST['mon_id']);
		redirect();
		break;
	
	case "multidodelete":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		while (list($key,$value) = each($_REQUEST["monitor"]))
		{
			delete_monitor($key);
		}
		redirect();
		break;
		
	case "duplicate":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		duplicate_monitor($_REQUEST['mon_id']);
		redirect();
		break;
	
	case "multiduplicate":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
		while (list($key,$value) = each($_REQUEST["monitor"]))
		{
			duplicate_monitor($key);
		}
		redirect();
		break;
	
	case "add":
		check_auth($GLOBALS['PERMIT']["ReadWrite"]);
	case "edit":
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
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="sub_dev_id" value="<?php echo $_REQUEST['sub_dev_id']; ?>">
	<input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
	<?php

	if (preg_match("/snmp_cache_view.php.*type=interface/", $_SERVER["HTTP_REFERER"]))
	{
		PrepGroupNavHistory("int_snmp_cache_view", $_REQUEST["dev_id"]);
	} // end if came from interface view of snmp_cache_view.php
	else if (preg_match("/snmp_cache_view.php.*type=disk/", $_SERVER["HTTP_REFERER"]))
	{
		PrepGroupNavHistory("disk_snmp_cache_view", $_REQUEST["dev_id"]);
	} // end if came from disk view of snmp_cache_view.php
	PrepGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	DrawGroupNavHistory("sub_device", $_REQUEST["sub_dev_id"]);
	
	js_confirm_dialog("del", "Are you sure you want to delete monitor ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}&mon_id=");
	make_display_table("Monitors for " . get_dev_sub_device_name($_REQUEST["sub_dev_id"]),
		"{$_SERVER['PHP_SELF']}?action=add&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => checkbox_toolbar()),
		array("text" => "Test"),
		array("text" => "Data"),
		array("text" => "Graph")
	); // end make_display_table();

	$mon_results = db_query("SELECT * FROM monitors WHERE sub_dev_id='{$_REQUEST['sub_dev_id']}'");
	$mons = array();
	while ($arow = mysql_fetch_array($mon_results))
	{
		$arow['short_name'] = get_short_test_name($arow['test_type'], $arow['test_id'], $arow['test_params']);
		array_push($mons, $arow);
	}

	function mon_sort($a, $b)
	{
		return strcmp($a['short_name'], $b['short_name']);
	}

	usort($mons, mon_sort);
	$mon_count = 0;

	foreach ($mons as $mon_row)
	{
		$mon_id  = $mon_row["id"];

		if ($mon_row["data_type"] != -1)
		{
			$graph = "<a href=\"enclose_graph.php?type=mon&id=$mon_id\"><img border='0' src='get_graph.php?type=tinymon&id=$mon_id'></a>";
		}
		else
		{
			$graph = "Not Graphed";
		} // end if data type

		if ((!isset($mon_row['delta_time'])) || ($mon_row["delta_time"] == 0) || !isset($mon_row['last_val']))
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
		else
		{
			$mon_row['last_val'] = sanitize_number($mon_row['last_val']);
		}
		
		if (!isset($mon_row['last_time']))
		{
			$mon_row['last_time'] = "";
		}

		$data = '<table border="0" cellpadding="2" cellspacing="2" align="left" width="100%" height="100%">
			<tr>
				<td bgcolor="#eeeeee" width="50%">Value</td>
				<td>' . $mon_row["last_val"] . '</td>
			</tr>
			<tr>
				<td bgcolor="#eeeeee">Rate of Change</td>
				<td>'. $rate_of_change .'</td>
			</tr>
			<tr>
				<td bgcolor="#eeeeee">Time Stamp</td>
				<td>'. $mon_row["last_time"] .'</td></tr>
			</table>';

		$html_name = htmlspecialchars($mon_row['short_name']);
		$java_name = addslashes($html_name);

		make_display_item("editfield".($mon_count%2),
			array("checkboxname" => "monitor", "checkboxid" => $mon_row['id']),
			array("text" => $html_name, "href" => "events.php?mon_id={$mon_row['id']}&tripid={$_REQUEST['tripid']}"),
			array("text" => $data),
			array("text" => $graph),
			array("text" => formatted_link("Duplicate", "{$_SERVER['PHP_SELF']}?action=duplicate&mon_id=$mon_id&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}", "", "duplicate") . "&nbsp;" . 
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&mon_id=$mon_id&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}", "", "edit") . "&nbsp;" .
				formatted_link("Delete","javascript:del('$java_name', '$mon_id')", "", "delete"))
		); // end make_display_item();
		
		$mon_count++;

	} // end for each monitor
	make_checkbox_command("", 5,
		array("text" => "Duplicate", "action" => "multiduplicate"),
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked monitors?")
	); // end make_checkbox_command
	make_status_line("monitor", $mon_count);
	?>
	</table>
	</form>
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

	make_edit_select_test($mon_row['test_type'], $mon_row['test_id'], $mon_row['test_params']);	
	make_edit_group("Graphing Options");
	make_edit_select_from_table("Data Type:", "data_type", "data_types", $mon_row["data_type"]);
	make_edit_text("Minimum Value:", "min_val", "10", "20", $mon_row["min_val"]);
	make_edit_text("Maximum Value:", "max_val", "10", "20", $mon_row["max_val"]);
	make_edit_label('[<a href="javascript:make_min_undefined();">make minimum undefined</a>]
		[<a href="javascript:make_max_undefined();">make maximum undefined</a>]');
	
	make_edit_group("Ownership");
	if ($_REQUEST["edit_subdevice"] == 1)
	{
		make_edit_select_subdevice($_REQUEST["sub_dev_id"]);
	}
	else
	{
		$label = "<big><b>Subdevice:</b><br>  ";
		$label .= get_dev_sub_device_name($_REQUEST['sub_dev_id']);
		$label .= "  [<a href='{$_SERVER['PHP_SELF']}?action={$_REQUEST['action']}&mon_id={$_REQUEST['mon_id']}&edit_subdevice=1&sub_dev_id={$_REQUEST['sub_dev_id']}&tripid={$_REQUEST['tripid']}'>change</a>]</big>";
		make_edit_label($label);
		make_edit_hidden("subdev_id[]", $_REQUEST["sub_dev_id"]);
	}
	
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
		sub_dev_id='{$_REQUEST['subdev_id'][0]}',
		test_type='{$_REQUEST['test_type']}',
		test_id='{$_REQUEST['test_id']}',
		test_params='" . $_REQUEST['test_params'] ."',
		data_type='{$_REQUEST['data_type']}',
		min_val={$_REQUEST['min_val']},
		max_val={$_REQUEST['max_val']},
		tuned=0 $db_end");
	
} // end do_edit()

?>
