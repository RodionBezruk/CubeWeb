<?php
function smarty_modifier_xoops_user_avatarize($uid)
{
	$handler =& xoops_gethandler('user');
	$user =& $handler->get(intval($uid));
	if (is_object($user) && $user->isActive() && ($user->get('user_avatar') != "blank.gif")) {
		if (file_exists(XOOPS_UPLOAD_PATH . "/" . $user->get('user_avatar'))) {
			return XOOPS_UPLOAD_URL . "/" . $user->getShow('user_avatar');
		}
	}
	return XOOPS_URL . "/modules/user/images/no_avatar.gif";
}
?>
