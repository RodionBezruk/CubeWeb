<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class Legacy_ModuleListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.legacy.ModuleSettingsForm.TOKEN";
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
		$this->mFormProperties['name']=new XCube_StringArrayProperty('name');
		$this->mFormProperties['weight']=new XCube_IntArrayProperty('weight');
		$this->mFormProperties['isactive']=new XCube_BoolArrayProperty('isactive');
		$this->mFieldProperties['name']=new XCube_FieldProperty($this);
		$this->mFieldProperties['name']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['name']->addMessage("required",_MD_LEGACY_ERROR_REQUIRED,_AD_LEGACY_LANG_NAME,"140");
		$this->mFieldProperties['name']->addMessage("maxlength",_MD_LEGACY_ERROR_MAXLENGTH,_AD_LEGACY_LANG_NAME,"140");
		$this->mFieldProperties['name']->addVar("maxlength",140);
		$this->mFieldProperties['weight']=new XCube_FieldProperty($this);
		$this->mFieldProperties['weight']->setDependsByArray(array('required','min'));
		$this->mFieldProperties['weight']->addMessage("min",_AD_LEGACY_ERROR_MIN,_AD_LEGACY_LANG_WEIGHT,"0");
		$this->mFieldProperties['weight']->addVar("min",0);
	}
}
?>
