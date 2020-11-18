<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class User_RanksListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.user.RanksSettingsForm.TOKEN";
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
		$this->mFormProperties['title'] =& new XCube_StringArrayProperty('title');
		$this->mFormProperties['min'] =& new XCube_IntArrayProperty('min');
		$this->mFormProperties['max'] =& new XCube_IntArrayProperty('max');
		$this->mFormProperties['delete']= & new XCube_BoolArrayProperty('delete');
		$this->mFormProperties['confirm'] =& new XCube_BoolProperty('confirm');
		$this->mFieldProperties['title'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['title']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['title']->addMessage('required', _MD_USER_ERROR_REQUIRED, _AD_USER_LANG_RANK_TITLE, '50');
		$this->mFieldProperties['title']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _AD_USER_LANG_RANK_TITLE, '50');
		$this->mFieldProperties['title']->addVar('maxlength', 50);
		$this->mFieldProperties['min'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['min']->setDependsByArray(array('required', 'min'));
		$this->mFieldProperties['min']->addMessage('required', _MD_USER_ERROR_REQUIRED, _AD_USER_LANG_RANK_MIN);
		$this->mFieldProperties['min']->addMessage('min', _AD_USER_ERROR_MIN, _AD_USER_LANG_RANK_MIN, 0);
		$this->mFieldProperties['min']->addVar('min', 0);
		$this->mFieldProperties['max'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['max']->setDependsByArray(array('required', 'min'));
		$this->mFieldProperties['max']->addMessage('required', _MD_USER_ERROR_REQUIRED, _AD_USER_LANG_RANK_MAX);
		$this->mFieldProperties['max']->addMessage('min', _AD_USER_ERROR_MIN, _AD_USER_LANG_RANK_MAX, 0);
		$this->mFieldProperties['max']->addVar('min', 0);
	}
}
?>
