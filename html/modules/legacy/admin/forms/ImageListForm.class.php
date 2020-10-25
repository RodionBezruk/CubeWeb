<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class Legacy_ImageListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.legacy.ImageSettingsForm.TOKEN";
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
		$this->mFormProperties['nicename']=& new XCube_StringArrayProperty('nicename');
		$this->mFormProperties['weight']=& new XCube_IntArrayProperty('weight');
		$this->mFormProperties['display']=& new XCube_BoolArrayProperty('display');
		$this->mFormProperties['delete']=& new XCube_BoolArrayProperty('delete');
		$this->mFormProperties['confirm'] =& new XCube_BoolProperty('confirm');
		$this->mFieldProperties['nicename'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['nicename']->setDependsByArray(array('required'));
		$this->mFieldProperties['nicename']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_IMAGE_NICENAME);
		$this->mFieldProperties['weight'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['weight']->setDependsByArray(array('required'));
		$this->mFieldProperties['weight']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_IMAGE_WEIGHT);
	}
}
?>
