<?php
/********************************************
* NetMRG Integrator
*
* ajax.php
* AJAX Common Library
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

$xajax = new xajax("ajax.php");
$xajax->registerFunction("redraw_subdevice");
$xajax->registerFunction("redraw_monitor");

class netmrgXajaxResponse extends xajaxResponse
{
	function addCreateOption($sSelectId, $sOptionText, $sOptionValue)  
	{
		$this->addScript("addOption('".addSlashes($sSelectId)."','".addSlashes($sOptionText)."','".addSlashes($sOptionValue)."');");
	}

	function addCreateOptions($sSelectId, $aOptions)
	{
		foreach($aOptions as $sOptionText => $sOptionValue)
		{
			$this->addCreateOption($sSelectId, $sOptionText, $sOptionValue);
		}
	}

	function addClearSelect($sSelectId)
	{
		$this->addScript("clearSelect('".$sSelectId."');");
	}
}

?>
