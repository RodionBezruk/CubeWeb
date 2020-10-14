<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Pm_ReadAction extends Pm_AbstractAction
{
	var $mPrivMessage = null;
	var $mSendUser = null;
	var $mPreviousMessage = null;
	var $mNextMessage = null;
	function getDefaultView(&$controller,&$xoopsUser)
	{
		$msg_id = intval(xoops_getrequest('msg_id'));
		$pmHandler =& xoops_gethandler('privmessage');
		$this->mPrivMessage =& $pmHandler->get($msg_id);
		if (!is_object($this->mPrivMessage)) {
			return PM_FRAME_VIEW_ERROR;
		}
		if ($this->mPrivMessage->getVar('to_userid') != $xoopsUser->getVar('uid')) {
			return PM_FRAME_VIEW_ERROR;
		}
		$this->mSendUser =& $this->mPrivMessage->getFromUser();
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('msg_id', $this->mPrivMessage->getVar('msg_id'), "<"));
		$criteria->add(new Criteria('to_userid', $xoopsUser->get('uid')));
		$criteria->setLimit(1);
		$criteria->setSort('msg_time');
		$criteria->setOrder('DESC');
		$t_objArr =& $pmHandler->getObjects($criteria);
		if (count($t_objArr) > 0 && is_object($t_objArr[0])) {
			$this->mPreviousMessage =& $t_objArr[0];
		}
		unset($t_objArr);
		unset($criteria);
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('msg_id', $this->mPrivMessage->getVar('msg_id'), ">"));
		$criteria->add(new Criteria('to_userid', $xoopsUser->get('uid')));
		$criteria->setLimit(1);
		$criteria->setSort('msg_time');
		$t_objArr =& $pmHandler->getObjects($criteria);
		if (count($t_objArr) > 0 && is_object($t_objArr[0])) {
			$this->mNextMessage =& $t_objArr[0];
		}
		if (!$this->mPrivMessage->isRead()) {
			$pmHandler->setRead($this->mPrivMessage);
		}
		return PM_FRAME_VIEW_INDEX;
	}
	function execute(&$controller, &$xoopsUser)
	{
		$controller->executeForward(XOOPS_URL . "/readpmsg.php?action=DeleteOne&msg_id=" . xoops_getrequest('msg_id'));
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect(XOOPS_URL . "/viewpmsg.php", 1, _MD_PM_ERROR_ACCESS);
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("readpmsg.html");
		$render->setAttribute("thisUser", $xoopsUser);
		if (is_object($this->mSendUser) && $this->mSendUser->isActive()) {
			$render->setAttribute("sendUser", $this->mSendUser);
		}
		$render->setAttribute("privMessage", $this->mPrivMessage);
		$render->setAttribute("previousMessage", $this->mPreviousMessage);
		$render->setAttribute("nextMessage", $this->mNextMessage);
		$render->setAttribute("anonymous", $controller->mRoot->mContext->getXoopsConfig('anonymous'));
	}
}
?>
