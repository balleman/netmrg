<?php
/********************************************
* NetMRG Integrator
*
* about.php
* Site Help - About
*
* see doc/LICENSE for copyright information
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

begin_page("about.php", "About");
?>

<div class="about">
<a href="http://www.netmrg.net/">NetMRG</a><br />
<h3>The Network Monitoring, Reporting, and Graphing Tool</h3>

Version <b><?php echo $GLOBALS["netmrg"]["version"]; ?></b><br /><br />

Copyright &copy;2001-2006 
  <a href="http://brady.thtech.net">Brady Alleman</a>, 
  <a href="http://www.silfreed.net/">Douglas E. Warner</a> and 
  <a href="http://www.nivek.ws/">Kevin Bonner</a>.<br />

<br />

Special thanks to our <a href="contributors.php">other contributors</a>.
</div>

<div class="about">
<br />
This project is licensed under the terms of the <a href="http://www.gnu.org/licenses/gpl.txt">GPL</a>, Version 2. <br />
Other licensing arrangements may be available upon request.  Please contact licensing@netmrg.net.

<br /><br />

<br />
<a href="http://www.netmrg.net/">NetMRG</a> is a project of 
    <a href="http://www.thtech.net/">TreehouseTechnologies</a>.
</div>

<?php

end_page();

?>
