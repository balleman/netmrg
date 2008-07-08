<?php
/********************************************
* NetMRG Integrator
*
* user_prefs.php
* User Preferences Page
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
check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

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
	&& $_REQUEST["uid"] !== false 
	&& GetUserID() != $_REQUEST["uid"])
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
	exit(0);
} // end if not user to edit or admin

// check that user is not the default map user
if ($_SESSION["netmrgsess"]["username"] == $GLOBALS["netmrg"]["defaultMapUser"])
{
	header("Location: {$GLOBALS['netmrg']['webroot']}/error.php?action=denied");
	exit(0);
} // end if default map user


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
	if (!$GLOBALS["netmrg"]["externalAuth"])
	{
		make_edit_section('Password');
		make_edit_password("Password:", "password", "25", "50", "");
		make_edit_password("Verify Password:", "vpassword", "25", "50", "");
	} // end if no external auth, show password dialog

	// slide show
	make_edit_section('Slide Show');
	make_edit_checkbox("Auto Scroll", "ss_auto_scroll", GetUserPref($_REQUEST["uid"], "SlideShow", "AutoScroll"));

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
	SetUserPref($_REQUEST["uid"], "SlideShow", "AutoScroll", !empty($_REQUEST["ss_auto_scroll"]));
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
