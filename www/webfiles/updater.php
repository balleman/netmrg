<?php
/********************************************
* NetMRG Integrator
*
* updater.php
* Updates the current installation
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
check_auth($GLOBALS['PERMIT']["Admin"]);

/***** CONFIG *****/

// database updates that need done
//    "apply_to_ver" => array("ver" => array("name" => "Update Name", "query" => "SELECT 1;"));
$dbupdates = array(
	"0.10pre1" => array(), // end 0.10pre1
	
	"0.10pre2" => array(
		array(
			"name" => "Graph Options",
			"query" => "ALTER TABLE `graphs` ADD `options` SET( 'nolegend', 'logarithmic' ) NOT NULL ;"
			),
		array(
			"name" => "Graph Options2",
			"query" => "ALTER TABLE `graphs` ADD `base` INT DEFAULT '1000' NOT NULL ;"
			),
		array(
			"name" => "Graph Item Times",
			"query" => "ALTER TABLE `graph_ds` ADD `start_time` VARCHAR( 20 ) NOT NULL ;"
			),
		array(
			"name" => "Graph Item Times2",
			"query" => "ALTER TABLE `graph_ds` ADD `end_time` VARCHAR( 20 ) NOT NULL ;"
			),
		array(
			"name" => "Graph Title",
			"query" => "ALTER TABLE `graphs` ADD `title` VARCHAR( 100 ) NOT NULL AFTER `name`;"
			),
		array(
			"name" => "Graph Item Muliplier",
			"query" => "ALTER TABLE `graph_ds` CHANGE `multiplier` `multiplier` VARCHAR( 100 ) DEFAULT '1' NOT NULL;"
			),
		array(
			"name" => "SNMP ifSpeed",
			"query" => "ALTER TABLE snmp_interface_cache ADD COLUMN ifSpeed int(11) NOT NULL;"
			),
		array(
			"name" => "Graph Title Fill-in",
			"query" => "UPDATE graphs SET title = name WHERE title = '';"
			)
	), // end 0.10pre2
	
	"0.10" => array(), // end 0.10
	
	"0.12" => array(
		array(
			"name" => "SNMP Version Support",
			"query" => "ALTER TABLE `devices` CHANGE `snmp_enabled` `snmp_version` TINYINT( 4 ) DEFAULT '1' NOT NULL ;"
			),
		array(
			"name" => "SNMP Timeout",
			"query" => "ALTER TABLE `devices` ADD `snmp_timeout` INT UNSIGNED DEFAULT '1000000' NOT NULL AFTER `snmp_version`, ADD `snmp_retries` TINYINT UNSIGNED DEFAULT '3' NOT NULL AFTER `snmp_timeout`, ADD `snmp_port` SMALLINT UNSIGNED DEFAULT '161' NOT NULL AFTER `snmp_retries` ;"
			),
		array(
			"name" => "User Prefs",
			"query" => "CREATE TABLE user_prefs ( id INT NOT NULL AUTO_INCREMENT, uid INT NOT NULL, module VARCHAR(64) NOT NULL, pref VARCHAR(64) NOT NULL, value VARCHAR(64) NOT NULL, PRIMARY KEY (id), KEY uid (uid), KEY uid_module_pref (uid, module, pref));"
			),
		array(
			"name" => "User Prefs Slideshow Default",
			"query" => "INSERT INTO user_prefs (uid, module, pref, value) SELECT id, 'SlideShow', 'AutoScroll', 1 FROM user;"
			)
	), // end 0.12
	
	"0.13" => array(), // end 0.13
	
	"0.14" => array(
		array(
			"name" => "SNMP Recache Method",
			"query" => "ALTER TABLE `devices` ADD `snmp_recache_method` SMALLINT DEFAULT '0' NOT NULL AFTER `dev_type`;"
			),
		array(
			"name" => "SNMP Recache Method Default",
			"query" => "UPDATE devices SET snmp_recache_method = 4 WHERE snmp_recache = 1;"
			),
		array(
			"name" => "SNMP Recache Method Default2",
			"query" => "UPDATE devices SET snmp_recache_method = 3 WHERE snmp_recache = 0 AND snmp_check_ifnumber = 1;"
			),
		array(
			"name" => "SNMP Recache Method Default3",
			"query" => "UPDATE devices SET snmp_recache_method = 1 WHERE snmp_recache = 0 AND snmp_check_ifnumber = 0;"
			),
		array(
			"name" => "SNMP Recache Drop",
			"query" => "ALTER TABLE `devices` DROP `snmp_recache`;"
			),
		array(
			"name" => "SNMP ifNumber Drop",
			"query" => "ALTER TABLE `devices` DROP `snmp_check_ifnumber`;"
			),
		array(
			"name" => "Log Table",
			"query" => "CREATE TABLE `log` ( `id` BIGINT NOT NULL AUTO_INCREMENT, `date` DATETIME NOT NULL, `dev_id` INT, `subdev_id` INT, `mon_id` INT, `level` INT NOT NULL , `component` INT NOT NULL , `message` VARCHAR( 200 ) NOT NULL , PRIMARY KEY ( `id` ) , INDEX ( `date` ) , INDEX ( `dev_id` ), INDEX ( `subdev_id` ) , INDEX( `mon_id` ));"
			),
		array(
			"name" => "User Index Drop",
			"query" => "ALTER TABLE user DROP INDEX user;"
			),
		array(
			"name" => "User Unique Index",
			"query" => "ALTER TABLE user ADD CONSTRAINT UNIQUE user (user);"
			)
	), // end 0.14
	
	"0.15" => array(), // end 0.15
	
	"0.16" => array(
		array(
			"name" => "Device SNMP Uptime Check Option",
			"query" => "ALTER TABLE `devices` ADD `no_snmp_uptime_check` TINYINT DEFAULT '0' NOT NULL ;"
			),
		array(
			"name" => "SNMP Test Type",
			"query" => "ALTER TABLE `tests_snmp` ADD `type` TINYINT DEFAULT '0' NOT NULL , ADD `subitem` INT DEFAULT '0' NOT NULL ;"
			),
		array(
			"name" => "Graph Min/Max",
			"query" => "ALTER TABLE `graphs` ADD `max` INT, ADD `min` INT;"
			)
	), // end 0.16
	
	"0.17" => array(
		array(
			"name" => "Internal Test Name Lengthen",
			"query" => "ALTER TABLE `tests_internal` CHANGE `name` `name` VARCHAR( 200 ) NOT NULL ;"
			),
		array(
			"name" => "Script Test Name Lengthen",
			"query" => "ALTER TABLE `tests_script` CHANGE `name` `name` VARCHAR( 200 ) NOT NULL ;"
			),
		array(
			"name" => "SQL Test Name Lengthen",
			"query" => "ALTER TABLE `tests_sql` CHANGE `name` `name` VARCHAR( 200 ) NOT NULL ;"
			),
		array(
			"name" => "New Internal Test - Lucent TNT Good Modems",
			"query" => "INSERT INTO tests_internal VALUES (2,'Lucent TNT \"Good\" Modems (available modems minus suspect modems)');"
			),
		array(
			"name" => "New Internal Test - UCD CPU Load",
			"query" => "INSERT INTO tests_internal VALUES (3,'UCD CPU Load (user + system)');"
			),
		array(
			"name" => "New Internal Test - Windows Disk Usage",
			"query" => "INSERT INTO tests_internal VALUES (4,'Windows Disk Usage (percent)');"
			),
		array(
			"name" => "New Internal Test - UCD Swap Usage",
			"query" => "INSERT INTO tests_internal VALUES (5,'UCD Swap Usage (percent)');"
			),
		array(
			"name" => "New Internal Test - Read Value from File",
			"query" => "INSERT INTO tests_internal VALUES (6,'Read Value from File');"
			),
		array(
			"name" => "User Disabled Support",
			"query" => "ALTER TABLE `user` ADD `disabled` TINYINT DEFAULT '0' NOT NULL;"
			)
	), // end 0.17
	
	"0.18" => array(
		array(
			"name" => "Graph Multiply Sum Support",
			"query" => "ALTER TABLE `graph_ds` CHANGE `stats` `stats` SET( 'CURRENT', 'AVERAGE', 'MAXIMUM', 'SUMS', 'INTEGER', 'MULTSUM' ) DEFAULT 'CURRENT,AVERAGE,MAXIMUM' NOT NULL ;"
			),
		array(
			"name" => "New Internal Test - SNMP Failure",
			"query" => "INSERT INTO tests_internal VALUES (7,'SNMP Failure');"
			),
		array(
			"name" => "SQL Test Timeout",
			"query" => "ALTER TABLE `tests_sql` ADD `timeout` INT DEFAULT '10' NOT NULL ;"
			)
	), // end 0.18
	
	"0.18.1" => array(), // end 0.18.1

	"0.18.2" => array(), // end 0.18.2
	
	"0.19" => array(
		array(
			"name" => "Increase size of monitors.type_id",
			"query" => "ALTER TABLE monitors MODIFY test_id INT NOT NULL;"
			),
		array(
			"name" => "Increase size of view.pos",
			"query" => "ALTER TABLE view MODIFY pos INT NOT NULL;"
			),
		array(
			"name" => "Windows CPU Internal Test",
			"query" => "INSERT INTO tests_internal VALUES (8,'Windows CPU Load');"
			),
		array(
			"name" => "Livingston Portmaster Modems Script Test",
			"query" => "INSERT INTO tests_script SET name='Livingston Portmaster Active Modems', cmd='pmmodems.pl %snmp_read_community% %ip%', data_type='2'"),
		array(
			"name" => "Graph Min NULL Fix",
			"query" => "UPDATE graphs SET min=NULL WHERE min=0"),
		array(
			"name" => "Graph Max NULL Fix",
			"query" => "UPDATE graphs SET max=NULL WHERE max=0")
	), // end 0.19

	"0.19.1" => array(), // end 0.19

	"0.20" => array(
		array(
			"name" => "Device Properties Table",
			"query" => "CREATE TABLE `dev_props` (`id` INT NOT NULL AUTO_INCREMENT, `dev_type_id` INT NOT NULL, `name` VARCHAR( 200 ) NOT NULL, `test_type` TINYINT NOT NULL, `test_id` INT NOT NULL, `test_params` VARCHAR( 150 ) NOT NULL, PRIMARY KEY ( `id` )) TYPE = MYISAM ;"),
		array(
			"name" => "Device Properties Values Table",
			"query" => "CREATE TABLE `dev_prop_vals` ( `dev_id` INT NOT NULL, `prop_id` INT NOT NULL, `value` VARCHAR( 250 ) NOT NULL, PRIMARY KEY ( `dev_id` , `prop_id` )) "),
		array(
			"name" => "SNMP Interface Cache New Fields",
			"query" => "ALTER TABLE `snmp_interface_cache` ADD `nexthop` VARCHAR( 20 ) , ADD `vlan` VARCHAR( 20 ) , ADD `mode` TINYINT"),
		array(
			"name" => "Graph Item Consolidation Function Field",
			"query" => "ALTER TABLE `graph_ds` ADD `cf` TINYINT NOT NULL DEFAULT '1'"),
		array(
			"name" => "Device Unknowns on SNMP Restart Field",
			"query" => "ALTER TABLE `devices` ADD `unknowns_on_snmp_restart` TINYINT NOT NULL DEFAULT '1'")
	), // end 0.20

	"0.21" => array(
		array(
			"name" => "SNMPv3",
			"query" => "ALTER TABLE `devices` ADD `snmp3_user` VARCHAR( 200 ) NOT NULL , ADD `snmp3_seclev` TINYINT NOT NULL , ADD `snmp3_aprot` TINYINT NOT NULL , ADD `snmp3_apass` VARCHAR( 200 ) NOT NULL , ADD `snmp3_pprot` TINYINT NOT NULL , ADD `snmp3_ppass` VARCHAR( 200 ) NOT NULL"),
		array(
			"name" => "Minimum Graph Option",
			"query" => "ALTER TABLE `graph_ds` MODIFY `stats` set('CURRENT','AVERAGE','MAXIMUM','SUMS','INTEGER','MULTSUM', 'MINIMUM') NOT NULL DEFAULT 'CURRENT,AVERAGE,MAXIMUM'"),
	), // end 0.21
	
); // end $dbupdates;



