<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class Legacy_InstallWizardForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.InstallWizardForm.TOKEN." . $this->get('dirname');
	}
	function prepare()
	{
		$this->mFormProperties['dirname'] =& new XCube_StringProperty('dirname');
		$this->mFormProperties['agree'] =& new XCube_BoolProperty('agree');
		$this->mFieldProperties['agree'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['agree']->setDependsByArray(array('min'));
		$this->mFieldProperties['agree']->addMessage('min', _AD_LEGACY_ERROR_PLEASE_AGREE);
		$this->mFieldProperties['agree']->addVar('min', '1');
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
