<?php
/********************************************
* NetMRG Integrator
*
* groups.php
* Monitored Device Groups Editing Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($PERMIT["ReadAll"]);

// if no action, set a default one
if (empty($_REQUEST["action"]))
{
	$_REQUEST["action"] = "list";
} // end if no action


// what to do
switch ($_REQUEST["action"])
{
	case "edit" :
		check_auth($PERMIT["ReadWrite"]);
		edit();
		break;
		
	case "add"  :
		check_auth($PERMIT["ReadWrite"]);
		add();
		break;
		
	case "update" :
		check_auth($PERMIT["ReadWrite"]);
		update_group($_REQUEST["grp_id"], $_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["parent_id"]);
		display();
		break;
		
	case "insert" :
		check_auth($PERMIT["ReadWrite"]);
		create_group($_REQUEST["grp_name"], $_REQUEST["grp_comment"], $_REQUEST["parent_id"]);
		display();
		break;
		
	case "delete" :
		check_auth($PERMIT["ReadWrite"]);
		delete_group($_REQUEST["grp_id"]);
		display();
		break;
		
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
	make_edit_select_from_table("Parent:", "parent_id", "groups", $grp_row["parent_id"], "", array(0 => "-Root-"), array(), "id != '$grp_id'");
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
	make_edit_select_from_table("Parent:", "parent_id", "groups", $_REQUEST["parent_id"], "", array(0 => "-Root-"), array(), "id != '-1'");
	make_edit_hidden("grp_id", -1);
	make_edit_hidden("action","insert");
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
