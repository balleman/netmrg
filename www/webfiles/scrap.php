?>

<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
<tr>
	<td colspan="4" bgcolor="<? print(get_color_by_name("edit_header")); ?>">
	<font color="<? print(get_color_by_name("edit_header_text")); ?>">
	Monitored Items
	</font>
	</td>
</tr>
<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>">
	<td width="35%">
		<font color="<? print(get_color_by_name("edit_header_text")); ?>">
		<b><a href="<? print($SCRIPT_NAME); ?>?orderby=name">Name</a></b></font>
	</td>
	<td width="35%">
		<font color="<? print(get_color_by_name("edit_header_text")); ?>">
		<b><a href="<? print($SCRIPT_NAME); ?>?orderby=ip">IP or Host Name</a></b></font>
	</td>
	<td width="15%"></td>
	<td width="15%" align="right">
	<a href="<?print("$SCRIPT_NAME?action=add");?>">
	<font color="#FFFF00"><b>Add</b></font></a>&nbsp;
	</td>
</tr>
<?

<tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>">
	<td><?print($dev_row["name"]);?></td>
	<td><?print($dev_row["ip"]);?></td>
	<td><a href="<?print("$SCRIPT_NAME?action=edit&dev_id=$dev_id");?>">Edit</a></td>
	<td><a href="<?print("$SCRIPT_NAME?action=delete&dev_id=$dev_id");?>">Delete</a></td>
</tr>

<font size="4" color="#800000">Events</font><br><br>
<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
	<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>">
		<td width="45%">
			<font color="<? print(get_color_by_name("edit_header_text")); ?>">
			<b>Monitor</b></font>
		</td>
		<td width="25%">
		<font color="<? print(get_color_by_name("edit_header_text")); ?>">
		<b>Result to Trigger</b></font>
		</td>
		<td width="15%">
		</td>
		<td width="15%" align="right">
			<a href="<?print("$SCRIPT_NAME?action=add");?>">
			<font color="#FFFF00"><b>Add</b></font></a>&nbsp;
		</td>
</tr>
<?
?>
<tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>">
	<td><?print($mon_row["dev_name"] . " (" . $mon_row["test_name"] . ")");?></td>
	<td><?print($mon_row["result"]);?></td>
	<td><a href="<?print("$SCRIPT_NAME?action=edit&event_id=$event_id");?>">Edit</a></td>
	<td><a href="<?print("$SCRIPT_NAME?action=delete&event_id=$event_id");?>">Delete</a></td>
</tr>
<?

?>
<font size="4" color="#800000">Monitored Devices</font><br><br>
<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>"><td width="35%"><font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Monitored Device</b></font></td><td width="35%">
<font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Type of Monitoring</b></font></td><td width="15%"></td>
<td width="15%" align="right"><a href="<?print("$SCRIPT_NAME?action=add");?>">
<font color="#FFFF00"><b>Add</b></font></a>&nbsp;</td></tr>
<?

?><tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>"><td><?print($mon_row[8]);?></td><td><?print($mon_row[5]);?>
</td><td><a href="<?print("$SCRIPT_NAME?action=edit&mon_id=$mon_id");?>">Edit</a>
</td><td><a href="<?print("$SCRIPT_NAME?action=delete&mon_id=$mon_id");?>">Delete</a></td></tr><?

?>
<font size="4" color="#800000">Notification</font><br><br>
<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>"><td width="35%"><font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Name</b></font></td><td width="35%">
<font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Command</b></font></td><td width="15%"></td>
<td width="15%" align="right"><a href="<?print("$SCRIPT_NAME?action=add");?>">
<font color="#FFFF00"><b>Add</b></font></a>&nbsp;</td></tr>
<?

?><tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>"><td><?print($test_row["name"]);?></td><td><?print($test_row["cmd"]);?>
</td><td><a href="<?print("$SCRIPT_NAME?action=edit&notify_id=$notify_id");?>">Edit</a>
</td><td><a href="<?print("$SCRIPT_NAME?action=delete&notify_id=$notify_id");?>">Delete</a></td></tr><?

?>
<font size="4" color="#800000">Event Responses</font><br><br>
<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>"><td width="30%"><font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Event</b></font></td><td width="15%">
<font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Notification Method</b></font></td>
<td width="25%"><font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Command Parameters</b></font></td><td width="15%"></td>
<td width="15%" align="right"><a href="<?print("$SCRIPT_NAME?action=add");?>">
<font color="#FFFF00"><b>Add</b></font></a>&nbsp;</td></tr>
<?

?><tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>"><td><?print($responses_row["dev_name"] . " (" . $responses_row["test_name"] . " = " . $responses_row["result"] . ")");?></td><td><?print($responses_row["notify_name"]);?>
</td><td><?print($responses_row["cmd_params"]);?>
</td><td><a href="<?print("$SCRIPT_NAME?action=edit&response_id=$response_id");?>">Edit</a>
</td><td><a href="<?print("$SCRIPT_NAME?action=delete&response_id=$response_id");?>">Delete</a></td></tr><?

