<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class Legacy_SmilesAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.SmilesAdminDeleteForm.TOKEN" . $this->get('id');
	}
	function prepare()
	{
		$this->mFormProperties['id'] =& new XCube_IntProperty('id');
		$this->mFieldProperties['id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['id']->setDependsByArray(array('required'));
		$this->mFieldProperties['id']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_ID);
	}
	function load(&$obj)
	{
		$this->set('id', $obj->get('id'));
	}
	function update(&$obj)
	{
		$obj->set('id', $this->get('id'));
	}
}
?>
