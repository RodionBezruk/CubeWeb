<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/user/forms/LostPassEditForm.class.php";
require_once XOOPS_MODULE_PATH . "/user/class/LostPassMailBuilder.class.php";
class User_LostPassAction extends User_Action
{
	var $mActionForm = null;
	function prepare(&$controller, &$xoopsUser, $moduleConfig)
	{
		$this->mActionForm =& new User_LostPassEditForm();
		$this->mActionForm->prepare();
	}
	function isSecure()
	{
		return false;
	}
	function hasPermission(&$controller, &$xoopsUser, $moduleConfig)
	{
		return !$controller->mRoot->mContext->mUser->mIdentity->isAuthenticated();
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$root =& XCube_Root::getSingleton();
		$code = $root->mContext->mRequest->getRequest('code');	
		$email = $root->mContext->mRequest->getRequest('email');	
		if (strlen($code) == 0 || strlen($email) == 0) {
			return USER_FRAME_VIEW_INPUT;
		} else {
			return $this->_updatePassword($controller);
		}
	}
	function _updatePassword(&$controller) {
		$this->mActionForm->fetch();
		$userHandler =& xoops_gethandler('user');
		$criteria =& new CriteriaCompo(new Criteria('email', $this->mActionForm->get('email')));
		$criteria->add(new Criteria('pass', $this->mActionForm->get('code'), '=', '', 'LEFT(%s, 5)'));
		$lostUserArr =& $userHandler->getObjects($criteria);
		if (is_array($lostUserArr) && count($lostUserArr) > 0) {
			$lostUser =& $lostUserArr[0];
		}
		else {
			return USER_FRAME_VIEW_ERROR;
		}
		$newpass = xoops_makepass();
		$extraVars['newpass'] = $newpass;
		$builder =& new User_LostPass2MailBuilder();
		$director =& new User_LostPassMailDirector($builder, $lostUser, $controller->mRoot->mContext->getXoopsConfig(), $extraVars);
		$director->contruct();
		$xoopsMailer =& $builder->getResult();
		if (!$xoopsMailer->send()) {
			return USER_FRAME_VIEW_ERROR;
		}
		$lostUser->set('pass',md5($newpass), true);
		$userHandler->insert($lostUser, true);
		return USER_FRAME_VIEW_SUCCESS;
	}
	function execute(&$controller, &$xoopsUser)	
	{
		$this->mActionForm->fetch();
		$this->mActionForm->validate();
		if ($this->mActionForm->hasError()) {
			return USER_FRAME_VIEW_INPUT;
		}
		$userHandler =& xoops_gethandler('user');
		$lostUserArr =& $userHandler->getObjects(new Criteria('email', $this->mActionForm->get('email')));
		if (is_array($lostUserArr) && count($lostUserArr) > 0) {
			$lostUser =& $lostUserArr[0];
		}
		else {
			return USER_FRAME_VIEW_ERROR;
		}
		$builder =& new User_LostPass1MailBuilder();
		$director =& new User_LostPassMailDirector($builder, $lostUser, $controller->mRoot->mContext->getXoopsConfig());
		$director->contruct();
		$xoopsMailer =& $builder->getResult();
		if (!$xoopsMailer->send()) {
			return USER_FRAME_VIEW_ERROR;
		}
		return USER_FRAME_VIEW_SUCCESS;
	}
	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("user_lostpass.html");
		$render->setAttribute("actionForm", $this->mActionForm);
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect(XOOPS_URL . '/', 3, _MD_USER_MESSAGE_SEND_PASSWORD);
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect(XOOPS_URL . '/', 3, _MD_USER_ERROR_SEND_MAIL);
	}
}
?>
