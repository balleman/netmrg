<?php
/********************************************
* NetMRG Integrator
*
* sub_devices.php
* Sub-Devices Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

// what to do
switch ($_REQUEST["action"])
{
	case "doedit":
		doedit();
		break;
	
	case "multidodelete":
	case "dodelete":
		dodelete();
		break;
		
	case "add":
	case "edit":
		displayedit();
		break;
	
	case "multiduplicate":
	case "duplicate":
		doduplicate();
		break;
	
	default:
		dodisplay();
		break;
}



/***** FUNCTIONS *****/
function dodisplay()
{
	// Display the list of sub-devices for a particular device.

	check_auth($PERMIT["ReadAll"]);
	begin_page("sub_devices.php", "Sub Device");
	js_checkbox_utils();
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" name="form">
	<input type="hidden" name="action" value="">
	<input type="hidden" name="dev_id" value="<?php echo $_REQUEST['dev_id']; ?>">
	<input type="hidden" name="tripid" value="<?php echo $_REQUEST['tripid']; ?>">
	<?php
	PrepGroupNavHistory("device", $_REQUEST["dev_id"]);
	DrawGroupNavHistory("device", $_REQUEST["dev_id"]);
	js_confirm_dialog("del", "Are you sure you want to delete subdevice ", " and all associated items?", "{$_SERVER['PHP_SELF']}?action=dodelete&dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}&sub_dev_id=");

	$results = db_query("SELECT name, id, type FROM sub_devices WHERE sub_devices.dev_id={$_REQUEST['dev_id']}");
	$rows = array();
	while ($row = db_fetch_array($results))
	{
		array_push($rows, $row);
	}
	
	function mysort($a, $b)
	{
		return compare_interface_names($a['name'], $b['name']);
	}
	
	usort($rows, mysort);
	
	make_display_table("Sub-Devices for " . get_device_name($_REQUEST["dev_id"]),
		"{$_SERVER['PHP_SELF']}?action=add&dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}",
		array("text" => checkbox_toolbar()),
		array("text" => "Sub-Devices"),
		array("text" => "Type")
	); // end make_display_table();

	GLOBAL $SUB_DEVICE_TYPES;

	for ($i = 0; $i < db_num_rows($results); $i++)
	{
		$row = $rows[$i];
		make_display_item("editfield".($i%2),
			array("checkboxname" => "subdevice", "checkboxid" => $row['id']),
			array("text" => $row["name"], "href" => "monitors.php?sub_dev_id={$row['id']}&tripid={$_REQUEST['tripid']}"),
			array("text" => $SUB_DEVICE_TYPES[$row["type"]]),
			array("text" =>
				formatted_link("Add Templates", "graphs.php?action=applytemplates&sub_dev_id={$row['id']}", "", "applytemplate") . "&nbsp;" .
				formatted_link("Parameters", "sub_dev_param.php?dev_id={$_REQUEST['dev_id']}&sub_dev_id={$row['id']}&tripid={$_REQUEST['tripid']}", "", "parameters") . "&nbsp;" .
				formatted_link("View", "view.php?action=view&object_type=subdevice&object_id={$row['id']}", "", "view") . "&nbsp;" .
				formatted_link("Duplicate", "{$_SERVER['PHP_SELF']}?action=duplicate&dev_id={$_REQUEST['dev_id']}&sub_dev_id={$row['id']}&tripid={$_REQUEST['tripid']}", "", "duplicate") . "&nbsp;" .
				formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&dev_id={$_REQUEST['dev_id']}&sub_dev_id={$row['id']}&tripid={$_REQUEST['tripid']}", "", "edit") . "&nbsp;" .
				formatted_link("Delete", "javascript:del('".addslashes($row['name'])."','{$row['id']}')", "", "delete"))
		); // end make_display_item();
	}
	make_checkbox_command("", 5,
		array("text" => "Duplicate", "action" => "multiduplicate"),
		array("text" => "Delete", "action" => "multidodelete", "prompt" => "Are you sure you want to delete the checked sub-devices?")
	); // end make_checkbox_command
	make_status_line("sub-device", $i);
	?>
	</table>
	</form>
	<?php
	end_page();
} // end display();


function doedit()
{
	if ($_REQUEST["sub_dev_id"] == 0)
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE id={$_REQUEST['sub_dev_id']}";
	}

	db_update("$db_cmd sub_devices SET
		name='{$_REQUEST['name']}',
		type='{$_REQUEST['type']}',
		dev_id='{$_REQUEST['edit_dev_id']}'
		$db_end");

	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}");
} // end doedit();


function dodelete()
{
	check_auth($PERMIT["ReadWrite"]);
	if (isset($_REQUEST['subdevice']))
	{
		while (list($key,$value) = each($_REQUEST["subdevice"]))
		{
			delete_subdevice($key);
		}
	}
	else
	{
		delete_subdevice($_REQUEST["sub_dev_id"]);
	}
	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end dodelete();


function doduplicate()
{
	check_auth($PERMIT["ReadWrite"]);
	if (isset($_REQUEST['subdevice']))
	{
		while (list($key,$value) = each($_REQUEST["subdevice"]))
		{
			duplicate_subdevice($key);
		}
	}
	else
	{
		duplicate_subdevice($_REQUEST['sub_dev_id']);
	}
	
	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end doduplicate();


function displayedit()
{
	check_auth($PERMIT["ReadWrite"]);
	begin_page("sub_devices.php", "Add/Edit Sub Device");

	if ($_REQUEST["action"] == "add")
	{
		$_REQUEST["sub_dev_id"] = 0;
		$row = array();
		$row["name"] = "";
		$row["type"] = "";
	} // end if add

	if ($_REQUEST["sub_dev_id"] > 0)
	{
		$query = db_query("SELECT * FROM sub_devices WHERE id = {$_REQUEST['sub_dev_id']}");
		$row   = db_fetch_array($query);
	}

	make_edit_table("Sub-Device Properties");
	make_edit_text("Name:", "name", 40, 80, $row["name"]);
	make_edit_select_from_array("Sub-Device Type:", "type", $GLOBALS['SUB_DEVICE_TYPES'], $row["type"]);
	make_edit_select_from_table("Device:", "edit_dev_id", "devices", $_REQUEST["dev_id"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id", $_REQUEST["sub_dev_id"]);
	make_edit_hidden("dev_id", $_REQUEST["dev_id"]);
	make_edit_hidden("tripid", $_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

} // end displayedit();


?>
