<?

########################################################
#                                                      #
#           NetMRG Integrator                          #
#           Web Interface                              #
#                                                      #
#           Site Help - About                          #
#           about.php                                  #
#                                                      #
#     Copyright (C) 2001-2002 Brady Alleman.           #
#     brady@pa.net - www.treehousetechnologies.com     #
#                                                      #
########################################################

require_once("../include/config.php");
check_auth(1);

begin_page();
?>

<br><br>
<div align="center">
<a href="http://netmrg.net/">
<font size="6" color="#000080"><b>NetMRG</b></font>
</a>
<br>

<big>
<b>The Network Monitoring, Reporting, and Graphing Tool</b>
<br><br>

Version <b><? echo $GLOBALS["netmrg"]["version"]; ?></b><br>
<br><br>

Copyright &copy; 2001-2002 <a href="http://www.treehousetechnologies.net/brady/">Brady Alleman</a>.<br>
This project is licensed under the terms of the <a href="http://www.gnu.org/">GNU</a> <a href="http://www.gnu.org/licenses/gpl.html">GPL</a>.
<br><br>
Portions of this project were developed at <a href="http://www.ctinetworks.com/">CTI|Networks</a>.<br>
<a href="http://raxnet.net/">Ian Berry</a>, <a href="http://silfreed.net/">Doug Warner</a>, and <a href="http://haller.ws/">Patrick Haller</a> offered suggestions, found bugs, and were supportive in general.<br>
<br>
This project makes use of <a href="http://www.rrdtool.org/">RRDTOOL</a>, <a href="http://net-snmp.sourceforge.net/">NET-SNMP</a>, <a href="http://www.php.net/">PHP</a>, C++, Perl, and pthreads.<br>
<br>
<a href="http://www.netmrg.net/">NetMRG</a> is a project of <a href="http://www.treehousetechnologies.net/">TreehouseTechnologies</a>.
</big>
</div>
<?

end_page();

?>