?>
<font size="4" color="#800000">Device Tests</font><br><br>
<table width="95%" border="0" cellspacing="2" cellpadding="2" align="center">
<tr bgcolor="<? print(get_color_by_name("edit_header")); ?>"><td width="35%"><font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Name</b></font></td><td width="35%">
<font color="<? print(get_color_by_name("edit_header_text")); ?>"><b>Command</b></font></td><td width="15%"></td>
<td width="15%" align="right"><a href="<?print("$SCRIPT_NAME?action=add");?>">
<font color="#FFFF00"><b>Add</b></font></a>&nbsp;</td></tr>
<?

?><tr bgcolor="<? print(get_color_by_name("edit_fields")); ?>"><td><?print($test_row["name"]);?></td><td><?print($test_row["cmd"]);?>
</td><td><a href="<?print("$SCRIPT_NAME?action=edit&test_id=$test_id");?>">Edit</a>
</td><td><a href="<?print("$SCRIPT_NAME?action=delete&test_id=$test_id");?>">Delete</a></td></tr><?
?>
<





?>
<font size="4" color="#800000">Add Device</font><br><br>
Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="text" name="dev_name" size="15" maxlength="50" value=""><br><br>
IP or Host Name:<br>
<input type="text" name="dev_ip" size="15" maxlength="50" value=""><br><br>
<input type="hidden" name="action" value="doadd">
<input type="submit" name="Submit" value="Submit">
</form>
<?


?>
<font size="4" color="#800000">Edit Device</font><br><br>
Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="text" name="dev_name" size="15" maxlength="50" value="<? print($dev_name); ?>"><br><br>
IP or Host Name:<br>
<input type="text" name="dev_ip" size="15" maxlength="50" value="<? print($dev_ip); ?>"><br><br>
<input type="hidden" name="action" value="doedit">
<input type="hidden" name="dev_id" value="<? print("$dev_id");?>">
<input type="submit" name="Submit" value="Submit">
</form>
<?


?>
<font size="4" color="#800000">Add Event</font><br><br>
Monitor:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<select name="mon_id" >
<?

?><option value="<?print($dev_id);?>"><?print("$dev_name\n");

?>
</select><br><br>
Result:<br><br>
<input type="text" name="result" size="25" maxlength="50">
<br><br>
<input type="hidden" name="action" value="doadd">
<input type="submit" name="Submit" value="Submit">
</form>
<?

<select name="">
			<option value="1" SELECTED></option>
			<option value="2"></option>
			<option value="3"></option>
</select>

?>
<font size="4" color="#800000">Edit Event</font><br><br>
Monitor:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<select name="mon_id" >
<?

?><option value="<?print($mon_id);?>"><?print("$mon_name\n");

?><option selected value="<?print($mon_id);?>"><?print("$mon_name\n");



		if ($test_result == $mon_row["last_status"]) {
		# If there was no change, do only "Always" events
			$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id AND result=$test_result AND when=1");
		} else {
		# If there was a change, do all matching events
			$event_results = do_query("SELECT * FROM mon_events WHERE monitors_id=$mon_id AND result=$test_result");
		} # end if

		
		
?>
<font size="4" color="#800000">Add Monitor</font><br><br>
Device Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<select name="dev_id" >
<?


?><option value="<?print($dev_id);?>"><?print("$dev_name\n");

?>
</select><br><br>
Type of Test:<br><br>
<select name="test_id">
<?


make_edit_select("Type of Test:","test_id");

$test_results = do_query("SELECT * FROM mon_test");
$test_total = mysql_num_rows($test_results);

