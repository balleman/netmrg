<?

$graph_results = do_query("SELECT * FROM graphs WHERE id=$id");
$graph_row = mysql_fetch_array($graph_results);


$command = get_path_by_name("rrdtool") . 'graph test.png --title"' . $graph_row["name"] . '" -w ' . 
		   $graph_row["xsize"] . ' -h ' . $graph_row["ysize"] . ' -v "' . $graph_row["vert_label"] . 
		   '" --imgformat PNG ';	
		   
$ds_results = do_query("SELECT * FROM graph_ds WHERE graph_id=$id");

$ds_total = mysql_num_rows($ds_results);

for ($ds_count = 1; $ds_count <= $ds_total; ++$ds_count) { 


$command = $command . "DEF:data" . $ds_count . "=./rrd/mon_" . $row["src_id"] . ".rrd:mon_" . 
           $row["src_id"] . ":AVERAGE " . $row["type"] . ":data" . $ds_count . $row["color"] . ':"' . $row["label"] . '"');

}
	?>