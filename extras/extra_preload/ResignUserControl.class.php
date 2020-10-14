<?php
define("RESIGN_USER_GROUP_ID", 0);
class ResignUserControl extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		if (RESIGN_USER_GROUP_ID > 0) {
			$root =& XCube_Root::getSingleton();
			$delegateMgr =& $root->getDelegateManager();
			$delegateMgr->add('User_UserDeleteAction._doDelete',
				"ResignUserControl::resign",
				XCUBE_DELEGATE_PRIORITY_2);
		}
	}
	function resign(&$flag, &$controller, &$xoopsUser)
	{
		$handler =& xoops_gethandler('member');
		$groups = $handler->getGroupsByUser($xoopsUser->get('uid'));
		foreach ($groups as $group) {
			$handler->removeUserFromGroup($group, $xoopsUser->get('uid'));
		}
		$handler->addUserToGroup(RESIGN_USER_GROUP_ID, $xoopsUser->get('uid'));
		xoops_notification_deletebyuser($xoopsUser->get('uid'));
		XCube_DelegateUtils::call('Legacy.Event.UserDelete', new XCube_Ref($xoopsUser));
		$flag = true;
		$root =& XCube_Root::getSingleton();
		$_SESSION = array();
		$root->mSession->destroy(true);
		$handler =& xoops_gethandler('online');
		$handler->destroy($xoopsUser->get('uid'));
		xoops_notification_deletebyuser($xoopsUser->get('uid'));
		$langMgr =& $root->getLanguageManager();
		$langMgr->loadPageTypeMessageCatalog('user');
		$controller =& $root->getController();
		$controller->executeRedirect(XOOPS_URL, 3, _US_BEENDELED);
	}
}
?>
