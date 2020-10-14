<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacyRender/admin/forms/TplsetEditForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class LegacyRender_TplsetCloneForm extends LegacyRender_TplsetEditForm
{
	function getTokenName()
	{
		return "module.legacyRender.TplsetCloneForm.TOKEN" . $this->get('tplset_id');
	}
	function prepare()
	{
		parent::prepare();
		$this->mFormProperties['tplset_id'] =& new XCube_IntProperty('tplset_id');
		$this->mFormProperties['tplset_name'] =& new XCube_StringProperty('tplset_name');
		$this->mFormProperties['tplset_credits'] =& new XCube_TextProperty('tplset_credits');
		$this->mFieldProperties['tplset_name'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['tplset_name']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['tplset_name']->addMessage('required', _AD_LEGACYRENDER_ERROR_REQUIRED, _AD_LEGACYRENDER_LANG_TPLSET_NAME, '50');
		$this->mFieldProperties['tplset_name']->addMessage('maxlength', _AD_LEGACYRENDER_ERROR_MAXLENGTH, _AD_LEGACYRENDER_LANG_TPLSET_NAME, '50');
		$this->mFieldProperties['tplset_name']->addVar('maxlength', 50);
	}
	function validateTplset_name()
	{
		$handler = xoops_getmodulehandler('tplset');
		if ($this->get('tplset_name') != null) {
			if ($handler->getCount(new Criteria('tplset_name', $this->get('tplset_name'))) > 0) {
				$this->addErrorMessage(_AD_LEGACYRENDER_ERROR_UNIQUE_NAME);
			}
			if (!preg_match("/^[a-z0-9\_]+$/i", $this->get('tplset_name'))) {
				$this->addErrorMessage(_AD_LEGACYRENDER_ERROR_TPLSET_NAME_RULE);
			}
		}
	}
	function load(&$obj)
	{
		parent::load($obj);
		$this->set('tplset_name', $obj->get('tplset_name'));
	}
	function update(&$obj)
	{
		parent::update($obj);
		$obj->set('tplset_name', $this->get('tplset_name'));
		$obj->set('tplset_id', 0);
	}
}
?>
