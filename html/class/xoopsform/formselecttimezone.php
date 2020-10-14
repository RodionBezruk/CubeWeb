<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
include_once XOOPS_ROOT_PATH."/class/xoopsform/formselect.php";
class XoopsFormSelectTimezone extends XoopsFormSelect
{
	function XoopsFormSelectTimezone($caption, $name, $value=null, $size=1)
	{
		$this->XoopsFormSelect($caption, $name, $value, $size);
		$this->addOptionArray(XoopsLists::getTimeZoneList());
	}
}
?>
