<?php
 if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_ModuleInfoAction extends Legacy_Action
{
	var $mModuleObject = null;
	var $mInstalledFlag = false;
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$dirname = xoops_getrequest('dirname');
		if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $dirname)) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		if (!is_dir(XOOPS_MODULE_PATH . "/" . $dirname)) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		$moduleHandler =& xoops_gethandler('module');
		$this->mModuleObject =& $moduleHandler->getByDirname($dirname);
		if (is_object($this->mModuleObject)) {
			$this->mModuleObject->loadAdminMenu();
			$this->mModuleObject->loadInfo($dirname);
			$this->mInstalledFlag = true;
		}
		else {
			$this->mModuleObject =& $moduleHandler->create();
			$this->mModuleObject->loadInfoAsVar($dirname);
			$this->mInstalledFlag = false;
		}
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$renderer)
	{
		$renderer->setTemplateName("module_information.html");
		$renderer->setAttribute('module', $this->mModuleObject);
		$renderer->setAttribute('installed', $this->mInstalledFlag);
	}
	function executeViewError(&$controller, &$xoopsUser, &$renderer)
	{
		$controller->executeRedirect('./index.php?action=ModuleList', 1, _AD_LEGACY_ERROR_MODULE_NOT_FOUND);
	}
}
?>
