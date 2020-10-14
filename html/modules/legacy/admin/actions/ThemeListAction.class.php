<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/ThemeSelectForm.class.php";
class Legacy_ThemeListAction extends Legacy_Action
{
	var $mThemes = null;
	var $mObjectHandler = null;
	var $mActionForm = null;
	var $mMainTheme = null;
	function prepare(&$controller, &$xoopsUser)
	{
		$this->_setupObject();
		$this->_setupActionForm();
		$handler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('conf_name', 'theme_set'));
		$criteria->add(new Criteria('conf_catid', XOOPS_CONF));
		$configs =& $handler->getConfigs($criteria);
		$this->mMainTheme = $configs[0]->get('conf_value');
	}
	function _setupObject()
	{
		$handler =& xoops_getmodulehandler('theme');
		$this->mThemes =& $handler->getObjects();
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_ThemeSelectForm();
		$this->mActionForm->prepare();
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$configHandler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('conf_name', 'theme_set_allowed'));
		$criteria->add(new Criteria('conf_catid', XOOPS_CONF));
		$configs =& $configHandler->getConfigs($criteria);
		$selectedThemeArr = unserialize($configs[0]->get('conf_value'));
		$this->mActionForm->load($selectedThemeArr);
		return LEGACY_FRAME_VIEW_INDEX;
	}
	function execute(&$controller, &$xoopsUser)
	{
		$this->mActionForm->fetch();
		$this->mActionForm->validate();
		if ($this->mActionForm->hasError()) {
			return $this->getDefaultView($controller, $xoopsUser);
		}
		$configHandler =& xoops_gethandler('config');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('conf_name', 'theme_set_allowed'));
		$criteria->add(new Criteria('conf_catid', XOOPS_CONF));
		$configs =& $configHandler->getConfigs($criteria);
		$t_themeArr = $this->mActionForm->getSelectableTheme();
		$configs[0]->set('conf_value', serialize($t_themeArr));
		if (!$configHandler->insertConfig($configs[0])) {
			die(); 
		}
		$themeName = $this->mActionForm->getChooseTheme();
		if ($themeName != null) {
			$criteria =& new CriteriaCompo();
			$criteria->add(new Criteria('conf_name', 'theme_set'));
			$criteria->add(new Criteria('conf_catid', XOOPS_CONF));
			$configs =& $configHandler->getConfigs($criteria);
			$configs[0]->set('conf_value', $themeName);
			if ($configHandler->insertConfig($configs[0])) {
				$controller->mRoot->mContext->setThemeName($themeName);
				$this->mMainTheme = $themeName;
			}
		}
		XCube_DelegateUtils::call('Legacy.Event.ThemeSettingChanged', $this->mMainTheme, $t_themeArr);
		return $this->getDefaultView($controller, $xoopsUser);
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("theme_list.html");
		$render->setAttribute("themes", $this->mThemes);
		$render->setAttribute("actionForm", $this->mActionForm);
		$render->setAttribute("currentThemeName", $this->mMainTheme);
	}
}
?>
