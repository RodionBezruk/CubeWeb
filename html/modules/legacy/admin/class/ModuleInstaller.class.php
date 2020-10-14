<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
class Legacy_ModuleInstaller
{
	var $mLog = null;
	var $_mForceMode = false;
	var $_mXoopsModule = null;
	function Legacy_ModuleInstaller()
	{
		$this->mLog =& new Legacy_ModuleInstallLog();
	}
	function setCurrentXoopsModule(&$xoopsModule)
	{
		$this->_mXoopsModule =& $xoopsModule;
	}
	function setForceMode($isForceMode)
	{
		$this->_mForceMode = $isForceMode;
	}
	function _installTables()
	{
		Legacy_ModuleInstallUtils::installSQLAutomatically($this->_mXoopsModule, $this->mLog);
	}
    function _installModule()
    {
		$moduleHandler =& xoops_gethandler('module');
		if (!$moduleHandler->insert($this->_mXoopsModule)) {
			$this->mLog->addError("*Could not install module information*");
			return false;
		}
        $gpermHandler =& xoops_gethandler('groupperm');
        if ($this->_mXoopsModule->getInfo('hasAdmin')) {
            $adminPerm =& $this->_createPermission(XOOPS_GROUP_ADMIN);
            $adminPerm->setVar('gperm_name', 'module_admin');
            if (!$gpermHandler->insert($adminPerm)) {
                $this->mLog->addError(_AD_LEGACY_ERROR_COULD_NOT_SET_ADMIN_PERMISSION);
            }
        }
        if ($this->_mXoopsModule->getVar('dirname') == 'system') {
			$root =& XCube_Root::getSingleton();
			$root->mLanguageManager->loadModuleAdminMessageCatalog('system');
            require_once XOOPS_ROOT_PATH . "/modules/system/constants.php";
            $fileHandler = opendir(XOOPS_ROOT_PATH . "/modules/system/admin");
            while ($file = readdir($fileHandler)) {
                $infoFile = XOOPS_ROOT_PATH . "/modules/system/admin/" . $file . "/xoops_version.php";
                if (file_exists($infoFile)) {
                    require_once $infoFile;
                    if (!empty($modversion['category'])) {
                        $sysAdminPerm  =& $this->_createPermission(XOOPS_GROUP_ADMIN);
                        $adminPerm->setVar('gperm_itemid', $modversion['category']);
                        $adminPerm->setVar('gperm_name', 'system_admin');
                        if (!$gpermHandler->insert($adminPerm)) {
                            $this->mLog->addError(_AD_LEGACY_ERROR_COULD_NOT_SET_SYSTEM_PERMISSION);
                        }
                        unset($sysAdminPerm);
                    }
                    unset($modversion);
                }
            }
        }
        if ($this->_mXoopsModule->getInfo('hasMain')) {
            $read_any = $this->_mXoopsModule->getInfo('read_any');
            if ($read_any) {
                $memberHandler =& xoops_gethandler('member');
                $groupObjects =& $memberHandler->getGroups();
                foreach($groupObjects as $group) {
                    $readPerm =& $this->_createPermission($group->getVar('groupid'));
                    $readPerm->setVar('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError(_AD_LEGACY_ERROR_COULD_NOT_SET_READ_PERMISSION);
                    }
                }
            } else {
                $root =& XCube_Root::getSingleton();
                $groups = $root->mContext->mXoopsUser->getGroups(true);
                foreach($groups as $mygroup) {
                    $readPerm =& $this->_createPermission($mygroup);
                    $readPerm->setVar('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError(_AD_LEGACY_ERROR_COULD_NOT_SET_READ_PERMISSION);
                    }
                }
            }
        }
    }
    function &_createPermission($group)
    {
        $gpermHandler =& xoops_gethandler('groupperm');
        $perm =& $gpermHandler->create();
        $perm->setVar('gperm_groupid', $group);
        $perm->setVar('gperm_itemid', $this->_mXoopsModule->getVar('mid'));
        $perm->setVar('gperm_modid', 1);
        return $perm;
    }
	function _installTemplates()
	{
		Legacy_ModuleInstallUtils::installAllOfModuleTemplates($this->_mXoopsModule, $this->mLog);
	}
    function _installBlocks()
    {
		Legacy_ModuleInstallUtils::installAllOfBlocks($this->_mXoopsModule, $this->mLog);
    }
    function _installPreferences()
    {
        Legacy_ModuleInstallUtils::installAllOfConfigs($this->_mXoopsModule, $this->mLog);
    }
    function _processScript()
    {
        $installScript = trim($this->_mXoopsModule->getInfo('onInstall'));
        if ($installScript != false) {
            require_once XOOPS_MODULE_PATH . "/" . $this->_mXoopsModule->get('dirname') . "/" . $installScript;
            $funcName = 'xoops_module_install_' . $this->_mXoopsModule->get('dirname');
			if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $funcName)) {
				$this->mLog->addError(XCUbe_Utils::formatMessage(_AD_LEGACY_ERROR_FAILED_TO_EXECUTE_CALLBACK, $funcName));
				return;
			}
            if (function_exists($funcName)) {
				$result = $funcName($this->_mXoopsModule);                	
				if (!$result) {
                    $this->mLog->addError(XCUbe_Utils::formatMessage(_AD_LEGACY_ERROR_FAILED_TO_EXECUTE_CALLBACK, $funcName));
                }
            }
        }
    }
	function _processReport()
	{
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_INSTALLATION_MODULE_SUCCESSFUL, $this->_mXoopsModule->get('name')));
		}
		else {
			$this->mLog->addError(XCube_Utils::formatMessage(_AD_LEGACY_ERROR_INSTALLATION_MODULE_FAILURE, $this->_mXoopsModule->get('name')));
		}
	}
	function executeInstall()
	{
		$this->_installTables();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installModule();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installTemplates();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installBlocks();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_installPreferences();
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
