<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/CustomBlockDeleteForm.class.php";
class Legacy_BlockUninstallForm extends Legacy_CustomBlockDeleteForm
{
	function getTokenName()
	{
		return "module.legacy.BlockUninstallForm.TOKEN" . $this->get('bid');
	}
	function update(&$obj)
	{
		parent::update($obj);
		$obj->set('last_modified', time());
		$obj->set('visible', false);
	}
}
?>
