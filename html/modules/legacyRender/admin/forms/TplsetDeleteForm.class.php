<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class LegacyRender_TplsetDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacyRender.TplsetDeleteForm.TOKEN" . $this->get('tplset_id');
	}
	function prepare()
	{
		$this->mFormProperties['tplset_id'] =& new XCube_IntProperty('tplset_id');
		$this->mFieldProperties['tplset_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['tplset_id']->setDependsByArray(array('required'));
		$this->mFieldProperties['tplset_id']->addMessage('required', _AD_LEGACYRENDER_ERROR_REQUIRED, _AD_LEGACYRENDER_LANG_TPLSET_ID);
	}
	function load(&$obj)
	{
		$this->set('tplset_id', $obj->get('tplset_id'));
	}
	function update(&$obj)
	{
		$obj->set('tplset_id', $this->get('tplset_id'));
	}
}
?>
