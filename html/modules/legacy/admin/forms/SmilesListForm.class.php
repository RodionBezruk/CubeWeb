<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class Legacy_SmilesListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.legacy.SmilesSettingsForm.TOKEN";
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
		$this->mFormProperties['code']=& new XCube_StringArrayProperty('code');
		$this->mFormProperties['emotion']=& new XCube_StringArrayProperty('emotion');
		$this->mFormProperties['display']=& new XCube_BoolArrayProperty('display');
		$this->mFormProperties['delete']=& new XCube_BoolArrayProperty('delete');
		$this->mFormProperties['confirm'] =& new XCube_BoolProperty('confirm');
		$this->mFieldProperties['code']=& new XCube_FieldProperty($this);
		$this->mFieldProperties['code']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['code']->addMessage("required",_MD_LEGACY_ERROR_REQUIRED,_MD_LEGACY_LANG_CODE,"50");
		$this->mFieldProperties['code']->addMessage("maxlength",_MD_LEGACY_ERROR_MAXLENGTH,_MD_LEGACY_LANG_CODE,"50");
		$this->mFieldProperties['code']->addVar("maxlength",50);
		$this->mFieldProperties['emotion']=& new XCube_FieldProperty($this);
		$this->mFieldProperties['emotion']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['emotion']->addMessage("required",_MD_LEGACY_ERROR_REQUIRED,_MD_LEGACY_LANG_EMOTION,"75");
		$this->mFieldProperties['emotion']->addMessage("maxlength",_MD_LEGACY_ERROR_MAXLENGTH,_MD_LEGACY_LANG_EMOTION,"75");
		$this->mFieldProperties['emotion']->addVar("maxlength",75);
	}
}
?>
