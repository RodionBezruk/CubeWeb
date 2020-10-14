<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
class Legacy_ModulePhasedUpgrader
{
	var $_mMilestone = array();
	var $mLog = null;
	var $_mCurrentXoopsModule;
	var $_mCurrentVersion;
	var $_mTargetXoopsModule;
	var $_mTargetVersion;
	var $_mForceMode = false;
	function Legacy_ModulePhasedUpgrader()
	{
		$this->mLog =& new Legacy_ModuleInstallLog();
	}
	function setForceMode($isForceMode)
	{
		$this->_mForceMode = $isForceMode;
	}
	function setCurrentXoopsModule(&$xoopsModule)
	{
		$handler =& xoops_gethandler('module');
		$cloneModule =& $handler->create();
		$cloneModule->unsetNew();
		$cloneModule->set('mid', $xoopsModule->get('mid'));
		$cloneModule->set('name', $xoopsModule->get('name'));
		$cloneModule->set('version', $xoopsModule->get('version'));
		$cloneModule->set('last_update', $xoopsModule->get('last_update'));
		$cloneModule->set('weight', $xoopsModule->get('weight'));
		$cloneModule->set('isactive', $xoopsModule->get('isactive'));
		$cloneModule->set('dirname', $xoopsModule->get('dirname'));
		$cloneModule->set('hasmain', $xoopsModule->get('hasmain'));
		$cloneModule->set('hasadmin', $xoopsModule->get('hasadmin'));
		$cloneModule->set('hassearch', $xoopsModule->get('hassearch'));
		$cloneModule->set('hasconfig', $xoopsModule->get('hasconfig'));
		$cloneModule->set('hascomments', $xoopsModule->get('hascomments'));
		$cloneModule->set('hasnotification', $xoopsModule->get('hasnotification'));
		$this->_mCurrentXoopsModule =& $cloneModule;
		$this->_mCurrentVersion = $cloneModule->get('version');
	}
	function setTargetXoopsModule(&$xoopsModule)
	{
		$this->_mTargetXoopsModule =& $xoopsModule;
		$this->_mTargetVersion = $this->getTargetPhase();
	}
	function executeUpgrade()
	{
		if ($this->hasUpgradeMethod()) {
			return $this->_callUpgradeMethod();
		}
		else {
			return $this->executeAutomaticUpgrade();
		}
	}
	function getCurrentVersion()
	{
		return $this->_mCurrentVersion;
	}
	function getTargetPhase()
	{
		ksort($this->_mMilestone);
		foreach ($this->_mMilestone as $t_version => $t_value) {
			if ($t_version > $this->getCurrentVersion()) {
				return $t_version;
			}
		}
		return $this->_mTargetXoopsModule->get('version');
	}
	function hasUpgradeMethod()
	{
		ksort($this->_mMilestone);
		foreach ($this->_mMilestone as $t_version => $t_value) {
			if ($t_version > $this->getCurrentVersion()) {
				if (is_callable(array($this, $t_value))) {
					return true;
				}
			}
		}
		return false;
	}
	function _callUpgradeMethod()
	{
		ksort($this->_mMilestone);
		foreach ($this->_mMilestone as $t_version => $t_value) {
			if ($t_version > $this->getCurrentVersion()) {
				if (is_callable(array($this, $t_value))) {
					return $this->$t_value();
				}
			}
		}
		return false;
	}
	function isLatestUpgrade()
	{
		return ($this->_mTargetXoopsModule->get('version') == $this->getTargetPhase());
	}
	function saveXoopsModule(&$module)
	{
		$handler =& xoops_gethandler('module');
		if ($handler->insert($module)) {
			$this->mLog->addReport("XoopsModule is updated.");
		}
		else {
			$this->mLog->addError("Could not update module information.");
		}
	}
	function _processScript()
	{
		$installScript = trim($this->_mTargetXoopsModule->getInfo('onUpdate'));
		if ($installScript != false) {
			require_once XOOPS_MODULE_PATH . "/" . $this->_mTargetXoopsModule->get('dirname') . "/" . $installScript;
			$funcName = 'xoops_module_update_' . $this->_mTargetXoopsModule->get('dirname');
			if (function_exists($funcName)) {
				if (!call_user_func($funcName, $this->_mTargetXoopsModule, $this->getCurrentVersion())) {
					$this->mLog->addError("Failed to execute " . $funcName);
				}
			}
		}
	}
	function _processReport()
	{
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_UPDATING_MODULE_SUCCESSFUL, $this->_mCurrentXoopsModule->get('name')));
		}
		else {
			$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_UPDATING_MODULE_FAILURE, $this->_mCurrentXoopsModule->get('name')));
		}
	}
	function _updateModuleTemplates()
	{
		Legacy_ModuleInstallUtils::clearAllOfModuleTemplatesForUpdate($this->_mTargetXoopsModule, $this->mLog);
		Legacy_ModuleInstallUtils::installAllOfModuleTemplates($this->_mTargetXoopsModule, $this->mLog);
	}
	function _updateBlocks()
	{
		Legacy_ModuleInstallUtils::smartUpdateAllOfBlocks($this->_mTargetXoopsModule, $this->mLog);
	}
	function _updatePreferences()
	{
		Legacy_ModuleInstallUtils::smartUpdateAllOfPreferences($this->_mTargetXoopsModule, $this->mLog);
	}
	function executeAutomaticUpgrade()
	{
		$this->mLog->addReport(_AD_LEGACY_MESSAGE_UPDATE_STARTED);
		$this->_updateModuleTemplates();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_updateBlocks();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_updatePreferences();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->saveXoopsModule($this->_mTargetXoopsModule);
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_processScript();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_processReport();
		return true;
	}
}
?>
