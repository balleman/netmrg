<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Sub-Devices Parameters Page                #
#           sub_dev_param.php                          #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");

if (!isset($action))
{
        // Display the list of sub-devices for a particular device.

	check_auth(1);
        begin_page();

	$results = do_query("SELECT name, value FROM sub_dev_variables WHERE sub_dev_id=$sub_dev_id");

	$custom_add_link = "$SCRIPT_NAME?action=add&sub_dev_id=$sub_dev_id";
	make_display_table("Parameters for " . get_sub_device_name($sub_dev_id), "Name", "", "Value", "");

	for ($i = 0; $i < mysql_num_rows($results); $i++)
	{
		$row = mysql_fetch_array($results);
		make_display_item($row["name"], "", $row["value"], "", formatted_link("Edit", "$SCRIPT_NAME?action=edit&sub_dev_id=$sub_dev_id&name=" . $row["name"]) . "&nbsp;" . formatted_link("Delete", ""), "");
	}

	?></table><?

	end_page();
}

if ($action == "doedit")
{
        if ($type == "add") 
	{
		$db_cmd = "INSERT INTO";
		$db_end = "";
	}
	else
	{
		$db_cmd = "UPDATE";
		$db_end = "WHERE name=\"$name\" AND sub_dev_id=$sub_dev_id";
	}

	do_update("$db_cmd sub_dev_variables SET
                        name=\"$name\",
			value=\"$value\",
			sub_dev_id=$sub_dev_id
			$db_end");

        header("Location: " . $SCRIPT_NAME . "?sub_dev_id=$sub_dev_id");
}

if (($action == "edit") || ($action == "add"))
{
	check_auth(2);
	begin_page();

	if ($sub_dev_id > 0)
	{
		$query = do_query("SELECT * FROM sub_dev_variables WHERE sub_dev_id = $sub_dev_id AND name = \"$name\"");
		if (mysql_num_rows($query) > 0)
		{
			$row   = mysql_fetch_array($query);
		}
	}

	make_edit_table("Sub-Device Parameter");
        make_edit_text("Name:", name, 40, 80, $row["name"]);
	make_edit_text("Value:", value, 40, 80, $row["value"]);
	if ($row["name"] == "")
	{
	        make_edit_hidden("type", "add");
	}
	make_edit_hidden("action","doedit");
	make_edit_hidden("sub_dev_id",$sub_dev_id);
	make_edit_submit_button();
	make_edit_end();
	end_page();

}


?>
