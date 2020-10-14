<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/user/class/AbstractEditAction.class.php";
require_once XOOPS_MODULE_PATH . "/user/forms/EditUserForm.class.php";
define ('USER_COOKIE_KEEP_TIME', 31536000);
class User_EditUserAction extends User_AbstractEditAction
{
	var $mUserCookie = "";
	function prepare(&$controller, &$xoopsUser, $moduleConfig)
	{
		parent::prepare($controller, $xoopsUser, $moduleConfig);
		$this->mUserCookie = $moduleConfig['usercookie'];
	}
	function _getId()
	{
		$root =& XCube_Root::getSingleton();
		$uid = is_object($root->mContext->mXoopsUser) ? $root->mContext->mXoopsUser->get('uid') : 0;
		return isset($_REQUEST['uid']) ? intval(xoops_getrequest('uid')) : $uid;
	}
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('users', 'user');
		return $handler;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new User_EditUserForm($this->mConfig);
		$this->mActionForm->prepare();
	}
	function isEnableCreate()
	{
		return false;
	}
	function isSecure()
	{
		return true;
	}
	function hasPermission(&$controller, &$xoopsUser, $moduleConfig)
	{
		if (!is_object($this->mObject)) {
			return false;
		}
		if ($controller->mRoot->mContext->mUser->isInRole('Module.user.Admin')) {
			return true;
		}
		return ($this->mObject->get('uid') == $xoopsUser->get('uid'));
	}
	function _doExecute()
	{
		if ($this->mObjectHandler->insert($this->mObject)) {
			if ($this->mActionForm->get('usercookie')) {
				setcookie($this->mUserCookie, $this->mObject->getShow('uname'), time() + USER_COOKIE_KEEP_TIME);
			}
			else {
				setcookie($this->mUserCookie);
			}
			return true;
		}
		else {
			return false;
		}
	}
	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("user_edituser.html");
		$render->setAttribute("actionForm", $this->mActionForm);
		$render->setAttribute("thisUser", $this->mObject);
		$render->setAttribute("currentUser", $xoopsUser);
		$render->setAttribute("allow_chgmail", $this->mConfig['allow_chgmail']);
		$handler =& xoops_gethandler('timezone');
		$timezoneArr =& $handler->getObjects();
		$render->setAttribute('timezones', $timezoneArr);
		$umodeOptions = array("nest" => _NESTED, "flat" => _FLAT, "thread" => _THREADED);
		$render->setAttribute('umodeOptions', $umodeOptions);
		$uorderOptions = array(0 => _OLDESTFIRST, 1 => _NEWESTFIRST);
		$render->setAttribute('uorderOptions', $uorderOptions);
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadPageTypeMessageCatalog('notification');
		require_once XOOPS_ROOT_PATH . "/include/notification_constants.php";
		$root =& XCube_Root::getSingleton();
		$service =& $root->mServiceManager->getService('privateMessage');
		$methodOptions = array();
		$methodOptions[XOOPS_NOTIFICATION_METHOD_DISABLE] = _NOT_METHOD_DISABLE;
		if ($service != null) {
			$methodOptions[XOOPS_NOTIFICATION_METHOD_PM] = _NOT_METHOD_PM;
		}
		$methodOptions[XOOPS_NOTIFICATION_METHOD_EMAIL] = _NOT_METHOD_EMAIL;
		$render->setAttribute('notify_methodOptions', $methodOptions);
		$modeOptions = array(
			XOOPS_NOTIFICATION_MODE_SENDALWAYS => _NOT_MODE_SENDALWAYS,
			XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE => _NOT_MODE_SENDONCE,
			XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT => _NOT_MODE_SENDONCEPERLOGIN
		);
		$render->setAttribute('notify_modeOptions', $modeOptions);
	}
	function executeViewSuccess(&$controller,&$xoopsUser,&$render)
	{
		$controller->executeForward(XOOPS_URL . '/userinfo.php?uid=' . $this->mObject->getShow('uid'));
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect(XOOPS_URL . '/', 1, _MD_USER_ERROR_DBUPDATE_FAILED);
	}
}
?>
