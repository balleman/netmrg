<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           A Page of Graphs associated with a         #
#           group or device.			       #
#           view.php		     		       #
#                                                      #
#                                                      #
#                                                      #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
require_once("/var/www/netmrg/lib/stat.php");
require_once(netmrg_root() . "lib/format.php");
require_once(netmrg_root() . "lib/graphing.php");
require_once(netmrg_root() . "lib/auth.php");
view_check_auth();
refresh_tag();
begin_page();

if (!empty($_REQUEST["full_edit"])) {
	$full_edit = $_REQUEST["full_edit"];
} else {
	$full_edit = 0;
} // end if full_edit was set or not

if (!empty($_REQUEST["action"])) {
	if ($_REQUEST["action"] == "doadd")
	{
		do_update("INSERT INTO view SET 
			pos_id=".$_REQUEST["pos_id"].", 
			pos_id_type=".$_REQUEST["pos_id_type"].", 
			graph_id=".$_REQUEST["graph_id"].", 
			graph_id_type=\"custom\"");
		unset($action);
		$full_edit = 1;

	} else if ($_REQUEST["action"] == "dodelete") {
		do_update("DELETE FROM view 
			WHERE pos_id=".$_REQUEST["pos_id"]." 
			AND pos_id_type=".$_REQUEST["pos_id_type"]." 
			AND graph_id=".$_REQUEST["graph_id"]);
		unset($action);
		$full_edit = 1;

	} else if ($_REQUEST["action"] == "move") {
		// do moving and stuff (... and things)
		do_update("UPDATE view SET pos=".$_REQUEST["val"]." 
			WHERE pos_id=".$_REQUEST["pos_id"]." 
			AND pos_id_type=".$_REQUEST["pos_id_type"]." 
			AND graph_id=".$_REQUEST["graph_id"]);
		unset($action);

	} // end if we have an action to perform

} // end if an action was defined

$view_select = "
    SELECT * FROM view
    LEFT JOIN graphs ON view.graph_id=graphs.id
    WHERE pos_id_type=".$_REQUEST["pos_id_type"]."
    AND pos_id=".$_REQUEST["pos_id"]."
    ORDER BY pos";
$view_result = do_query($view_select);
$num = mysql_num_rows($view_result);

if (!isset($_REQUEST["action"]))
{
	if ($full_edit != 1)
	{

		print("<div align=\"center\">");

		for ($i = 0; $i < $num; $i++)
		{
			$row = mysql_fetch_array($view_result);

			if ($row["graph_id_type"] == 0)
			{
				print("<a href=\"./enclose_graph.php?type=" . $row["graph_id_type"] . "&id=" . $row["graph_id"] . "\">" .
					"<img border=\"0\" src=\"./get_graph.php?type=" . $row["graph_id_type"] . "&id=" . $row["graph_id"] . "\"></a><br>");
			}

			if ($row["graph_id_type"] == 10)
			{
				print("<table width='100%' bgcolor='" . $row["separator_color"] . "'><tr><td><b><font color='AAAAAA'>" . $row["separator_text"] . "</font></b></td></tr></table>");
			}

		}

		print("</div>");

		if (get_permit() > 1)
		{
			print("<a href='./view.php?pos_id_type=".$_REQUEST["pos_id_type"]."&pos_id=".$_REQUEST["pos_id"]."&full_edit=1'>[Edit]</a>");
		}

	}
	else
	{
		$custom_add_link = "./view.php?pos_id_type=".$_REQUEST["pos_id_type"]."&pos_id=".$_REQUEST["pos_id"]."&action=add";
		make_display_table("Edit View","Graph","");

		for ($i = 0; $i < $num; $i++)
		{
			$row = mysql_fetch_array($view_result);
			make_display_item($row["name"],"",
				formatted_link("Move Up", "$SCRIPT_NAME?action=move&val=" . ($row["pos"] - 1) . "&pos_id=" . $row["pos_id"] . "&pos_id_type=" . $row["pos_id_type"] . "&graph_id=" . $row["graph_id"] . "&full_edit=1") . "&nbsp;" .
				formatted_link("Move Down", "$SCRIPT_NAME?action=move&val=" . ($row["pos"] + 1) . "&pos_id=" . $row["pos_id"] . "&pos_id_type=" . $row["pos_id_type"] . "&graph_id=" . $row["graph_id"] . "&full_edit=1") . "&nbsp;" .
				formatted_link("Delete","./view.php?action=delete&pos_id_type=$pos_id_type&pos_id=$pos_id&graph_id=" . $row["graph_id"]), "");
		}

		print("</table><a href='./view.php?pos_id_type=".$_REQUEST["pos_id_type"]."&pos_id=".$_REQUEST["pos_id"]."&full_edit=0'>[Done Editing]</a>");

	}
}


if (!empty($_REQUEST["action"])) {
	if ($_REQUEST["action"] == "add")
	{
		make_edit_table("Add Graph to View");
		make_edit_select_from_table("Graph:","graph_id","graphs",0);
		make_edit_hidden("pos_id",$pos_id);
		make_edit_hidden("pos_id_type",$pos_id_type);
		make_edit_hidden("action","doadd");
		make_edit_submit_button();
		make_edit_end();
	} else if ($_REQUEST["action"] == "delete") {
		// Display delete confirmation
?>

	<font size="4" color="#800000">Confirm Delete</font><br><br>

	Are you sure you want to delete this graph from this view?

	<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
	<input type="submit" value="Yes">
	<input type="hidden" name="pos_id" value="<? print("$pos_id"); ?>">
	<input type="hidden" name="pos_id_type" value="<? print("$pos_id_type"); ?>">
	<input type="hidden" name="graph_id" value="<? print("$graph_id"); ?>">
	<input type="hidden" name="action" value="dodelete">
	</form>
	<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
	<input type="submit" value="No">
	</form>

<?
	} // end if action is to do something
} // end if action is defined


end_page();

?>
