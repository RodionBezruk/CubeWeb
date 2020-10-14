<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractEditAction.class.php";
class Legacy_AbstractDeleteAction extends Legacy_AbstractEditAction
{
	function isEnableCreate()
	{
		return false;
	}
	function _doExecute()
	{
		return $this->mObjectHandler->delete($this->mObject);
	}
}
?>
