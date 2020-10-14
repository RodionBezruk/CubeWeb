<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_ImagecategoryAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.ImagecategoryAdminDeleteForm.TOKEN" . $this->get('imgcat_id');
	}
	function prepare()
	{
		$this->mFormProperties['imgcat_id'] =& new XCube_IntProperty('imgcat_id');
		$this->mFieldProperties['imgcat_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['imgcat_id']->setDependsByArray(array('required'));
		$this->mFieldProperties['imgcat_id']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_IMGCAT_ID);
	}
	function load(&$obj)
	{
		$this->set('imgcat_id', $obj->get('imgcat_id'));
	}
	function update(&$obj)
	{
		$obj->set('imgcat_id', $this->get('imgcat_id'));
	}
}
?>
