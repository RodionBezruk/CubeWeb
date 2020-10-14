<?php
function b_legacy_usermenu_show()
{
    $root =& XCube_Root::getSingleton();
    $xoopsUser =& $root->mController->mRoot->mContext->mXoopsUser;
    if (is_object($xoopsUser)) {
        $block = array();
        $block['uid'] = $xoopsUser->get('uid');
		$block['flagShowInbox'] = false;
		$url = null;
		$service =& $root->mServiceManager->getService('privateMessage');
		if ($service != null) {
			$client =& $root->mServiceManager->createClient($service);
			$url = $client->call('getPmInboxUrl', array('uid' => $xoopsUser->get('uid')));
			if ($url != null) {
				$block['inbox_url'] = $url;
				$block['new_messages'] = $client->call('getCountUnreadPM', array('uid' => $xoopsUser->get('uid')));
				$block['flagShowInbox']=true;
			}
		}
		$block['show_adminlink'] = $root->mContext->mUser->isInRole('Site.Administrator');
        return $block;
    }
    return false;
}
?>
