<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XUpgrade_UpgradeProcessor
{
	var $mConfigHandler = null;
	var $mLog = null;
	var $mIsInstall = false;
	function XUpgrade_UpgradeProcessor($isInstall)
	{
		$this->mIsInstall = $isInstall;
		$this->mConfigHandler =& xoops_gethandler('config');
	}
	function execute(&$module, &$log)
	{
		if ($module->get('dirname') != 'XUpgrade') {
			return;
		}
		$this->mLog =& $log;
		$log->add(_MI_XUPGRADE_MESSAGE_START_PORTING);
		$this->_portConfigs();
		if (XUPGRADE_ENABLE_TEMPLATEPORTING == true) {
			$this->_portTemplates();
		}
		if ($this->mIsInstall) {
			$this->_adjustModules();
		}
	}
	function _portConfigs()
	{
		$generalConfigs =& $this->_getOldConfigs(XOOPS_CONF);
		$oldConfigs =& $this->_getOldConfigs(XOOPS_CONF_USER);
		$cubeConfigs =& $this->_getCubeConfigs('user');
		$this->_fullCopy($oldConfigs, $cubeConfigs, 'user');
		$userItems = array('usercookie', 'use_ssl', 'sslpost_name', 'sslloginlink');
		foreach ($userItems as $key) {
			if (isset($generalConfigs[$key])) {
				$this->_copy($generalConfigs[$key], $cubeConfigs[$key], 'user');
			}
		}
		$oldConfigs =& $this->_getOldConfigs(XOOPS_CONF_METAFOOTER);
		$cubeConfigs =& $this->_getCubeConfigs('legacyRender');
		$this->_fullCopy($oldConfigs, $cubeConfigs, 'legacyRender');
		if (isset($generalConfigs['banners'])) {
			$this->_copy($generalConfigs['banners'], $cubeConfigs['banners'], 'legacyRender');
		}
		require_once XOOPS_ROOT_PATH . "/kernel/configcategory.php";
		$root =& XCube_Root::getSingleton();
		$handler =& new XoopsConfigCategoryHandler($root->mController->mDB);
		$category =& $handler->get(XOOPS_CONF_USER);
		if (is_object($category)) {
			if ($handler->delete($category)) {
				$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_DELETED_CATEGORY, _MI_XUPGRADE_LANG_USER_CATEGORY));
			}
			else {
				$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_DELETED_CATEGORY, _MI_XUPGRADE_LANG_USER_CATEGORY));
			}
		}
		$category =& $handler->get(XOOPS_CONF_METAFOOTER);
		if (is_object($category)) {
			if ($handler->delete($category)) {
				$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_DELETED_CATEGORY, _MI_XUPGRADE_LANG_METEFOOTER_CATEGORY));
			}
			else {
				$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_DELETED_CATEGORY, _MI_XUPGRADE_LANG_METEFOOTER_CATEGORY));
			}
		}
	}
	function _portTemplates()
	{
		$targetTemplates = array(
			'system_comment.html' => 'legacy_comment.html',
			'system_comments_flats.html' => 'legacy_comments_flats.html',
			'system_comments_next.html' => 'legacy_comments_next.html',
			'system_comments_thread.html' => 'system_comments_thread.html',
			'system_dummy.html' => 'legacy_dummy.html',
			'system_notification_list.html' => 'legacy_notification_list.html',
			'system_notification_select.html' => 'legacy_notification_select_form.html',
			'system_redirect.html' => 'legacy_redirect.html',
			'system_rss.html' => 'legacy_rss.html',
			'system_siteclosed.html' => 'legacy_site_closed.html'
		);
		$handler =& xoops_getmodulehandler('tplfile', 'legacyRender');
		foreach ($targetTemplates as $srcName => $descName) {
			$criteria =& new CriteriaCompo();
			$criteria->add(new Criteria('tpl_module', 'system'));
			$criteria->add(new Criteria('tpl_tplset', 'default', '<>'));
			$criteria->add(new Criteria('tpl_file', $srcName));
			$src_tplfileArr =& $handler->getObjects($criteria);
			foreach ($src_tplfileArr as $src_tplfile) {
				$src_tplfile->loadSource();
				$criteria =& new CriteriaCompo();
				$criteria->add(new Criteria('tpl_module', 'base'));
				$criteria->add(new Criteria('tpl_tplset', $src_tplfile->get('tpl_tplset')));
				$criteria->add(new Criteria('tpl_file', $descName));
				$desc_tplfileArr =& $handler->getObjects($criteria);
				foreach (array_keys($desc_tplfileArr) as $key) {
					$desc_tplfileArr[$key]->loadSource();
					$desc_tplfileArr[$key]->Source->set('tpl_source', $src_tplfile->Source->get('tpl_source'));
					if ($handler->insert($desc_tplfileArr[$key])) {
						$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_PORTED_TEMPLATE, $src_tplfile->get('tpl_file'), $src_tplfile->get('tpl_tplset')));
					}
					else {
						$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_PORTED_TEMPLATE, $src_tplfile->get('tpl_file'), $src_tplfile->get('tpl_tplset')));
					}
				}
			}
		}
	}
	function &_getOldConfigs($categoryid)
	{
		$criteria =& new Criteria('conf_catid', $categoryid);
		$t_configs =& $this->mConfigHandler->getConfigs($criteria);
		$oldConfigs = array();
		foreach (array_keys($t_configs) as $key) {
			$oldConfigs[$t_configs[$key]->get('conf_name')] =& $t_configs[$key];
		}
		return $oldConfigs;
	}
	function &_getCubeConfigs($dirname)
	{
		$cubeConfigs = array();
		$handler =& xoops_gethandler('module');
		$module =& $handler->getByDirname($dirname);
		if (!is_object($module)) {
			return $cubeConfigs;
		}
		$criteria =& new Criteria('conf_modid', $module->get('mid'));
		$t_configs =& $this->mConfigHandler->getConfigs($criteria);
		foreach (array_keys($t_configs) as $key) {
			$cubeConfigs[$t_configs[$key]->get('conf_name')] =& $t_configs[$key];
		}
		return $cubeConfigs;
	}
	function _copy(&$oldConfig, &$cubeConfig, $dirname)
	{
		if (!is_object($oldConfig) || !is_object($cubeConfig)) {
			$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_CONFIGS_WRONG));
			return;
		}
		$value =& $oldConfig->getConfValueForOutput('conf_value');
		if (is_array($value)) {
			$value = implode('|', $value);
		}
		$cubeConfig->setConfValueForInput($value);
		if ($this->mConfigHandler->insertConfig($cubeConfig)) {
			$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_PORTED_CONFIG, $cubeConfig->get('conf_name'), 'user'));
		}
		else {
			$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_PORTED_CONFIG, $cubeConfig->get('conf_name'), 'user'));
		}
		if ($this->mConfigHandler->deleteConfig($oldConfig)) {
			$this->mLog->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_DELETED_CONFIG, $oldConfig->get('conf_name')));
		}
		else {
			$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_DELETED_CONFIG, $oldConfig->get('conf_name')));
		}
	}
	function _fullCopy(&$oldConfigs, &$cubeConfigs, $dirname)
	{
		foreach (array_keys($oldConfigs) as $key) {
			if (isset($cubeConfigs[$key])) {
				$this->_copy($oldConfigs[$key], $cubeConfigs[$key], $dirname);
			}
			else {
				$this->mLog->addError(XCube_Utils::formatMessage(_MI_XUPGRADE_ERROR_FIND_CONFIG, $key, 'user'));
			}
		}
	}
	function _adjustModules()
	{
		$handler =& xoops_gethandler('module');
		$criteria =& new CriteriaCompo();
		$criteria->add(new Criteria('isactive', 0));
		$criteria->add(new Criteria('hasmain', 0));
		$modules =& $handler->getObjects($criteria);
		foreach ($modules as $module) {
			$module->set('isactive', 1);
			$handler->insert($module);
			$log->add(XCube_Utils::formatMessage(_MI_XUPGRADE_MESSAGE_ADJUST_MODULE_ISACTIVE, $module->get('dirname')));
		}
	}
}
?>
