<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/modules/legacy/admin/forms/BlockEditForm.class.php";
class Legacy_BlockInstallEditForm extends Legacy_BlockEditForm
{
	function getTokenName()
	{
		return "module.legacy.BlockInstallEditForm.TOKEN" . $this->get('bid');
	}
	function update(&$obj)
	{
		parent::update($obj);
		$obj->set('visible', true);
	}
}
?>
