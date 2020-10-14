<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class LegacyRender_BannerfinishAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacyRender.BannerfinishAdminDeleteForm.TOKEN" . $this->get('bid');
	}
	function prepare()
	{
		$this->mFormProperties['bid'] =& new XCube_IntProperty('bid');
		$this->mFieldProperties['bid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['bid']->setDependsByArray(array('required'));
		$this->mFieldProperties['bid']->addMessage('required', _AD_LEGACYRENDER_ERROR_REQUIRED, _AD_LEGACYRENDER_LANG_BID);
	}
	function load(&$obj)
	{
		$this->set('bid', $obj->get('bid'));
	}
	function update(&$obj)
	{
		$obj->set('bid', $this->get('bid'));
	}
}
?>
