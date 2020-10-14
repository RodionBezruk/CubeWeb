<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/modules/XUpgrade/admin/class/UpgradeProcessor.class.php";
class XUpgrade_Installer extends Legacy_ModuleInstaller
{
	function _processScript()
	{
		$procedure =& new XUpgrade_UpgradeProcessor(true);
		$procedure->execute($this->_mXoopsModule, $this->mLog);
	}
}
?>
