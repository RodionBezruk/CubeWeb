<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_MiscSslloginAction extends Legacy_Action
{
	function execute(&$controller, &$xoopsUser)
	{
		return LEGACY_FRAME_VIEW_INDEX;
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$root =& $controller->mRoot;
		$render->setTemplateName("legacy_misc_ssllogin.html");
		$render->setAttribute("message", XCube_Utils::formatMessage(_MD_LEGACY_MESSAGE_LOGIN_SUCCESS, $xoopsUser->get('uname')));
	}
}
?>
