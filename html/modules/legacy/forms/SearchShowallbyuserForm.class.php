<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/Legacy_Validator.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/forms/SearchShowallForm.class.php";
class Legacy_SearchShowallbyuserForm extends Legacy_SearchShowallForm
{
	function prepare()
	{
		parent::prepare();
		$this->mFormProperties['uid'] =& new XCube_IntProperty('uid');
		$this->mFormProperties['mid'] =& new XCube_IntProperty('mid');
		$this->mFormProperties['start'] =& new XCube_IntProperty('start');
		$this->mFieldProperties['uid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['uid']->setDependsByArray(array('required'));
		$this->mFieldProperties['uid']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_UID);
		$this->mFieldProperties['mid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['mid']->setDependsByArray(array('required'));
		$this->mFieldProperties['mid']->addMessage('required', _MD_LEGACY_ERROR_REQUIRED, _MD_LEGACY_LANG_MID);
	}
	function update(&$params)
	{
		$params['uid'] = $this->get('uid');
		$params['start'] = $this->get('start');
		if (defined("LEGACY_SEARCH_SHOWALL_MAXHIT")) {
			$params['maxhit'] = LEGACY_SEARCH_SHOWALL_MAXHIT;
		}
		else {
			$params['maxhit'] = 20;
		}
	}
}
?>