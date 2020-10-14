<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyImagebodyObject extends XoopsSimpleObject
{
	function LegacyImagebodyObject()
	{
		$this->initVar('image_id', XOBJ_DTYPE_INT, '', false);
		$this->initVar('image_body', XOBJ_DTYPE_TEXT, '', true);
	}
}
class LegacyImagebodyHandler extends XoopsObjectGenericHandler
{
	var $mTable = "imagebody";
	var $mPrimary = "image_id";
	var $mClass = "LegacyImagebodyObject";
}
?>
