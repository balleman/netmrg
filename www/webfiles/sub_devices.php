<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Sub-Devices Editing Page                   #
#           sub_devices.php                            #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");

if (!isset($_REQUEST["action"]))
{
        // Display the list of sub-devices for a particular device.

	check_auth(1);
        begin_page();

	$results = do_query("SELECT sub_devices.name, sub_devices.id FROM sub_devices WHERE sub_devices.dev_id={$_REQUEST['dev_id']}");

	$custom_add_link = "{$_SERVER['PHP_SELF']}?action=add&dev_id={$_REQUEST['dev_id']}";
	make_display_table("Sub-Devices for " . get_device_name($_REQUEST["dev_id"]), "Sub-Devices", "");

	for ($i = 0; $i < mysql_num_rows($results); $i++)
	{
		$row = mysql_fetch_array($results);
		make_display_item($row["name"], "./monitors.php?sub_dev_id=" . $row["id"], formatted_link("Parameters", "sub_dev_param.php?dev_id={$_REQUEST['dev_id']}&sub_dev_id=" . $row["id"]) . "&nbsp;" .formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&dev_id={$_REQUEST['dev_id']}&sub_dev_id=" . $row["id"]) . "&nbsp;" . formatted_link("Delete", ""), "");
	}

	?></table><?

	end_page();
}

if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "doedit")
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

	do_update("$db_cmd sub_devices SET
		name='{$_REQUEST['name']}',
		type='{$_REQUEST['type']}',
		dev_id='{$_REQUEST['dev_id']}'
		$db_end");

	header("Location: {$_SERVER['PHP_SELF']}?dev_id={$_REQUEST['dev_id']}");
}

if (!empty($_REQUEST["action"]) && ($_REQUEST["action"] == "edit" || $_REQUEST["action"] == "add"))
{
	check_auth(2);
	begin_page();

	if ($_REQUEST["action"] == "add")
	{
		$_REQUEST["sub_dev_id"] = 0;
		$row = array();
		$row["name"] = "";
		$row["type"] = "";
	} // end if add

	if ($_REQUEST["sub_dev_id"] > 0)
	{
		$query = do_query("SELECT * FROM sub_devices WHERE id = {$_REQUEST['sub_dev_id']}");
		$row   = mysql_fetch_array($query);
	}

	make_edit_table("Sub-Device Properties");
	make_edit_text("Name:", "name", 40, 80, $row["name"]);
	make_edit_select_from_table("Sub-Device Type:", "type", "sub_dev_types", $row["type"]);
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id", $_REQUEST["sub_dev_id"]);
	make_edit_hidden("dev_id", $_REQUEST["dev_id"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

}


?>
