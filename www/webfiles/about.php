<?php
/********************************************
* NetMRG Integrator
*
* about.php
* Site Help - About
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

begin_page("about.php", "About");
?>

<div class="about">
<a href="http://www.netmrg.net/">NetMRG</a><br />
<h3>The Network Monitoring, Reporting, and Graphing Tool</h3>

Version <b><?php echo $GLOBALS["netmrg"]["version"]; ?></b><br /><br />

Copyright &copy;2001-2007 
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
