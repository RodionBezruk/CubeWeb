<?php
function smarty_modifier_xoops_user($uid, $key)
{
	$handler=&xoops_gethandler('member');
	$user=&$handler->getUser(intval($uid));
	if(is_object($user)&&$user->isActive()) {
		return $user->getShow($key);
	}
	return null;
}
?>
