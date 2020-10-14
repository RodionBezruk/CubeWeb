<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractListAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/CommentFilterForm.class.php";
class Legacy_CommentViewAction extends Legacy_Action
{
	var $mObject = null;
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$handler =& xoops_getmodulehandler('comment');
		$this->mObject =& $handler->get(xoops_getrequest('com_id'));
		if ($this->mObject == null) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$render)
	{
		$this->mObject->loadModule();
		$this->mObject->loadUser();
		$this->mObject->loadStatus();
		$render->setTemplateName("comment_view.html");
		$render->setAttribute('object', $this->mObject);
		$handler =& xoops_getmodulehandler('comment');
		$criteria =& new Criteria('com_pid', $this->mObject->get('com_id'));
		$children =& $handler->getObjects($criteria);
		if (count($children) > 0) {
			foreach (array_keys($children) as $key) {
				$children[$key]->loadModule();
				$children[$key]->loadUser();
			}
		}
		$render->setAttribute('children', $children);
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeForward('./index.php');
	}
}
?>
