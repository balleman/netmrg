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
check_auth(0);

begin_page("contributors.php", "Contributors");
?>

<br><br>

<div align="center">
<a href="http://netmrg.net/"><font size="6" color="#000080"><b>NetMRG</b></font></a><br>

<big>

<b>The Network Monitoring, Reporting, and Graphing Tool</b>

<br><br>

<b><u>Other Contributors</u></b><br><br><br>

<b>Code</b><br>
<a href="http://user.pa.net/~keb/">Kevin Bonner</a> was, and continues to be, instrumental in the application of autoconf, automake and portability features to this project.<br>

<br>

<b>Suggestions and Comments</b><br>
<a href="http://raxnet.net/">Ian Berry</a>, the main developer of <a href="http://raxnet.net/products/cacti/">Cacti</a>, has a lot of insight into graphing software which has helped this project.<br>
<a href="http://haller.ws/">Patrick Haller</a> assisted with the debugging of threading problems.<br>

<br>

<b>Testing and Debugging</b><br>
The staff of <a href="http://www.ctinetworks.com/">CTI|Networks</a> was finding bugs in NetMRG long before the rest of the world knew about it.<br>

<br><br>

</div>

<?php

end_page();

?>
