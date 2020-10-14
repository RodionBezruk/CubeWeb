<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class User_UserRegistMailDirector
{
	var $mBuilder;
	var $mUser;
	var $mXoopsConfig;
	var $mUserConfig;
	function User_UserRegistMailDirector(&$builder, &$user, $xoopsConfig, $userConfig)
	{
		$this->mBuilder =& $builder;
		$this->mUser =& $user;
		$this->mXoopsConfig =$xoopsConfig;
		$this->mUserConfig = $userConfig;
	}
	function contruct()
	{
		$this->mBuilder->setTemplate();
		$this->mBuilder->setToUsers($this->mUser, $this->mUserConfig);
		$this->mBuilder->setFromEmail($this->mXoopsConfig);
		$this->mBuilder->setSubject($this->mUser, $this->mXoopsConfig);
		$this->mBuilder->setBody($this->mUser, $this->mXoopsConfig);
	}
}
class User_RegistUserActivateMailBuilder
{
	var $mMailer;
	function User_RegistUserActivateMailBuilder()
	{
		$this->mMailer =& getMailer();
		$this->mMailer->useMail();
	}
	function setTemplate()
	{
		$root=&XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$this->mMailer->setTemplateDir(XOOPS_ROOT_PATH . '/modules/user/language/' . $language . '/mail_template/');
		$this->mMailer->setTemplate('register.tpl');
	}
	function setToUsers($user, $userConfig)
	{
		$this->mMailer->setToUsers($user);
	}
	function setFromEmail($xoopsConfig)
	{
		$this->mMailer->setFromEmail($xoopsConfig['adminmail']);
		$this->mMailer->setFromName($xoopsConfig['sitename']);
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(@sprintf(_MD_USER_LANG_USERKEYFOR, $user->getShow('uname')));
	}
	function setBody($user,$xoopsConfig)
	{
		$this->mMailer->assign('SITENAME', $xoopsConfig['sitename']);
		$this->mMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
		$this->mMailer->assign('SITEURL', XOOPS_URL . '/');
		$this->mMailer->assign('USERACTLINK', XOOPS_URL . '/user.php?op=actv&uid=' . $user->getVar('uid') . '&actkey=' . $user->getShow('actkey'));
	}
	function &getResult()
	{
		return $this->mMailer;
	}
}
class User_RegistUserAdminActivateMailBuilder extends User_RegistUserActivateMailBuilder
{
	function setTemplate()
	{
		$root=&XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$this->mMailer->setTemplateDir(XOOPS_ROOT_PATH . '/modules/user/language/' . $language . '/mail_template/');
		$this->mMailer->setTemplate('adminactivate.tpl');
	}
	function setToUsers($user, $userConfig)
	{
		$memberHandler=&xoops_gethandler('member');
		$this->mMailer->setToGroups($memberHandler->getGroup($userConfig['activation_group']));
	}
	function setFromUser($xoopsConfig)
	{
		$this->mMailer->setFromEmail($xoopsConfig['adminmail']);
		$this->mMailer->setFromName($xoopsConfig['sitename']);
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(@sprintf(_MD_USER_LANG_USERKEYFOR,$user->getVar('uname')));
	}
	function setBody($user, $xoopsConfig)
	{
		parent::setBody($user,$xoopsConfig);
		$this->mMailer->assign('USERNAME', $user->getVar('uname'));
		$this->mMailer->assign('USEREMAIL', $user->getVar('email'));
		$this->mMailer->assign('USERACTLINK', XOOPS_URL . '/user.php?op=actv&uid=' . $user->getVar('uid') . '&actkey=' . $user->getVar('actkey'));
	}
}
class User_RegistUserNotifyMailBuilder extends User_RegistUserActivateMailBuilder
{
	function setTemplate()
	{
	}
	function setToUsers($user, $userConfig)
	{
		$memberHandler=&xoops_gethandler('member');
		$this->mMailer->setToGroups($memberHandler->getGroup($userConfig['new_user_notify_group']));
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(@sprintf(_MD_USER_LANG_NEWUSERREGAT, $xoopsConfig['sitename']));
	}
	function setBody($user, $xoopsConfig)
	{
		$this->mMailer->setBody(@sprintf(_MD_USER_LANG_HASJUSTREG, $user->getVar('uname')));
	}
}
class User_RegistAdminCommitMailBuilder extends User_RegistUserActivateMailBuilder
{
	function setTemplate()
	{
		$root=&XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$this->mMailer->setTemplateDir(XOOPS_ROOT_PATH . '/modules/user/language/' . $language . '/mail_template/');
		$this->mMailer->setTemplate('activated.tpl');
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(@sprintf(_MD_USER_LANG_YOURACCOUNT, $xoopsConfig['sitename']));
	}
}
?>
