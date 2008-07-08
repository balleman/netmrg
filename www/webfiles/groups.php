<?php
/********************************************
* NetMRG Integrator
*
* groups.php
* Monitored Device Groups Editing Page
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
check_auth($GLOBALS['PERMIT']["ReadWrite"]);

// if no action, set a default one
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action


// what to do
switch ($_REQUEST["action"])
{
	case "edit" :
		edit();
		break;
		
	case "add"  :
		add();
		break;
		
	case "update" :
		update_group($_REQUEST["grp_id"], $_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["edit_parent_id"]);
		display();
		break;
		
	case "insert" :
		create_group($_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["edit_parent_id"]);
		display();
		break;
		
	case "delete" :
		delete_group($_REQUEST["grp_id"]);
		display();
		break;
		
	case "deletemulti" :
		if (isset($_REQUEST["grp_id"]))
		{
			foreach ($_REQUEST["grp_id"] as $key => $val)
			{
				delete_group($key);
			} // end foreach group, delete
		}
		display();
} // end what to do



/***** FUNCTIONS *****/
function edit()
{
	// Display editing screen
	begin_page("groups.php", "Groups");
	
	$grp_id = $_REQUEST["grp_id"];
	$grp_results = db_query("SELECT * FROM groups WHERE id=$grp_id");
	$grp_row = db_fetch_array($grp_results);
	$grp_name = $grp_row["name"];
	$grp_comment = $grp_row["comment"];
	
	make_edit_table("Edit Group");
	make_edit_text("Name:","grp_name","25","100",$grp_name);
	make_edit_text("Comment:","grp_comment","50","200",$grp_comment);
	make_edit_select_from_table("Parent:", "edit_parent_id", "groups", $grp_row["parent_id"], "", array(0 => "-Root-"), array(), "id != '$grp_id'");
	make_edit_hidden("grp_id", $grp_id);
	make_edit_hidden("action","update");
	make_edit_hidden("parent_id",$_REQUEST["parent_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
} // end edit();


function add()
{
	// Display editing screen
	begin_page("groups.php", "Groups");
	
	make_edit_table("Edit Group");
	make_edit_text("Name:","grp_name","25","100","");
	make_edit_text("Comment:","grp_comment","50","200","");
	make_edit_select_from_table("Parent:", "edit_parent_id", "groups", $_REQUEST["parent_id"], "", array(0 => "-Root-"), array(), "id != '-1'");
	make_edit_hidden("grp_id", -1);
	make_edit_hidden("action","insert");
	make_edit_hidden("parent_id",$_REQUEST["parent_id"]);
	make_edit_hidden("tripid",$_REQUEST["tripid"]);
	make_edit_submit_button();
	make_edit_end();
	end_page();
} // end add();


function display()
{
	header("Location: grpdev_list.php?parent_id={$_REQUEST['parent_id']}&tripid={$_REQUEST['tripid']}");
	exit();
} // end display();


?>
