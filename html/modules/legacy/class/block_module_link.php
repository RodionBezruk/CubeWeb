<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyBlock_module_linkObject extends XoopsSimpleObject
{
	function LegacyBlock_module_linkObject()
	{
		$this->initVar('block_id', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('module_id', XOBJ_DTYPE_INT, '0', true);
	}
}
class LegacyBlock_module_linkHandler extends XoopsObjectGenericHandler
{
	var $mTable = "block_module_link";
	var $mPrimary = "block_id";
	var $mClass = "LegacyBlock_module_linkObject";
}
?>
