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
check_auth(0);

begin_page("about.php", "About");
?>

<br><br>

<div align="center">
<a href="http://netmrg.net/"><font size="6" color="#000080"><b>NetMRG</b></font></a><br>

<big>

<b>The Network Monitoring, Reporting, and Graphing Tool</b>

<br><br>

Version <b><?php echo $GLOBALS["netmrg"]["version"]; ?></b><br>
<br><br>

Copyright &copy; 2001-2003 <a href="http://thtech.net/brady/">Brady Alleman</a> and <a href="http://silfreed.net/">Douglas E. Warner</a>.<br>

<br><br>
Special thanks to our <a href="contributors.php">other contributors</a>.

</big>
<br><br><br>

<table width="55%" style="border: 1px solid black;">
<tr><td align="left">
This project is licensed under the terms of the MIT License.<br><br>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:<br><br>

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.<br><br>

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
</td>
</tr>
</table>

<br>
<a href="http://www.netmrg.net/">NetMRG</a> is a project of <a href="http://www.treehousetechnologies.net/">TreehouseTechnologies</a>.

</div>

<?php

end_page();

?>
