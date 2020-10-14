<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyNon_installation_moduleHandler extends XoopsObjectHandler
{
	var $_mXoopsModules = array();
	var $_mExclusions = array(".", "..", "CVS");
	function LegacyNon_installation_moduleHandler(&$db)
	{
		parent::XoopsObjectHandler($db);
		$this->_setupObjects();
	}
	function _setupObjects()
	{
		if (count($this->_mXoopsModules) == 0) {
			if ($handler = opendir(XOOPS_MODULE_PATH))	{
				while (($dir = readdir($handler)) !== false) {
					if (!in_array($dir, $this->_mExclusions) && is_dir(XOOPS_MODULE_PATH . "/" . $dir)) {
						$module =& $this->get($dir);
						if ($module !== false ) {
							$this->_mXoopsModules[] =& $module;
						}
						unset($module);
					}
				}
			}
		}
	}
	function &get($dirname)
	{
		$ret = false;
		if (!file_exists(XOOPS_MODULE_PATH . "/" . $dirname . "/xoops_version.php")) {
			return $ret;
		}
		$moduleHandler =& xoops_gethandler('module');
		$check =& $moduleHandler->getByDirname($dirname);
		if (is_object($check)) {
			return $ret;
		}
		$module =& $moduleHandler->create();
		$module->loadInfoAsVar($dirname);
		return $module;
	}
	function &getObjects($criteria=null)
	{
		return $this->_mXoopsModules;
	}
	function &getObjectsFor2ndInstaller()
	{
		$ret = array();
		foreach (array_keys($this->_mXoopsModules) as $key) {
			if (empty($this->_mXoopsModules[$key]->modinfo['disable_legacy_2nd_installer'])) {
				$ret[] =& $this->_mXoopsModules[$key];
			}
		}
		return $ret;
	}
}
?>
