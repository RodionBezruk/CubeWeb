<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_CommentListForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			return "module.legacy.CommentSettingsForm.TOKEN";
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
		$this->mFormProperties['status'] =& new XCube_IntArrayProperty('status');
		$this->mFormProperties['delete']= & new XCube_BoolArrayProperty('delete');
		$this->mFormProperties['confirm'] =& new XCube_BoolProperty('confirm');
		$this->mFieldProperties['status'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['status']->setDependsByArray(array('required','objectExist'));
		$this->mFieldProperties['status']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_COM_STATUS);
		$this->mFieldProperties['status']->addMessage('objectExist', _AD_LEGACY_ERROR_OBJECTEXIST, _AD_LEGACY_LANG_COM_STATUS);
		$this->mFieldProperties['status']->addVar('handler', 'commentstatus');
		$this->mFieldProperties['status']->addVar('module', 'legacy');
	}
}
?>
