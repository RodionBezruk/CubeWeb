<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/BlockEditForm.class.php";
class Legacy_CustomBlockEditForm extends Legacy_BlockEditForm
{
	function getTokenName()
	{
		return "module.legacy.CustomBlockEditForm.TOKEN" . $this->get('bid');
	}
	function prepare()
	{
		parent::prepare();
		$this->mFormProperties['content'] =& new XCube_TextProperty('content');
		$this->mFormProperties['c_type'] =& new XCube_StringProperty('c_type');
		$this->mFieldProperties['content'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['content']->setDependsByArray(array('required'));
		$this->mFieldProperties['content']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_CONTENT);
		$this->mFieldProperties['c_type'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['c_type']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['c_type']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _AD_LEGACY_LANG_C_TYPE, '1');
		$this->mFieldProperties['c_type']->addMessage('maxlength', _MD_LEGACY_ERROR_MAXLENGTH, _AD_LEGACY_LANG_C_TYPE, '1');
		$this->mFieldProperties['c_type']->addVar('maxlength', '1');
	}
	function load(&$obj)
	{
		parent::load($obj);
		$this->set('content', $obj->get('content'));
		$this->set('c_type', $obj->get('c_type'));
	}
	function update(&$obj)
	{
		parent::update($obj);
		$obj->set('content', $this->get('content'));
		$obj->set('block_type', 'C');
		$obj->set('c_type', $this->get('c_type'));
		$obj->set('visible', 1);
		$obj->set('isactive', 1);
		switch ($this->get('c_type')) {
			case 'H':
				$obj->set('name', _AD_LEGACY_LANG_CUSTOM_HTML);
				break;
			case 'P':
				$obj->set('name', _AD_LEGACY_LANG_CUSTOM_PHP);
				break;
			case 'S':
				$obj->set('name', _AD_LEGACY_LANG_CUSTOM_WITH_SMILIES);
				break;
			case 'T':
				$obj->set('name', _AD_LEGACY_LANG_CUSTOM_WITHOUT_SMILIES);
				break;
		}
	}
}
?>
