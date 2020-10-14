<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formradio.php";
class XoopsFormRadioYN extends XoopsFormRadio
{
	function XoopsFormRadioYN($caption, $name, $value=null, $yes=_YES, $no=_NO)
	{
		$this->XoopsFormRadio($caption, $name, $value);
		$this->addOption(1, $yes);
		$this->addOption(0, $no);
	}
}
?>
