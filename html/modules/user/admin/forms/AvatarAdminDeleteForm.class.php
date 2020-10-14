<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class User_AvatarAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.user.AvatarAdminDeleteForm.TOKEN" . $this->get('avatar_id');
	}
	function prepare()
	{
		$this->mFormProperties['avatar_id'] =& new XCube_IntProperty('avatar_id');
		$this->mFieldProperties['avatar_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['avatar_id']->setDependsByArray(array('required'));
		$this->mFieldProperties['avatar_id']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_AVATAR_ID);
	}
	function load(&$obj)
	{
		$this->set('avatar_id', $obj->get('avatar_id'));
	}
	function update(&$obj)
	{
		$obj->setVar('avatar_id', $this->get('avatar_id'));
	}
}
?>
