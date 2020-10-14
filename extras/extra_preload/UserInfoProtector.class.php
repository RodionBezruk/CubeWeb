<?php
class UserInfoProtector extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		$root =& XCube_Root::getSingleton();
		$delegateMgr =& $root->getDelegateManager();
		$delegateMgr->add('Legacypage.Userinfo.Access',
			"UserInfoProtector::rightCheck",
			XCUBE_DELEGATE_PRIORITY_2);
	}
	function rightCheck()
	{
		$root =& XCube_Root::getSingleton();
		if (!$root->mContext->mUser->mIdentity->isAuthenticated()) {
			$root->mController->executeForward(XOOPS_URL);
		}
		$uid = $root->mContext->mXoopsUser->get('uid');
		$requestUid = $root->mContext->getRequest('uid');
		if ($uid != null && $uid != $requestUid) {
			$root->mController->executeForward(XOOPS_URL);
		}
	}
}
?>
