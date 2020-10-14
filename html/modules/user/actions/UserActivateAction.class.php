<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/user/class/AbstractEditAction.class.php";
require_once XOOPS_MODULE_PATH . "/user/class/RegistMailBuilder.class.php";
class User_UserActivateAction extends User_AbstractEditAction
{
	function _getId()
	{
		return isset($_REQUEST['uid']) ? intval(xoops_getrequest('uid')) : 0;
	}
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('users', 'user');
		return $handler;
	}
	function isEnableCreate()
	{
		return false;
	}
	function isSecure()
	{
		return false;
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		if ((!isset($_REQUEST['actkey'])) || (!$this->mObject)) {
			$controller->executeForward(XOOPS_URL . '/');
		}
		if ($this->mObject->get('actkey') != xoops_getrequest('actkey')) {
			$controller->executeRedirect(XOOPS_URL . '/', 3, _MD_USER_MESSAGE_ACTKEYNOT);
		} 
		if ($this->mObject->get('level') > 0) {
			$controller->executeRedirect(XOOPS_URL . '/user.php', 3, _MD_USER_MESSAGE_ACONTACT);
		}
		$this->mObject->set('level', '1');
		$this->mObjectHandler->insert($this->mObject, true);
		if ($this->mConfig['activation_type'] == 2) {
			$builder =& new User_RegistAdminCommitMailBuilder();
			$director =& new User_UserRegistMailDirector($builder, $this->mObject, $controller->mRoot->mContext->getXoopsConfig(), $this->mConfig);
			$director->contruct();
			$mailer=&$builder->getResult();
			if ($mailer->send()) {
				$controller->executeRedirect(XOOPS_URL . '/', 5, sprintf(_MD_USER_MESSAGE_ACTVMAILOK, $this->mObject->get('uname')));
			} else {
				$controller->executeRedirect(XOOPS_URL . '/', 5, sprintf(_MD_USER_MESSAGE_ACTVMAILNG, $this->mObject->get('uname')));
			}
		} else {
			$controller->executeRedirect(XOOPS_URL . '/user.php', 5, _MD_USER_MESSAGE_ACTLOGIN);
		}
	}
}
?>
