<?php
/********************************************
* NetMRG Integrator
*
* updater.php
* Updates the current installation
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(3);

// check default action
if (empty($_REQUEST['action']))
{
	$_REQUEST["action"] = "prompt";
} // end if no action

// database updates that need done
//    "fromver" => array('query', ..)
$dbupdates = array(
	"0.10pre1" => array(
"ALTER TABLE `graphs` ADD `options` SET( 'nolegend', 'logarithmic' ) NOT NULL ;",
"ALTER TABLE `graphs` ADD `base` INT DEFAULT '1000' NOT NULL ;",
"ALTER TABLE `graph_ds` ADD `start_time` VARCHAR( 20 ) NOT NULL ;",
"ALTER TABLE `graph_ds` ADD `end_time` VARCHAR( 20 ) NOT NULL ;",
"ALTER TABLE `graphs` ADD `title` VARCHAR( 100 ) NOT NULL AFTER `name`;",
"ALTER TABLE `graph_ds` CHANGE `multiplier` `multiplier` VARCHAR( 100 ) DEFAULT '1' NOT NULL;",
"ALTER TABLE snmp_interface_cache ADD COLUMN ifSpeed int(11) NOT NULL;",
"UPDATE graphs SET title = name WHERE title = '';"
	), // end 0.10pre1
	"0.10pre2" => array(), // end 0.10pre2
	"0.10" => array(
"ALTER TABLE `devices` CHANGE `snmp_enabled` `snmp_version` TINYINT( 4 ) DEFAULT '1' NOT NULL ;",
"ALTER TABLE `devices` ADD `snmp_timeout` INT UNSIGNED DEFAULT '1000000' NOT NULL AFTER `snmp_version` ,
  ADD `snmp_retries` TINYINT UNSIGNED DEFAULT '3' NOT NULL AFTER `snmp_timeout` ,
  ADD `snmp_port` SMALLINT UNSIGNED DEFAULT '161' NOT NULL AFTER `snmp_retries` ;",
"CREATE TABLE user_prefs (
  id INT NOT NULL AUTO_INCREMENT, uid INT NOT NULL,
  module VARCHAR(64) NOT NULL, pref VARCHAR(64) NOT NULL,
  value VARCHAR(64) NOT NULL,
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY uid_module_pref (uid, module, pref));",
"INSERT INTO user_prefs (uid, module, pref, value) SELECT id, 'SlideShow', 'AutoScroll', 1 FROM user;"
	), // end 0.10
	"0.12" => array(), // end 0.12
	"0.13" => array(), // end 0.13
	"0.14" => array(
"ALTER TABLE `devices` ADD `snmp_recache_method` SMALLINT DEFAULT '0' NOT NULL AFTER `dev_type`;",
"UPDATE devices SET snmp_recache_method = 4 WHERE snmp_recache = 1;",
"UPDATE devices SET snmp_recache_method = 3 WHERE snmp_recache = 0 AND snmp_check_ifnumber = 1;",
"UPDATE devices SET snmp_recache_method = 1 WHERE snmp_recache = 0 AND snmp_check_ifnumber = 0;",
"ALTER TABLE `devices` DROP `snmp_recache`;",
"ALTER TABLE `devices` DROP `snmp_check_ifnumber`;",
"CREATE TABLE `log` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `date` DATETIME NOT NULL,
    `dev_id` INT,
    `subdev_id` INT,
    `mon_id` INT,
    `level` INT NOT NULL ,
    `component` INT NOT NULL ,
    `message` VARCHAR( 200 ) NOT NULL ,
    PRIMARY KEY ( `id` ) , INDEX ( `date` ) ,
    INDEX ( `dev_id` ), INDEX ( `subdev_id` ) , INDEX( `mon_id` ));",
"ALTER TABLE user DROP INDEX user;",
"ALTER TABLE user ADD CONSTRAINT UNIQUE user (user);"
	) // end 0.14
); // end $dbupdates;

// check what to do
switch ($_REQUEST['action'])
{
	case "update":
		update($dbupdates);
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

	$dbver = GetDBVersion();

	if ($dbver != $GLOBALS["netmrg"]["version"])
	{
?>
The current database needs to be updated from version
<b><?php echo $dbver; ?></b> to <b><?php echo $GLOBALS["netmrg"]["version"] ?></b><br />
<br />
<?php
		make_edit_table("Update Database?");
		make_edit_hidden("action", "update");
		make_edit_submit_button();
		make_edit_end();
	} // end if database needs updated
	else
	{
?>
Your database is already at the latest version,
<b><?php echo $dbver; ?></b>.  No upgrade is needed.
<?php
	} // end if no changes
	end_page();
} // end edit();


/**
* update($dbupdates)
*
* update's an installation
*/
function update($dbupdates)
{
	begin_page("updater.php", "Updater");

	$dbver = GetDBVersion();
	$doupdates = false;

	while (list($dbupver, $dbqueries) = each($dbupdates))
	{
		if ($dbupver == $dbver)
		{
			$doupdates = true;
		} // end if we've reached the version to do updates for
		else if ($dbupver == $GLOBALS["netmrg"]["version"])
		{
			break;
		} // end if we're at the current version

		if ($doupdates)
		{
			echo "<b>$dbupver</b><br />\n";
			foreach($dbqueries as $dbquery)
			{
				echo " .";
				db_query($dbquery);
			} // end foreach query in this version
			echo "<br /><br />\n";
		} // end if we're to do the updates
	} // end foreach version

	// update the database version
	UpdateDBVersion($GLOBALS["netmrg"]["version"]);

	if ($doupdates)
	{
		echo "All updates were completed successfully.<br />\n";
	} // end if we performed updates
	else
	{
		echo "No updates were needed for this version of NetMRG<br />\n";
	} // end else no updates
	echo "Enjoy your new version of NetMRG!!<br />\n";
	echo '<a href="index.php">[continue]</a>'."<br />\n";
	
	end_page();
} // end update();


?>
