<?php
/********************************************
* NetMRG Integrator
*
* sub_dev_param.php
* Sub-Devices Parameters Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");

if (empty($_REQUEST["action"]))
{
	// Display the list of sub-devices for a particular device.

	check_auth(1);
	begin_page("sub_dev_param.php", "Sub Device Parameters);

	$results = do_query("SELECT name, value FROM sub_dev_variables WHERE sub_dev_id={$_REQUEST['sub_dev_id']}");

	$custom_add_link = "{$_SERVER['PHP_SELF']}?action=add&sub_dev_id={$_REQUEST['sub_dev_id']}";
	make_display_table("Parameters for " . get_sub_device_name($_REQUEST["sub_dev_id"]), "Name", "", "Value", "");

	for ($i = 0; $i < mysql_num_rows($results); $i++)
	{
		$row = mysql_fetch_array($results);
		make_display_item($row["name"], "", $row["value"], "", formatted_link("Edit", "{$_SERVER['PHP_SELF']}?action=edit&sub_dev_id={$_REQUEST['sub_dev_id']}&name=" . $row["name"]) . "&nbsp;" . formatted_link("Delete", ""), "");
	}

	?></table><?php

	end_page();
}

elseif ($_REQUEST["action"] == "doedit")
{
        if ($_REQUEST["type"] == "add")
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE name=\"{$_REQUEST['name']}\" AND sub_dev_id={$_REQUEST['sub_dev_id']}";
	}

	do_update("$db_cmd sub_dev_variables SET
                        name=\"{$_REQUEST['name']}\",
			value=\"{$_REQUEST['value']}\",
			sub_dev_id={$_REQUEST['sub_dev_id']}
			$db_end");

        header("Location: " . $_SERVER["PHP_SELF"] . "?sub_dev_id={$_REQUEST['sub_dev_id']}");
}

elseif (($_REQUEST["action"] == "edit") || ($_REQUEST["action"] == "add"))
{
	check_auth(2);
	begin_page("sub_dev_param.php", "Add/Edit Sub Device Parameter");

	if ($_REQUEST["action"] == "edit")
	{
		$query = do_query("SELECT * FROM sub_dev_variables WHERE sub_dev_id = {$_REQUEST['sub_dev_id']} AND name = \"{$_REQUEST['name']}\"");
		if (mysql_num_rows($query) > 0)
		{
			$row   = mysql_fetch_array($query);
		}
	}
	else
	{
		$row["name"] = "";
		$row["value"] = "";
	}

	make_edit_table("Sub-Device Parameter");
        make_edit_text("Name:", "name", 40, 80, $row["name"]);
	make_edit_text("Value:", "value", 40, 80, $row["value"]);
	if ($row["name"] == "")
	{
	        make_edit_hidden("type", "add");
	}
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id",$_REQUEST["sub_dev_id"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();

}


?>
