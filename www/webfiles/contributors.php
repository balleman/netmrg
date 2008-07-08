<?php
/********************************************
* NetMRG Integrator
*
* contributors.php
* Site Help - Contributors
*
* Copyright (C) 2001-2008
*   Brady Alleman <brady@thtech.net>
*   Douglas E. Warner <silfreed@silfreed.net>
*   Kevin Bonner <keb@nivek.ws>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
********************************************/


require_once("../include/config.php");
check_auth($GLOBALS['PERMIT']["SingleViewOnly"]);

begin_page("contributors.php", "Contributors");
?>

<div class="about">
<a href="http://www.netmrg.net/">NetMRG</a><br />
<h3>The Network Monitoring, Reporting, and Graphing Tool</h3>

<h4>Other Contributors</h4>

<h5>Code</h5>
Ryabkov (Rojer) Deomid has contributed many patches, fixing bugs that 
    are difficult to reproduce.<br />
<br />

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
