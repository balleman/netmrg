<?php
/********************************************
* NetMRG Integrator
*
* user_prefs.php
* User Preferences Page
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

// check default action
if (empty($_REQUEST['action']))
{
	$_REQUEST["action"] = "edit";
} // end if no action

// check that user id is set
if (empty($_REQUEST["uid"]))
{
	$_REQUEST["uid"] = GetUserID();
} // end if user id isn't set

// check that user is the same as the one they want to edit
// or we're an admin
if ($_SESSION["netmrgsess"]["permit"] != 3
	&& GetUserID() != $_REQUEST["uid"])
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
	exit(0);
} // end if not user to edit or admin


// check what to do
switch ($_REQUEST['action'])
{
	case "update":
		update($_REQUEST["uid"]);
		break;

	case "edit":
	default:
		edit($_REQUEST["uid"]);
		break;
} // end switch action



/***** FUNCTIONS *****/

/**
* edit($uid)
*
* edits a user's preferences
*/
function edit($uid)
{
	$username = GetUsername($uid);

	begin_page("user_prefs.php", "User Preferences ($username)");

	make_edit_table("Edit Preferences for $username");
	make_edit_hidden("action", "update");
	make_edit_hidden("uid", $uid);

	// edit password
	make_edit_section('Password');
	make_edit_password("Password:", "password", "25", "50", "");
	make_edit_password("Verify Password:", "vpassword", "25", "50", "");

	// slide show
	make_edit_section('Slide Show');
	make_edit_checkbox("Auto Scroll", "ss_auto_scroll", GetUserPref("SlideShow", "AutoScroll"));

	make_edit_submit_button();
	make_edit_end();

	end_page();
} // end edit();


/**
* update($uid)
*
* update's a user's info
*/
function update($uid)
{
	$username = GetUsername($uid);

	begin_page("user_prefs.php", "User Preferences ($username)");

	// array of error messages
	$errors = array();
	// array of results
	$results = array();

	// if password
	if (!empty($_REQUEST["password"]))
	{
		if ($_REQUEST["password"] != $_REQUEST["vpassword"])
		{
			array_push($errors, "Your passwords do not match");
		} // end if passwords don't match
	} // end if ! password

	// if there were errors, display them and quit
	if (count($errors) != 0)
	{
		DisplayErrors($errors);
		return;
	} // end if errors

	// update password
	if (!empty($_REQUEST["password"]))
	{
		db_query("UPDATE user SET pass = md5('{$_REQUEST['password']}')
			WHERE id = '$uid'");
		array_push($results, "Password updated successfully.");
	} // end if ! password

	// update slide show auto scroll
	SetUserPref("SlideShow", "AutoScroll", !empty($_REQUEST["ss_auto_scroll"]));
	array_push($results, "Slide Show Auto Scroll was set to ".(!empty($_REQUEST["ss_auto_scroll"]) ? "true" : "false"));

	// print results
	if (count($results) == 0)
	{
		array_push($results, "Nothing was modified");
	} // end if no results
	DisplayResults($results);

	end_page();
} // end update();


?>
