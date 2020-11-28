<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractListAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/CommentFilterForm.class.php";
class Legacy_CommentListAction extends Legacy_AbstractListAction
{
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('comment');
		return $handler;
	}
	function &_getFilterForm()
	{
		$filter =& new Legacy_CommentFilterForm($this->_getPageNavi(), $this->_getHandler());
		return $filter;
	}
	function _getBaseUrl()
	{
		return "./index.php?action=CommentList";
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		foreach (array_keys($this->mObjects) as $key) {
			$this->mObjects[$key]->loadModule();
			$this->mObjects[$key]->loadUser();
			$this->mObjects[$key]->loadStatus();
		}
		$moduleArr = array();
		$handler =& xoops_getmodulehandler('comment');
		$modIds = $handler->getModuleIds();
		$moduleHandler =& xoops_gethandler('module');
		foreach ($modIds as $mid) {
			$module =& $moduleHandler->get($mid);
			if (is_object($module)) {
				$moduleArr[] =& $module;
			}
			unset ($module);
		}
		$statusArr = array();
		$statusHandler =& xoops_getmodulehandler('commentstatus');
		$statusArr =& $statusHandler->getObjects();
		$render->setTemplateName("comment_list.html");
		$render->setAttribute("objects", $this->mObjects);
		$render->setAttribute("pageNavi", $this->mFilter->mNavi);
		$render->setAttribute("moduleArr", $moduleArr);
		$render->setAttribute("statusArr", $statusArr);
	}
}
?>
