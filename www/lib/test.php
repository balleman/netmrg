<? 

#require("./snmp_caching.php");
#print("chm1: " . do_snmp_get("chm1.rtr.pa.net","public",".1.3.6.1.4.1.9.2.1.58.0") . "\n\n");
#print("chm2: " . do_snmp_get("chm2.rtr.pa.net","public",".1.3.6.1.4.1.9.2.1.58.0") . "\n\n");

require("./graphing.php");
print(get_graph_command("custom",284,0,0));

?>

