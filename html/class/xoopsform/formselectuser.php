<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formselect.php";
class XoopsFormSelectUser extends XoopsFormSelect
{
	function XoopsFormSelectUser($caption, $name, $include_anon=false, $value=null, $size=1, $multiple=false)
	{
	    $this->XoopsFormSelect($caption, $name, $value, $size, $multiple);
		$member_handler =& xoops_gethandler('member');
		if ($include_anon) {
			global $xoopsConfig;
			$this->addOption(0, $xoopsConfig['anonymous']);
		}
		$this->addOptionArray($member_handler->getUserList());
	}
}
?>
