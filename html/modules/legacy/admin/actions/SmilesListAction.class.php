<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractListAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/SmilesFilterForm.class.php";
class Legacy_SmilesListAction extends Legacy_AbstractListAction
{
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('smiles');
		return $handler;
	}
	function &_getFilterForm()
	{
		$filter =& new Legacy_SmilesFilterForm($this->_getPageNavi(), $this->_getHandler());
		return $filter;
	}
	function _getBaseUrl()
	{
		return "./index.php?action=SmilesList";
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("smiles_list.html");
		$render->setAttribute("objects", $this->mObjects);
		$render->setAttribute("pageNavi", $this->mFilter->mNavi);
	}
}
?>