for ($test_count = 1; $test_count <= $test_total; ++$test_count) { 

$test_row = mysql_fetch_array($test_results);
$test_name = $test_row["name"];
$test_id	= $test_row["id"];

make_edit_select_option($test_name, 
?><option value="<?print($test_id);?>"><?print("$test_name\n");

} # end for
?>
</select>
<br><br>
<input type="hidden" name="action" value="doadd">
<input type="submit" name="Submit" value="Submit">
</form>
<?
?>
<font size="4" color="#800000">Edit Monitor</font><br><br>
Device Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<select name="dev_id" >
<?
for ($dev_count = 1; $dev_count <= $dev_total; ++$dev_count) { 

$dev_row = mysql_fetch_array($dev_results);
$dev_name = $dev_row["name"];
$dev_id = $dev_row["id"];

if ($dev_id_cur != $dev_id) {
?><option value="<?print($dev_id);?>"><?print("$dev_name\n");
} else {
?><option selected value="<?print($dev_id);?>"><?print("$dev_name\n");
} # end if
} # end for


?>
</select><br><br>
Type of Test:<br><br>
<select name="test_id">
<?


for ($test_count = 1; $test_count <= $test_total; ++$test_count) { 

$test_row = mysql_fetch_array($test_results);
$test_name = $test_row["name"];
$test_id   = $test_row["id"];

if ($test_id_cur != $test_id) { 
?><option value="<?print($test_id);?>"><?print("$test_name\n");
} else {
?><option selected value="<?print($test_id);?>"><?print("$test_name\n");
} # end if
} # end for

$test_results = do_query("SELECT * FROM mon_test");
$test_total = mysql_num_rows($test_results);

$dev_results = do_query("SELECT * FROM mon_devices");
$dev_total = mysql_num_rows($dev_results);

?>
</select>
<br><br>
<input type="hidden" name="action" value="doedit">
<input type="hidden" name="mon_id" value="<?print("$mon_id");?>">
<input type="submit" name="Submit" value="Submit">
</form>
<?





?> 
<table width="95%" align="center">
	<tr>
		<td><strong>Group</strong></td>
		<td><strong>Device</strong></td>
		<td><strong>Monitors</strong></td>
		<td><strong>Events</strong></td>
		<td><strong>Lights</strong></td>
	</tr> 
<?


#$mon_row["dev_name"] . " (" . $mon_row["test_name"] . ")","",

$tree_index = split(":", $c_tree_index);
$tree_value = split(":", $c_tree_value);


if (isset($expand)) {

array_push($tree_index, $expand);
array_push($tree_value, 

}

setcookie("c_tree_index", join(":", $tree_index));
setcookie("c_tree_value", join(":", $tree_value));


#PassThru("/usr/local/rrdtool-1.0.28/bin/rrdtool graph - \
#--title=\"test\" \
#DEF:i1=./rrd/mon_16.rrd:mon_16:AVERAGE DEF:i2=./rrd/mon_18.rrd:mon_18:AVERAGE \
#DEF:i3=./rrd/mon_19.rrd:mon_19:AVERAGE DEF:i4=./rrd/mon_20.rrd:mon_20:AVERAGE \
#DEF:i5=./rrd/mon_22.rrd:mon_22:AVERAGE AREA:i2#121212:i2 AREA:i1#131313:i1 \
#AREA:i3#141414:i3 AREA:i4#151515:i4 AREA:i5#161616:i5");

<table><tr><td width="90%">

<font size="4" color="#800000"><? print($title); ?></font><br><br>




?>
<font size="4" color="#800000">Add Test</font><br><br>
Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="text" name="test_name" size="15" maxlength="50" value=""><br><br>
Command:<br>
<input type="text" name="test_cmd" size="15" maxlength="100" value=""><br><br>
<? make_edit_select_from_table("Data Type:","data_type","mon_data_types",0); ?>
<input type="hidden" name="action" value="doadd">
<input type="submit" name="Submit" value="Submit">
</form>
<?




?>
<font size="4" color="#800000">Edit Test</font><br><br>
Name:<br>
<form action="<? print("$SCRIPT_NAME"); ?>" method="post">
<input type="text" name="test_name" size="15" maxlength="50" value="<? print($test_name); ?>"><br><br>
Command:<br>
<input type="text" name="test_cmd" size="15" maxlength="100" value="<? print($test_cmd); ?>"><br><br>
<? make_edit_select_from_table("Data Type:","data_type","mon_data_types",$test_row["data_type"]); ?>
<input type="hidden" name="action" value="doedit">
<input type="hidden" name="test_id" value="<? print("$test_id");?>">
<input type="submit" name="Submit" value="Submit">
</form>
<?


make_edit_select("Monitor:","mon_id");

$event_results = do_query("SELECT * FROM mon_events WHERE id=$event_id");
$event_row = mysql_fetch_array($event_results);
$mon_id_cur	= $event_row["monitors_id"];
$result_cur = $event_row["result"];

$mon_results = do_query("
SELECT mon_monitors.id, mon_devices.name as dev_name, mon_test.name as test_name
FROM mon_monitors 
LEFT JOIN mon_devices ON mon_monitors.device_id=mon_devices.id 
LEFT JOIN mon_test ON mon_monitors.test_id=mon_test.id");
$mon_total = mysql_num_rows($mon_results);

for ($mon_count = 1; $mon_count <= $mon_total; ++$mon_count) { 

$mon_row = mysql_fetch_array($mon_results);
$mon_name = $mon_row["dev_name"] . " (" . $mon_row["test_name"] . ")";
$mon_id = $mon_row["id"];

if ($mon_id_cur != $mon_id) {
make_edit_select_option($mon_name,$mon_id);
} else {
make_edit_select_option($mon_name,$mon_id,1);
} # end if
} # end for

make_edit_select_end();