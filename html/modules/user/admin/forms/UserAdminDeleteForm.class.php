<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class User_UserAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.user.UserAdminDeleteForm.TOKEN" . $this->get('uid');
	}
	function prepare()
	{
		$this->mFormProperties['uid'] =& new XCube_IntProperty('uid');
		$this->mFieldProperties['uid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['uid']->setDependsByArray(array('required'));
		$this->mFieldProperties['uid']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_UID);
	}
	function load(&$obj)
	{
		$this->set('uid', $obj->get('uid'));
	}
	function update(&$obj)
	{
		$obj->setVar('uid', $this->get('uid'));
	}
}
?>
