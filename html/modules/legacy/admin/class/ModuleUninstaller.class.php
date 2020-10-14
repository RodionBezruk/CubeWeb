<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
class Legacy_ModuleUninstaller
{
	var $mLog = null;
	var $_mForceMode = false;
	var $_mXoopsModule = null;
	var $m_fireNotifyUninstallTemplateBegun;
	function Legacy_ModuleUninstaller()
	{
		$this->mLog =& new Legacy_ModuleInstallLog();
		$this->m_fireNotifyUninstallTemplateBegun =& new XCube_Delegate();
		$this->m_fireNotifyUninstallTemplateBegun->register("Legacy_ModuleUninstaller._fireNotifyUninstallTemplateBegun");
	}
	function setCurrentXoopsModule(&$xoopsModule)
	{
		$this->_mXoopsModule =& $xoopsModule;
	}
	function setForceMode($isForceMode)
	{
		$this->_mForceMode = $isForceMode;
	}
	function _uninstallModule()
	{
		$moduleHandler =& xoops_gethandler('module');
		if (!$moduleHandler->delete($this->_mXoopsModule)) {
			$this->mLog->addError(_AD_LEGACY_ERROR_DELETE_MODULEINFO_FROM_DB);
		}
		else {
			$this->mLog->addReport(_AD_LEGACY_MESSAGE_DELETE_MODULEINFO_FROM_DB);
		}
	}
	function _uninstallTables()
	{
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		$dirname = $this->_mXoopsModule->get('dirname');
		$t_search = array('{prefix}', '{dirname}', '{Dirname}', '{_dirname_}');
		$t_replace = array(XOOPS_DB_PREFIX, strtolower($dirname), ucfirst(strtolower($dirname)), $dirname);
		$tables = $this->_mXoopsModule->getInfo('tables');
		if ($tables != false && is_array($tables)) {
			foreach($tables as $table) {
				$t_tableName = $table;
				if (isset($this->_mXoopsModule->modinfo['cube_style']) && $this->_mXoopsModule->modinfo['cube_style'] == true) {
					$t_tableName = str_replace($t_search, $t_replace, $table);
				}
				else {
					$t_tableName = $db->prefix($table);
				}
				$sql = "DROP TABLE " . $t_tableName;
				if ($db->query($sql)) {
					$this->mLog->addReport(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_DROP_TABLE, $t_tableName));
				}
				else {
					$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_DROP_TABLE, $t_tableName));
				}
			}
		}
	}
	function _uninstallTemplates()
	{
		$this->m_fireNotifyUninstallTemplateBegun->call(new XCube_Ref($this->_mXoopsModule));
		Legacy_ModuleInstallUtils::uninstallAllOfModuleTemplates($this->_mXoopsModule, $this->mLog);
	}
	function _uninstallBlocks()
	{
		Legacy_ModuleInstallUtils::uninstallAllOfBlocks($this->_mXoopsModule, $this->mLog);
		$tplHandler =& xoops_gethandler('tplfile');
		$criteria =& new Criteria('tpl_module', $this->_mXoopsModule->get('dirname'));
		if(!$tplHandler->deleteAll($criteria)) {
			$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_DELETE_BLOCK_TEMPLATES, $tplHandler->db->error()));
		}
	}
	function _uninstallPreferences()
	{
		Legacy_ModuleInstallUtils::uninstallAllOfConfigs($this->_mXoopsModule, $this->mLog);
		Legacy_ModuleInstallUtils::deleteAllOfNotifications($this->_mXoopsModule, $this->mLog);
		Legacy_ModuleInstallUtils::deleteAllOfComments($this->_mXoopsModule, $this->mLog);
	}
	function _processScript()
	{
		$installScript = trim($this->_mXoopsModule->getInfo('onUninstall'));
		if ($installScript != false) {
			require_once XOOPS_MODULE_PATH . "/" . $this->_mXoopsModule->get('dirname') . "/" . $installScript;
			$funcName = 'xoops_module_uninstall_' . $this->_mXoopsModule->get('dirname');
			if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $funcName)) {
				$this->mLog->addError(XCUbe_Utils::formatMessage(_AD_LEGACY_ERROR_FAILED_TO_EXECUTE_CALLBACK, $funcName));
				return;
			}
			if (function_exists($funcName)) {
				if (!call_user_func($funcName, $this->_mXoopsModule)) {
					$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_FAILED_TO_EXECUTE_CALLBACK, $funcName));
				}
			}
		}
	}
	function _processReport()
	{
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_UNINSTALLATION_MODULE_SUCCESSFUL, $this->_mXoopsModule->get('name')));
		}
		else {
			$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_UNINSTALLATION_MODULE_FAILURE, $this->_mXoopsModule->get('name')));
		}
	}
	function executeUninstall()
	{
		$this->_uninstallTables();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		if ($this->_mXoopsModule->get('mid') != null) {
			$this->_uninstallModule();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallTemplates();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallBlocks();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_uninstallPreferences();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
			$this->_processScript();
			if (!$this->_mForceMode && $this->mLog->hasError()) {
				$this->_processReport();
				return false;
			}
		}
		$this->_processReport();
		return true;
	}
}
?>
