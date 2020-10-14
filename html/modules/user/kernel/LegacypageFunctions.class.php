<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class User_LegacypageFunctions
{
	function userinfo()
	{
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('user');
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName("UserInfo");
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function edituser()
	{
		$actionName = "EditUser";
		switch (xoops_getrequest('op')) {
			case 'avatarform':
			case 'avatarupload':
				$actionName = "AvatarEdit";
				break;
			case 'avatarchoose':
				$actionName = "AvatarSelect";
				break;
		}
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('user');
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function register()
	{
		$root =& XCube_Root::getSingleton();
		$xoopsUser =& $root->mContext->mXoopsUser;
		if (is_object($xoopsUser)) {
			$root->mController->executeForward(XOOPS_URL);
		}
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('user');
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$actionName = "";
		$action = $root->mContext->mRequest->getRequest('action');
		if ($action != null && $action =="UserRegister") {
			$actionName = "UserRegister";
		}
		else {
			$actionName = $action != null ? "UserRegister_confirm" : "UserRegister";
		}
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function lostpass()
	{
		$root =& XCube_Root::getSingleton();
		$xoopsUser =& $root->mContext->mXoopsUser;
		if (is_object($xoopsUser)) {
			$root->mController->executeForward(XOOPS_URL);
		}
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('user');
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$root =& XCube_Root::getSingleton();
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName("LostPass");
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function user()
	{
		$root =& XCube_Root::getSingleton();
		$op = isset($_REQUEST['op']) ? trim(xoops_getrequest('op')) : "main";
		$xoopsUser =& $root->mContext->mXoopsUser;
		$actionName = "default";
		switch($op) {
			case "login":
				$root->mController->checkLogin();
				return;
			case "logout":
				$root->mController->logout();
				return;
			case "main":
				if (is_object($xoopsUser)) {
					$root->mController->executeForward(XOOPS_URL . "/userinfo.php?uid=" . $xoopsUser->get('uid'));
				}
				break;
			case "actv":
				$actionName = "UserActivate";
				break;
			case "delete":
				$actionName = "UserDelete";
				break;
		}
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('user');
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function checkLogin(&$xoopsUser)
	{
		if (is_object($xoopsUser)) {
			return;
		}
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		$userHandler =& xoops_getmodulehandler('users', 'user');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('uname', xoops_getrequest('uname')));
		$criteria->add(new Criteria('pass', md5(xoops_getrequest('pass'))));
		$userArr =& $userHandler->getObjects($criteria);
		if (count($userArr) != 1) {
			return;
		}
		if ($userArr[0]->get('level') == 0) {
			return;
		}
		$handler =& xoops_gethandler('user');
		$user =& $handler->get($userArr[0]->get('uid'));
		$xoopsUser = $user;
		$root->mSession->regenerate();
		$_SESSION = array();
		$_SESSION['xoopsUserId'] = $xoopsUser->get('uid');
		$_SESSION['xoopsUserGroups'] = $xoopsUser->getGroups();
	}
    function checkLoginSuccess(&$xoopsUser)
    {
		if (is_object($xoopsUser)) {
			$handler =& xoops_gethandler('user');
			$xoopsUser->set('last_login', time());
			$handler->insert($xoopsUser);
		}
	}
	function logout(&$successFlag, $xoopsUser)
	{
		$root =& XCube_Root::getSingleton();
		$xoopsConfig = $root->mContext->mXoopsConfig;
		$root->mLanguageManager->loadModuleMessageCatalog('user');
		$_SESSION = array();
		$root->mSession->destroy(true);
		if (is_object($xoopsUser)) {
			$onlineHandler =& xoops_gethandler('online');
			$onlineHandler->destroy($xoopsUser->get('uid'));
		}
		$successFlag = true;
    }
	function misc()
	{
		if (xoops_getrequest('type') != 'online') {
			return;
		}
		require_once XOOPS_MODULE_PATH . "/user/class/ActionFrame.class.php";
		$root =& XCube_Root::getSingleton();
		$root->mController->setupModuleContext('user');
		$actionName = "MiscOnline";
		$moduleRunner = new User_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->setDialogMode(true);
		$root->mController->execute();
		$root->mController->executeView();
	}
}
?>
