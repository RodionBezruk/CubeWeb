<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class User_UserListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.user.UserSettingsForm.TOKEN";
		}
		else {
			return null;
		}
	}
	function getTokenErrorMessage()
	{
		return null;
	}
	function prepare()
	{
		$this->mFormProperties['level']= & new XCube_IntArrayProperty('level');
		$this->mFormProperties['posts']= & new XCube_IntArrayProperty('posts');
		$this->mFormProperties['delete']= & new XCube_BoolArrayProperty('delete');
		$this->mFormProperties['confirm'] =& new XCube_BoolProperty('confirm');
		$this->mFieldProperties['level']= & new XCube_FieldProperty($this);
		$this->mFieldProperties['level']->setDependsByArray(array('required','min'));
		$this->mFieldProperties['level']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_LEVEL);
		$this->mFieldProperties['level']->addMessage("min",_AD_USER_ERROR_MIN,_MD_USER_LANG_LEVEL,"0");
		$this->mFieldProperties['level']->addVar("min",0);
		$this->mFieldProperties['posts']= & new XCube_FieldProperty($this);
		$this->mFieldProperties['posts']->setDependsByArray(array('required','min'));
		$this->mFieldProperties['posts']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_POSTS);
		$this->mFieldProperties['posts']->addMessage("min",_AD_USER_ERROR_MIN,_MD_USER_LANG_POSTS,"0");
		$this->mFieldProperties['posts']->addVar("min",0);
	}
}
?>
