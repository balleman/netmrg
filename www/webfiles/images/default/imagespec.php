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
	"arrow-up" => "arrow-up.png",
	"arrow-right" => "arrow-right.png",
	"arrow-down" => "arrow-down.png",
	"arrow-left" => "arrow-left.png",
	"arrow-up-disabled" => "arrow-up-disabled.png",
	"arrow-right-disabled" => "arrow-right-disabled.png",
	"arrow-down-disabled" => "arrow-down-disabled.png",
	"arrow-left-disabled" => "arrow-left-disabled.png",
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
