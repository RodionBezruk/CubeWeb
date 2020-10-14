<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_PageNavigator.class.php";
require_once XOOPS_MODULE_PATH . "/pm/forms/PmDeleteForm.class.php";
class Pm_DefaultAction extends Pm_AbstractAction
{
	var $mActionForm = null;
	var $mPmObjects = array();
	var $mPageNavi = null;
	function prepare(&$controller, &$xoopsUser, &$moduleConfig)
	{
		$this->mActionForm =& new Pm_PmDeleteForm();
		$this->mActionForm->prepare();
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$pmHandler =& xoops_gethandler('privmessage');
		$total = $pmHandler->getCountByFromUid($xoopsUser->uid());
		$this->mPageNavi =& new XCube_PageNavigator(XOOPS_URL . "/viewpmsg.php", XCUBE_PAGENAVI_START);
		$this->mPageNavi->setTotalItems($total);
		$this->mPageNavi->fetch();
		$this->mPmObjects =& $pmHandler->getObjectsByFromUid($xoopsUser->uid(), $this->mPageNavi->getStart());
		return PM_FRAME_VIEW_INDEX;
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("viewpmsg.html");
		$render->setAttribute("pmObjects", $this->mPmObjects);
		$render->setAttribute("total_messages", count($this->mPmObjects));
		$render->setAttribute("currentUser", $xoopsUser);
		$render->setAttribute("anonymous", $controller->mRoot->mContext->getXoopsConfig('anonymous'));
		$render->setAttribute("pageNavi", $this->mPageNavi);
		$render->setAttribute("actionForm", $this->mActionForm);
	}
}
?>
