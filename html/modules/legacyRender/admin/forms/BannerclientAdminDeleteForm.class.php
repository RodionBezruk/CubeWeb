<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class LegacyRender_BannerclientAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacyRender.BannerclientAdminDeleteForm.TOKEN" . $this->get('cid');
	}
	function prepare()
	{
		$this->mFormProperties['cid'] =& new XCube_IntProperty('cid');
		$this->mFieldProperties['cid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['cid']->setDependsByArray(array('required'));
		$this->mFieldProperties['cid']->addMessage('required', _AD_LEGACYRENDER_ERROR_REQUIRED, _AD_LEGACYRENDER_LANG_CID);
	}
	function load(&$obj)
	{
		$this->set('cid', $obj->get('cid'));
	}
	function update(&$obj)
	{
		$obj->set('cid', $this->get('cid'));
	}
}
?>
