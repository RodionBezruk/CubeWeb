<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class Legacy_ModuleUpdateForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.ModuleUpdateForm.TOKEN." . $this->get('dirname');
	}
	function prepare()
	{
		$this->mFormProperties['dirname'] =& new XCube_StringProperty('dirname');
		$this->mFormProperties['force'] =& new XCube_BoolProperty('force');
	}
	function load(&$obj)
	{
		$this->set('dirname', $obj->get('dirname'));
	}
	function update(&$obj)
	{
		$obj->set('dirname', $this->get('dirname'));
	}
}
?>
