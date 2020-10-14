<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formselect.php";
class XoopsFormSelectMatchOption extends XoopsFormSelect
{
	function XoopsFormSelectMatchOption($caption, $name, $value=null, $size=1)
	{
		$this->XoopsFormSelect($caption, $name, $value, $size, false);
		$this->addOption(XOOPS_MATCH_START, _STARTSWITH);
		$this->addOption(XOOPS_MATCH_END, _ENDSWITH);
		$this->addOption(XOOPS_MATCH_EQUAL, _MATCHES);
		$this->addOption(XOOPS_MATCH_CONTAIN, _CONTAINS);
	}
}
?>
