<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_BlockListForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.BlockListForm.TOKEN";
	}
	function prepare()
	{
		$this->mFormProperties['weight'] =& new XCube_IntArrayProperty('weight');
		$this->mFormProperties['side'] =& new XCube_IntArrayProperty('side');
		$this->mFormProperties['bcachetime'] =& new XCube_IntArrayProperty('bcachetime');
		$this->mFieldProperties['weight'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['weight']->setDependsByArray(array('required','intRange'));
		$this->mFieldProperties['weight']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_WEIGHT);
		$this->mFieldProperties['weight']->addMessage('intRange', _AD_LEGACY_ERROR_INTRANGE, _AD_LEGACY_LANG_WEIGHT);
		$this->mFieldProperties['weight']->addVar('min', '0');
		$this->mFieldProperties['weight']->addVar('max', '65535');
		$this->mFieldProperties['side'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['side']->setDependsByArray(array('required','objectExist'));
		$this->mFieldProperties['side']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_SIDE);
		$this->mFieldProperties['side']->addMessage('objectExist', _AD_LEGACY_ERROR_OBJECTEXIST, _AD_LEGACY_LANG_SIDE);
		$this->mFieldProperties['side']->addVar('handler', 'columnside');
		$this->mFieldProperties['side']->addVar('module', 'legacy');
		$this->mFieldProperties['bcachetime'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['bcachetime']->setDependsByArray(array('required','objectExist'));
		$this->mFieldProperties['bcachetime']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_BCACHETIME);
		$this->mFieldProperties['bcachetime']->addMessage('objectExist', _AD_LEGACY_ERROR_OBJECTEXIST, _AD_LEGACY_LANG_BCACHETIME);
		$this->mFieldProperties['bcachetime']->addVar('handler', 'cachetime');
	}
}
?>
