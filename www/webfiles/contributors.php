<?php
/********************************************
* NetMRG Integrator
*
* contributors.php
* Site Help - Contributors
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth(1);

begin_page("about.php", "About");
?>

<br><br>

<div align="center">
<a href="http://netmrg.net/"><font size="6" color="#000080"><b>NetMRG</b></font></a><br>

<big>

<b>The Network Monitoring, Reporting, and Graphing Tool</b>

<br><br>

<b><u>Other Contributors</u></b><br><br><br>

<b>Code</b><br>
<a href="http://user.pa.net/~keb/">Kevin Bonner</a> reconstructed the Makefile.<br>

<br>

<b>Suggestions and Comments</b><br>
<a href="http://raxnet.net/">Ian Berry</a> has a lot of insight into graphing software.<br>
<a href="http://haller.ws/">Patrick Haller</a> got me started on pthreads debugging.<br>

<br>

<b>Testing and Debugging</b><br>
The staff of <a href="http://www.ctinetworks.com/">CTI|Networks</a> was finding bugs in NetMRG long before the rest of the world knew about it.<br>

<br><br>

</div>

<?php

end_page();

?>
