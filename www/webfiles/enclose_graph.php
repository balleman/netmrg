<?php
/********************************************
* NetMRG Integrator
*
* enclose_graph.php
* Enclose Graph in a Page
*
* named in honor of Ian Berry's
*   "enclose_url.php"
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
// we need to auth different ways depending on type of graph
switch($_REQUEST["type"])
{
	case "template" :
		EncloseGraphCheckAuth($_REQUEST["type"], $_REQUEST["subdev_id"]);
		break;
	
	default :
		EncloseGraphCheckAuth($_REQUEST["type"], $_REQUEST["id"]);
		break;
} // end switch type for auth

if (!isset($_REQUEST['action']))
{
	$_REQUEST['action'] = "";
}

begin_page("enclose_graph.php", "Graph", 1);

switch ($_REQUEST['action'])
{
	case 'dissect':     show_dissection();  break;
	case 'history':     show_history();     break;
	case 'advanced':    show_advanced();    break;
	default:            show();
}

function template()
{
	if ($_REQUEST['type'] == "template")
	{
		return "subdev_id={$_REQUEST['subdev_id']}&";
	}
	else
	{
		return "";
	}
}

function show()
{
	$opts = template() . "type={$_REQUEST['type']}&id={$_REQUEST['id']}";
	
	echo('<div align="center">');
	echo('<img src="get_graph.php?' . $opts . '"></div><br>');
	echo('<a href="enclose_graph.php?' . $opts . '&action=history">Show History</a><br>');
	if ($_REQUEST['type'] == 'template' || $_REQUEST['type'] == 'custom')
	{
		echo('<a href="enclose_graph.php?' . $opts . '&action=dissect">Dissect</a><br>');
	}
	echo('<a href="enclose_graph.php?' . $opts . '&action=advanced">Advanced</a><br>');
}

function show_history()
{
	$opts = template() . "type={$_REQUEST['type']}&id={$_REQUEST['id']}";

	echo('<div align="center">');
	echo('<img src="get_graph.php?' . $opts . '&hist=0"><br>');
	echo('<img src="get_graph.php?' . $opts . '&hist=1"><br>');
	echo('<img src="get_graph.php?' . $opts . '&hist=2"><br>');
	echo('<img src="get_graph.php?' . $opts . '&hist=3"><br>');
	echo('</div><br>');
	echo('<a href="enclose_graph.php?' . $opts . '&action=show">Normal View</a><br>');
}

function show_dissection()
{
	$opts = template() . "type={$_REQUEST['type']}&id={$_REQUEST['id']}";
	$dbq = db_query("SELECT id FROM graph_ds WHERE graph_id = '{$_REQUEST['id']}' AND mon_id != -2 ORDER BY position, id");
	echo('<div align="center">');
	while ($dbr = mysql_fetch_array($dbq))
	{
		echo('<img src="get_graph.php?' . template() . 'type=' . $_REQUEST['type'] . '_item&id=' . $dbr['id'] . '"><br>');
	}
	echo('</div><br>');
	echo('<a href="enclose_graph.php?' . $opts . '&action=show">Normal View</a><br>');
}

function show_advanced()
{
	if (!isset($_REQUEST['start']))
	{
		$_REQUEST['start'] = "+yesterday";
	}
	if (!isset($_REQUEST['end']))
	{
		$_REQUEST['end'] = "+5 minutes ago";
	}
	if (!isset($_REQUEST['min']))
	{
		$_REQUEST['min'] = "0";
	}
	if (!isset($_REQUEST['max']))
	{
		$_REQUEST['max'] = "0";
	}

	$opts = template() . "type={$_REQUEST['type']}&id={$_REQUEST['id']}&start={$_REQUEST['start']}&";
	$opts .= "end={$_REQUEST['end']}&min={$_REQUEST['min']}&max={$_REQUEST['max']}";

	echo('<div align="center">');
	echo('<img src="get_graph.php?' . $opts . '"></div><br>');

	echo('<form action="enclose_graph.php" method="get">');
	make_edit_hidden("action", "advanced");
	make_edit_hidden("type", $_REQUEST['type']);
	make_edit_hidden("id", $_REQUEST['id']);
	if ($_REQUEST['type'] == "template")
	{
		make_edit_hidden("subdev_id", $_REQUEST['subdev_id']);
	}

	echo('Start: <input type="text" name="start" value="' . $_REQUEST['start'] . '" size="20">&nbsp;');
	echo('End: <input type="text" name="end" value="' . $_REQUEST['end'] . '" size="20">&nbsp;');
	echo('Max: <input type="text" name="max" value="' . $_REQUEST['max'] . '" size="8">&nbsp;');
	echo('Min: <input type="text" name="min" value="' . $_REQUEST['min'] . '" size="8">&nbsp;&nbsp;');
	echo('<input type="submit" name="submit" value="Render"><br>');
	echo('</form>');
	echo('<a href="enclose_graph.php?' . $opts . '&action=show">Normal View</a><br>');
}

end_page();

?>
