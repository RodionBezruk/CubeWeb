<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
class Legacy_ImageAdminDeleteForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.ImageAdminDeleteForm.TOKEN" . $this->get('image_id');
	}
	function prepare()
	{
		$this->mFormProperties['image_id'] =& new XCube_IntProperty('image_id');
		$this->mFieldProperties['image_id'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['image_id']->setDependsByArray(array('required'));
		$this->mFieldProperties['image_id']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_IMAGE_ID);
	}
	function load(&$obj)
	{
		$this->set('image_id', $obj->get('image_id'));
	}
	function update(&$obj)
	{
		$obj->set('image_id', $this->get('image_id'));
	}
}
?>
