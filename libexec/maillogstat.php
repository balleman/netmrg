#!/usr/bin/php -q
<?
/*  maillogstat.php
**
**  outputs the a specified statistic for a host 
*/



/********** Config **********/
error_reporting(E_ERROR);

// make a new $CFG object
class object {};
$CFG = new object;

$CFG->dbhost = "localhost";
$CFG->dbuser = "maillog";
$CFG->dbpass = "k33pmym41l0gs3cur3";
$CFG->dbname = "maillog";

// connect to mysql
mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass)
	or die("ERROR: cannot connect to server\n");
mysql_select_db($CFG->dbname)
	or die("ERROR: cannot connect to database\n");



/********** Core **********/
if ($argc != 3 || !is_string($argv[1]) || !is_string($argv[2])) {
	exit();
} // end if not enough command line args

$hostname = $argv[1];
$counter = $argv[2];

$stat_query = "SELECT $counter FROM postfix WHERE hostname = '$hostname'";
$stat_result = mysql_query($stat_query) or 
	die("ERROR: cannot perform query\n$stat_query\n\n");

$stat = mysql_fetch_array($stat_result);
echo "{$stat[$counter]}\n";

exit();



/********** FUNCTIONS **********/

/* Error($errortext);  outputs $errortext to stderr */
function Error($errortext)
{
	error_log($errortext."\n\n", 3, "/dev/stderr");
}
?>