/***** ACTIONS *****/
// check default action
if (empty($_REQUEST['action']))
{
	$_REQUEST["action"] = "prompt";
} // end if no action

// check what to do
switch ($_REQUEST['action'])
{
	case "viewupdates":
		Updater($dbupdates);
		break;
	
	case "performupdate":
		if (!isset($_REQUEST["force_update"]))
		{
			$_REQUEST["force_update"] = false;
		} // end if no force set
		if (!isset($_REQUEST["which_update"]))
		{
			$_REQUEST["which_update"] = "";
		} // end if no update selected
		if (!isset($_REQUEST["update_version"]))
		{
			$_REQUEST["update_version"] = "";
		} // end if no update_version
		Updater($dbupdates, $_REQUEST["update_version"], $_REQUEST["which_update"], $_REQUEST["force_update"]);
		break;
	
	case "prompt":
	default:
		prompt();
		break;
} // end switch action



/***** FUNCTIONS *****/

/**
* prompt()
*
* tells a user what we're about to do
*/
function prompt()
{
	begin_page("updater.php", "Updater");
	
	$dbver = $GLOBALS["netmrg"]["dbversion"];
	
	if ($dbver != $GLOBALS["netmrg"]["version"])
	{
?>
	<form name="form" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
	<input type="hidden" name="action" value="performupdate" />
	<input type="hidden" name="update_version" value="all" />
	<input type="hidden" name="which_update" value="all" />
	<input type="hidden" name="force_update" value="0" />
	</form>
<div class="update-text">
The current database needs to be updated from version
<b><?php echo $dbver; ?></b> to <b><?php echo $GLOBALS["netmrg"]["version"] ?></b><br />
<a href="#" onclick="document.form.submit();">[apply all updates]</a>
</div>
<?php
	} // end if database needs updated
	else
	{
?>
<div class="update-text">
Your database is already at the latest version,
<b><?php echo $dbver; ?></b>.  No upgrade is needed.
</div>
<?php
	} // end if no changes
	
	// prompt to view updates anyway
?>
<div class="update-text">
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=viewupdates">View all available updates</a>
</div>
<?php
	
	end_page();
} // end prompt();


