<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractListAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/ImagecategoryFilterForm.class.php";
class Legacy_ImagecategoryListAction extends Legacy_AbstractListAction
{
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('imagecategory');
		return $handler;
	}
	function &_getFilterForm()
	{
		$filter =& new Legacy_ImagecategoryFilterForm($this->_getPageNavi(), $this->_getHandler());
		return $filter;
	}
	function _getBaseUrl()
	{
		return "./index.php?action=ImagecategoryList";
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("imagecategory_list.html");
		$render->setAttribute("objects", $this->mObjects);
		$render->setAttribute("pageNavi", $this->mFilter->mNavi);
	}
}
?>
