<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class Legacy_ActionSearchForm extends XCube_ActionForm 
{
	var $mState = null;
	function prepare()
	{
		$this->mFormProperties['keywords']=new XCube_StringProperty('keywords');
		$this->mFieldProperties['keywords']=new XCube_FieldProperty($this);
		$this->mFieldProperties['keywords']->setDependsByArray(array('required'));
		$this->mFieldProperties['keywords']->addMessage("required",_AD_LEGACY_ERROR_SEARCH_REQUIRED);
	}
	function fetch()
	{
		parent::fetch();
		$this->set('keywords', trim($this->get('keywords')));
	}
}	