/**
* Updater()
*
* shows and applies updates
*/
function Updater($dbupdates, $version = "", $which_update = "", $force = false)
{
	begin_page("updater.php", "Updater");
	
	$dbver = $GLOBALS["netmrg"]["dbversion"];
	
	// make sure we're good to run
	PrepUpdater($dbupdates);
	
?>
	<a href="index.php">[Home]</a>
	<a href="updater.php">[Updater]</a>
	
	<form name="form" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
	<input type="hidden" name="action" value="performupdate" />
	<input type="hidden" name="update_version" value="" />
	<input type="hidden" name="which_update" value="" />
	<input type="hidden" name="force_update" value="0" />
	</form>
	<table cellpadding="0" cellspacing="0">
<?php
	foreach ($dbupdates as $dbupver => $dbqueries)
	{
		$numupdates = count($dbqueries);
		$updates_applied = 0;
		
		echo '<tr><td class="update-list-version" colspan="2">v'.$dbupver;
		echo " - ".$numupdates." update";
		echo ($numupdates != 1) ? "s" : "";
		echo "</td>\n";
		echo '<td class="update-list-version" nowrap="nowrap"><a href="#" onclick="document.form.update_version.value=\''.$dbupver.'\'; document.form.which_update.value=\'all\'; document.form.submit();">[apply all]</a></td></tr>'."\n";
		echo '<tr><td class="update-list-header" nowrap="nowrap">Name</td>'."\n";
		echo '<td class="update-list-header">Applied - ';
		echo '<span class="update-list-item-applied">yes</span> ';
		echo '<span class="update-list-item-noapplied">no</span> ';
		echo '<span class="update-list-item-error">error</span>';
		echo "</td>\n";
		echo '<td class="update-list-header" nowrap="nowrap">action</td</tr>'."\n";
		foreach ($dbqueries as $dbkey => $dbquery)
		{
			$update_status = "notapplied";
			$update_error = "";
			
			// check if the update is applied
			if (CheckUpdate($dbupver, $dbquery['name']))
			{
				$update_status = "applied";
				$updates_applied++;
			} // end if update is applied
			
			if (($version == $dbupver && ($which_update == $dbkey || $which_update == "all"))
				|| ($version == "all" && $which_update == "all"))
			{
				if (!CheckUpdate($dbupver, $dbquery['name']) || $force)
				{
					db_query($dbquery['query'], true);
					
					// check for error
					if (!mysql_errno($GLOBALS["netmrg"]["dbconn"]))
					{
						$update_status = "applied";
						$updates_applied++;
						LogUpdate($dbupver, $dbquery['name'], $dbver);
					} // if no error
					else
					{
						$update_status = "error";
						$update_error = "<br /><b>DB_ERROR:</b> Couldn't execute query:<br>\n<pre>{$dbquery['query']}</pre><br>\n<pre>".mysql_error($GLOBALS["netmrg"]["dbconn"])."</pre><br>\n\n";
						if ($force)
						{
							LogUpdate($dbupver, $dbquery['name'], $dbver);
						} // end if we were forcing this update, we need to log it
					} // end if error
				} // end if the update isn't applied and we aren't forcing it
				
			} // end if we need to try to apply this update
			
			echo '<tr><td class="update-list-item" nowrap="nowrap">'. $dbquery['name'] ."</td>\n";
			echo '<td class="update-list-query update-list-item-'.$update_status.'">';
			echo (strlen($dbquery['query']) > 45) ? substr($dbquery['query'], 0, 42)."..." : $dbquery['query'];
			echo $update_error;
			echo "</td>\n";
			echo '<td class="update-list-item"><a href="#" onclick="document.form.update_version.value=\''.$dbupver.'\'; document.form.which_update.value=\''.$dbkey.'\'; document.form.submit();">[apply]</a> <a href="#" onclick="document.form.update_version.value=\''.$dbupver.'\'; document.form.which_update.value=\''.$dbkey.'\'; document.form.force_update.value=\'true\'; document.form.submit();">[force]</a></tr>'."\n";
			
		} // end foreach db query

		// update the database version if we're updating this version or all versions
		if (($version == $dbupver || $version == "all")
			&& $GLOBALS["netmrg"]["verhist"][$dbupver] > $GLOBALS["netmrg"]["verhist"][$dbver]
			&& $numupdates == $updates_applied)
		{
			UpdateDBVersion($dbupver);
		} // end if this version > db version

		echo '<tr><td class="update-list-header" colspan="3">';
		echo $updates_applied." update";
		echo ($updates_applied != 1) ? "s" : "";
		echo ' applied</td></tr>'."\n";
	} // end foreach update version
?>
	</table>
	<br />
	<a href="index.php">[Home]</a>
	<a href="updater.php">[Updater]</a>
<?php
	end_page();
} // end Updater();


