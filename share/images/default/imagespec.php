<?php
/**
* imagespec.php
*
* specfile for images
*/

$imagepackname = "default";
$GLOBALS["netmrg"]["imagedir"] = "{$GLOBALS['netmrg']['webroot']}/images/$imagepackname";
$GLOBALS["netmrg"]["imagespec"] = array(
	"logo" => "netmrg-logo-small.png",
	"disk" => "disk.png",
	"arrow" => "arrow.png",
	"viewgraph-on" => "viewgraph-on.png",
	"viewgraph-off" => "viewgraph-off.png",
	"status-good-trig"       => "status-good-trig.png",
	"status-good-untrig"     => "status-good-untrig.png",
	"status-warning-trig"    => "status-warning-trig.png",
	"status-warning-untrig"  => "status-warning-untrig.png",
	"status-critical-trig"   => "status-critical-trig.png",
	"status-critical-untrig" => "status-critical-untrig.png",
	"status-unknown-trig"    => "status-unknown-trig.png",
	"status-unknown-untrig"  => "status-unknown-untrig.png"
);
?>
