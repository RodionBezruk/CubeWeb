<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_ThemeSelect extends XCube_ActionFilter
{
	var $mIsSelectableTheme = null;
	function Legacy_ThemeSelect(&$controller)
	{
		parent::XCube_ActionFilter($controller);
		$this->mIsSelectableTheme =& new XCube_Delegate();
		$this->mIsSelectableTheme->register('Legacy_ThemeSelect.IsSelectableTheme');
		$controller->mSetupUser->add(array(&$this, 'doChangeTheme'));
	}
	function preBlockFilter()
	{
		$this->mController->mRoot->mDelegateManager->add("Site.CheckLogin.Success", array(&$this, "callbackCheckLoginSuccess"));
	}
	function doChangeTheme(&$principal, &$controller, &$context)
	{
		if (!empty($_POST['xoops_theme_select'])) {
		    $xoops_theme_select = explode('!-!', $_POST['xoops_theme_select']);
		    if ($this->_isSelectableTheme($xoops_theme_select[0])) {
    			$this->mRoot->mContext->setThemeName($xoops_theme_select[0]);
    			$_SESSION['xoopsUserTheme'] = $xoops_theme_select[0];
    			$controller->executeForward($GLOBALS['xoopsRequestUri']);
    		}
		} elseif (!empty($_SESSION['xoopsUserTheme']) && $this->_isSelectableTheme($_SESSION['xoopsUserTheme'])) {
			$this->mRoot->mContext->setThemeName($_SESSION['xoopsUserTheme']);
		}
	}
	function callbackCheckLoginSuccess(&$xoopsUser)
	{
		$userTheme = $xoopsUser->get('theme');
		if (in_array($userTheme, $this->mRoot->mContext->getXoopsConfig('theme_set_allowed'))) {
			$_SESSION['xoopsUserTheme'] = $userTheme;
			$this->mRoot->mContext->setThemeName($userTheme);
		}
	}
	function _isSelectableTheme($theme_name)
	{
		return in_array($theme_name, $this->mRoot->mContext->getXoopsConfig('theme_set_allowed'));
	}
}
?>