/**
* PrepUpdater($dbupdates)
*
* makes sure the Updater() is good to run
* by making sure the table exists and old updates are marked as 'applied'
*/
function PrepUpdater($dbupdates)
{
	$dbver = $GLOBALS["netmrg"]["dbversion"];
	
	if (db_fetch_cell("SHOW TABLES LIKE 'updates'") == "")
	{
		db_query("CREATE TABLE updates (update_version VARCHAR(16) NOT NULL, name VARCHAR(160) NOT NULL, version_applied_to VARCHAR(16) NOT NULL, dateapplied DATETIME NOT NULL);");
		
		foreach ($dbupdates as $dbupver => $dbqueries)
		{
			foreach ($dbqueries as $dbkey => $dbquery)
			{
				// we'll assume updates lower than $dbver are applied
				if ($GLOBALS["netmrg"]["verhist"][$dbupver] <= $GLOBALS["netmrg"]["verhist"][$dbver])
				{
					LogUpdate($dbupver, $dbquery['name'], "pre-$dbver");
				} // end if dbupver < this ver
			} // end foreach db query
		} // end foreach update version
	} // end if we don't have an 'updates' table
} // end PrepUpdater();


/**
* LogUpdate();
*
* log that we did an update
*/
function LogUpdate($update_version, $name, $version_applied_to)
{
	if (!CheckUpdate($update_version, $name))
	{
		db_query("INSERT INTO updates SET
			update_version = '$update_version',
			name = '$name',
			version_applied_to = '$version_applied_to',
			dateapplied = now()");
	} // end if the update's not already logged
} // end LogUpdate();


/**
* CheckUpdate($update_version, $name);
*
*/
function CheckUpdate($update_version, $name)
{
	if (db_fetch_cell("SELECT 1 FROM updates WHERE update_version = '$update_version' AND name = '$name'"))
	{
		return true;
	} // end if the update exists
	
	return false;
} // end CheckUpdate();


?>
