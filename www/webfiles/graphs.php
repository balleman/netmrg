<? 

require_once("format.php");

begin_page();

#include "/var/www/shared/common.php";
#body_tag();

#Check for state changes

if (isset($section)) { 

mysql_connect ('localhost', 'webuser', 'webuser') or die ('I cannot connect to the database. Make sure that mysql is installed and that you are trying to log in as a valid user.'); 
mysql_select_db ('webadmin') or die ('The database specified in database_name must exist and must be accessible by the user specified in mysql_connect'); 

$query = "SELECT * FROM graphlist WHERE id = $section"; 
$query_result_handle = mysql_query ($query) or die ('The query failed! table_name must be a valid table name that exists in the database specified in mysql_select_db'); 

# make sure that we recieved some data from our query 
$num_of_rows = mysql_num_rows ($query_result_handle) or die ("The query: '$query' did not return any data"); 
$row = mysql_fetch_row ($query_result_handle);

if ($row[6] == 0) {$changeto = 1;} else {$changeto = 0;}

$query = "UPDATE graphlist SET state = $changeto WHERE id = $section"; 
$query_result_handle = mysql_query ($query) or die ('The query failed! table_name must be a valid table name that exists in the database specified in mysql_select_db'); 

}

function display_item($id) {

mysql_connect ('localhost', 'webuser', 'webuser') or die ('I cannot connect to the database. Make sure that mysql is installed and that you are trying to log in as a valid user.'); 
mysql_select_db ('webadmin') or die ('The database specified in database_name must exist and must be accessible by the user specified in mysql_connect'); 

$query = "SELECT * FROM graphlist WHERE parent = $id
ORDER BY priority"; 
$query_result_handle = mysql_query ($query) or die ('The query failed! table_name must be a valid table name that exists in the database specified in mysql_select_db'); 

# make sure that we recieved some data from our query 
$num_of_rows = mysql_num_rows ($query_result_handle); # or die ("The query: '$query' did not return any data"); 

for ($count = 1; $row = mysql_fetch_row ($query_result_handle); ++$count) 
{ 

if ($row[4] == 0) { 

print("<ul type=\"disc\" compact>");

if ($row[6] == 1) {

print("<img src='../images/hide.gif' border=0>&nbsp;<a href=\"./graphs.php?section=$row[0]\">$row[2]</a><br>");
display_item($row[0]);

} else {# end expanded

print("<img src='../images/show.gif' border=0>&nbsp;<a href=\"./graphs.php?section=$row[0]\">$row[2]</a><br>");

} # end hidden

} # end is a menu item

if ($row[4] == 1) {

print("<ul type=\"disc\" compact>");

if ($row[6] == 1) {

print("<img src='../images/hide.gif' border=0>&nbsp;<a href=\"./graphs.php?section=$row[0]\">$row[2]</a><br>");
print("<img src=\"../cgi-bin/mrtg/14all-1.1.cgi?log=$row[3]&png=daily\">");
print("<br><font size=\"1\"><a href='./graphs.php?details=$row[3]'>(details)</a></font>");

} else {# end expanded

print("<img src='../images/show.gif' border=0>&nbsp;<a href=\"./graphs.php?section=$row[0]\">$row[2]</a><br>");

} # end hidden
} # end is a MRTG graph

print("</ul>");

}

}

function show_graph($title, $image) {
print("<br><table border=\"2\"><tr><td width='90%'>$title</td><td align='center'><a href='./graphs.php'>Back</a></td></tr>");
print("<tr><td colspan=\"2\"><img src=\"$image\"></td></tr></table>");
}

if (isset($details)) { 

show_graph("Daily","../cgi-bin/mrtg/14all-1.1.cgi?log=$details&png=daily");
show_graph("Weekly","../cgi-bin/mrtg/14all-1.1.cgi?log=$details&png=weekly");

} else { # Not detailed, do full list

display_item(0);

}

end_page();
?>
