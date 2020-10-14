<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_SiteClose extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		if ($this->mRoot->mContext->getXoopsConfig('closesite') == 1) {
			$this->mController->mSetupUser->add("Legacy_SiteClose::callbackSetupUser", XCUBE_DELEGATE_PRIORITY_FINAL);
			$this->mRoot->mDelegateManager->add("Site.CheckLogin.Success", array(&$this, "callbackCheckLoginSuccess"));
		}
	}
	function callbackSetupUser(&$principal, &$controller, &$context)
	{
		$accessAllowFlag = false;
		$xoopsConfig = $controller->mRoot->mContext->getXoopsConfig();
		if (!empty($_POST['xoops_login'])) {
			$controller->checkLogin();
			return;
		} else if (@$_GET['op']=='logout') { 
			$controller->logout();
			return;
		} elseif (is_object($context->mXoopsUser)) {
			foreach ($context->mXoopsUser->getGroups() as $group) {
				if (in_array($group, $xoopsConfig['closesite_okgrp']) || XOOPS_GROUP_ADMIN == $group) {
					$accessAllowFlag = true;
					break;
				}
			}
		}
		if (!$accessAllowFlag) {
			require_once XOOPS_ROOT_PATH . '/class/template.php';
			$xoopsTpl =& new XoopsTpl();
			$xoopsTpl->assign(array('xoops_sitename' => htmlspecialchars($xoopsConfig['sitename']),
									   'xoops_isuser' => is_object( $context->mXoopsUser ),
									   'xoops_themecss' => xoops_getcss(),
									   'xoops_imageurl' => XOOPS_THEME_URL . '/' . $xoopsConfig['theme_set'] . '/',
									   'lang_login' => _LOGIN,
									   'lang_username' => _USERNAME,
									   'lang_password' => _PASSWORD,
									   'lang_siteclosemsg' => $xoopsConfig['closesite_text']
									   ));
			$xoopsTpl->compile_check = true;
			$xoopsTpl->display(XOOPS_ROOT_PATH . '/modules/legacy/templates/legacy_site_closed.html');
			exit();
		}
	}
	function callbackCheckLoginSuccess(&$xoopsUser)
	{
		if (!is_object($xoopsUser)) {
			return;
		}
		if ($this->mRoot->mContext->getXoopsConfig('closesite')) {
			$accessAllowed = false;
			foreach ($xoopsUser->getGroups() as $group) {
				if (in_array($group, $this->mRoot->mContext->getXoopsConfig('closesite_okgrp')) || ($group == XOOPS_GROUP_ADMIN)) {
					$accessAllowed = true;
					break;
				}
			}
			if (!$accessAllowed) {
				$this->mController->executeRedirect(XOOPS_URL . '/', 1, _NOPERM);
			}
		}
	}
}
?>
