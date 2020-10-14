<?php
 if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH."/admin/actions/AbstractModuleInstallAction.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
require_once XOOPS_LEGACY_PATH."/admin/forms/ModuleInstallForm.class.php";
class Legacy_ModuleInstallAction extends Legacy_Action
{
	var $mInstallSuccess = null;
	var $mInstallFail = null;
	var $mXoopsModule = null;
	var $mInstaller = null;
	function Legacy_ModuleInstallAction($flag)
	{
		parent::Legacy_Action($flag);
		$this->mInstallSuccess =& new XCube_Delegate();
		$this->mInstallSuccess->register('Legacy_ModuleInstallAction.InstallSuccess');
		$this->mInstallFail =& new XCube_Delegate();
		$this->mInstallFail->register('Legacy_ModuleInstallAction.InstallFail');
	}
	function prepare(&$controller, &$xoopsUser)
	{
		$dirname = $controller->mRoot->mContext->mRequest->getRequest('dirname');
		$handler =& xoops_gethandler('module');
		$this->mXoopsModule =& $handler->getByDirname($dirname);
		if (is_object($this->mXoopsModule)) {
			return false;
		}
        $this->mXoopsModule =& $handler->create();
        $this->mXoopsModule->set('weight', 1);
        $this->mXoopsModule->loadInfoAsVar($dirname);
        if ($this->mXoopsModule->get('dirname') == null) {
            return false;
        }
        if ($this->mXoopsModule->get('dirname') == 'system') {
            $this->mXoopsModule->set('mid', 1);
        }
		$this->_setupActionForm();
		$this->mInstaller =& $this->_getInstaller();
		$this->mInstaller->setCurrentXoopsModule($this->mXoopsModule);
		return true;
	}
	function &_getInstaller()
	{
		$dirname = $this->mXoopsModule->get('dirname');
		$installer =& Legacy_ModuleInstallUtils::createInstaller($dirname);
		return $installer;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_ModuleInstallForm();
		$this->mActionForm->prepare();
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$this->mActionForm->load($this->mXoopsModule);
		return LEGACY_FRAME_VIEW_INPUT;
	}
	function execute(&$controller, &$xoopsUser)
	{
		if (isset($_REQUEST['_form_control_cancel'])) {
			return LEGACY_FRAME_VIEW_CANCEL;
		}
		$this->mActionForm->fetch();
		$this->mActionForm->validate();
		if ($this->mActionForm->hasError()) {
			return $this->getDefaultView($controller, $xoopsUser);
		}
		$this->mInstaller->setForceMode($this->mActionForm->get('force'));
		if (!$this->mInstaller->executeInstall()) {
			$this->mInstaller->mLog->addReport('Force Uninstallation is started.');
			$dirname = $this->mXoopsModule->get('dirname');
			$uninstaller =& Legacy_ModuleInstallUtils::createUninstaller($dirname);
			$uninstaller->setForceMode(true);
			$uninstaller->setCurrentXoopsModule($this->mXoopsModule);
			$uninstaller->executeUninstall();
		}
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function executeViewSuccess(&$controller,&$xoopsUser,&$renderer)
	{
		if (!$this->mInstaller->mLog->hasError()) {
			$this->mInstallSuccess->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleInstall.' . ucfirst($this->mXoopsModule->get('dirname') . '.Success'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		else {
			$this->mInstallFail->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleInstall.' . ucfirst($this->mXoopsModule->get('dirname') . '.Fail'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		$renderer->setTemplateName("module_install_success.html");
		$renderer->setAttribute('module', $this->mXoopsModule);
		$renderer->setAttribute('log', $this->mInstaller->mLog->mMessages);
	}
	function executeViewInput(&$controller,&$xoopsUser,&$renderer)
	{
		$renderer->setTemplateName("module_install.html");
		$renderer->setAttribute('module', $this->mXoopsModule);
		$renderer->setAttribute('actionForm', $this->mActionForm);
		$renderer->setAttribute('currentVersion', round($this->mXoopsModule->get('version') / 100, 2));
	}
	function executeViewCancel(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeForward("./index.php?action=InstallList");
	}
}
?>
