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
check_auth($PERMIT["SingleViewOnly"]);

begin_page("contributors.php", "Contributors");
?>

<div class="about">
<a href="http://www.netmrg.net/">NetMRG</a><br />
<h3>The Network Monitoring, Reporting, and Graphing Tool</h3>

<h4>Other Contributors</h4>

<h5>Code</h5>
<a href="http://user.pa.net/~keb/">Kevin Bonner</a> is instrumental in the 
    application of autoconf and portability features to this project.<br />
Ryabkov (Rojer) Deomid has contributed many patches, fixing bugs that 
    are difficult to reproduce.<br />
</ br>

<h5>Suggestions and Comments</h5>
<a href="http://home.raxnet.net/">Ian Berry</a>, the main developer 
    of <a href="http://www.cacti.net/">Cacti</a>, has a lot of insight into 
    graphing software which has helped this project.<br />
<a href="http://www.haller.ws/">Patrick Haller</a> assisted with concurrency 
    debugging in the original gatherer.<br />
<br />

<h5>Testing and Debugging</h5>
The staff of <a href="http://www.ctinetworks.com/">CTI Networks, Inc.</a> was 
    finding bugs in NetMRG long before the rest of the world knew about it.<br />

</div>

<?php

end_page();

?>
