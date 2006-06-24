<?php
/********************************************
* NetMRG Integrator
*
* ajax.php
* AJAX Common Library
*
* see doc/LICENSE for copyright information
********************************************/

$xajax = new xajax("ajax.php");
$xajax->registerFunction("redraw_subdevice");
$xajax->registerFunction("redraw_monitor");

class netmrgXajaxResponse extends xajaxResponse
{
	function addCreateOption($sSelectId, $sOptionText, $sOptionValue)  
	{
		$this->addScript("addOption('".$sSelectId."','".$sOptionText."','".$sOptionValue."');");
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
