<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class Legacy_CommentAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.XoopscommentsAdminDeleteForm.TOKEN" . $this->get('com_id');
	}
	function prepare()
	{
		$this->mFormProperties['com_id'] =& new XCube_IntProperty('com_id');
		$this->mFormProperties['delete_mode'] =& new XCube_StringProperty('delete_mode');
		$this->mFieldProperties['com_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['com_id']->setDependsByArray(array('required'));
		$this->mFieldProperties['com_id']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_COM_ID);
	}
	function load(&$obj)
	{
		$this->setVar('com_id', $obj->get('com_id'));
	}
	function update(&$obj)
	{
		$obj->setVar('com_id', $this->get('com_id'));
	}
}
?>
