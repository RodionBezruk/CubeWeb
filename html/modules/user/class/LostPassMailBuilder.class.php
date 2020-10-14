<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class User_LostPassMailDirector
{
	var $mBuilder;
	var $mXoopsUser;
	var $mXoopsConfig;
	var $mExtraVars;
	function User_LostPassMailDirector(&$builder, &$user, &$xoopsConfig, $extraVars = array())
	{
		$this->mBuilder =& $builder;
		$this->mXoopsUser =& $user;
		$this->mXoopsConfig =& $xoopsConfig;
		$this->mExtraVars = $extraVars;
	}
	function contruct()
	{
		$this->mBuilder->setToUsers($this->mXoopsUser, $this->mXoopsConfig);
		$this->mBuilder->setFromEmail($this->mXoopsUser, $this->mXoopsConfig);
		$this->mBuilder->setSubject($this->mXoopsUser, $this->mXoopsConfig);
		$this->mBuilder->setTemplate();
		$this->mBuilder->setBody($this->mXoopsUser, $this->mXoopsConfig,$this->mExtraVars);
	}
}
class User_LostPass1MailBuilder
{
	var $mMailer;
	function User_LostPass1MailBuilder()
	{
		$this->mMailer =& getMailer();
		$this->mMailer->useMail();
	}
	function setToUsers($user, $xoopsConfig)
	{
		$this->mMailer->setToUsers($user);
	}
	function setFromEmail($user, $xoopsConfig)
	{
		$this->mMailer->setFromEmail($xoopsConfig['adminmail']);
		$this->mMailer->setFromName($xoopsConfig['sitename']);
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(sprintf(_MD_USER_LANG_NEWPWDREQ, $xoopsConfig['sitename']));
	}
	function setTemplate()
	{
		$root =& XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$this->mMailer->setTemplateDir(XOOPS_MODULE_PATH . '/user/language/' . $language . '/mail_template/');
		$this->mMailer->setTemplate("lostpass1.tpl");
	}
	function setBody($user,$xoopsConfig,$extraVars)
	{
		$this->mMailer->assign("SITENAME", $xoopsConfig['sitename']);
		$this->mMailer->assign("ADMINMAIL", $xoopsConfig['adminmail']);
		$this->mMailer->assign("SITEURL", XOOPS_URL . "/");
		$this->mMailer->assign("IP", $_SERVER['REMOTE_ADDR']);
		$this->mMailer->assign("NEWPWD_LINK", XOOPS_URL . "/lostpass.php?email=" . $user->getShow('email') . "&code=" . substr($user->get("pass"), 0, 5));
	}
	function &getResult()
	{
		return $this->mMailer;
	}
}
class User_LostPass2MailBuilder extends User_LostPass1MailBuilder
{
	function setTemplate()
	{
		$root=&XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$this->mMailer->setTemplateDir(XOOPS_MODULE_PATH . '/user/language/' . $language . '/mail_template/');
		$this->mMailer->setTemplate("lostpass2.tpl");
	}
	function setSubject($user, $xoopsConfig)
	{
		$this->mMailer->setSubject(sprintf(_MD_USER_LANG_NEWPWDREQ, $xoopsConfig['sitename']));
	}
	function setBody($user, $xoopsConfig, $extraVars)
	{
		$this->mMailer->assign("SITENAME", $xoopsConfig['sitename']);
		$this->mMailer->assign("ADMINMAIL", $xoopsConfig['adminmail']);
		$this->mMailer->assign("SITEURL", XOOPS_URL . "/");
		$this->mMailer->assign("IP", $_SERVER['REMOTE_ADDR']);
		$this->mMailer->assign("NEWPWD", $extraVars['newpass']);
	}
}
?>
