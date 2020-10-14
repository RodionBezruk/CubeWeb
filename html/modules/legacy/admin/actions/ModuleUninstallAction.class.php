<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/actions/AbstractModuleInstallAction.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/forms/ModuleUninstallForm.class.php";
class Legacy_ModuleUninstallAction extends Legacy_Action
{
	var $mUninstallSuccess = null;
	var $mUninstallFail = null;
	var $mXoopsModule = null;
	var $mInstaller = null;
	function Legacy_ModuleUninstallAction($flag)
	{
		parent::Legacy_Action($flag);
		$this->mUninstallSuccess =& new XCube_Delegate();
		$this->mUninstallSuccess->register('Legacy_ModuleUninstallAction.UninstallSuccess');
		$this->mUninstallFail =& new XCube_Delegate();
		$this->mUninstallFail->register('Legacy_ModuleUninstallAction.UninstallFail');
	}
	function prepare(&$controller, &$xoopsUser)
	{
		$dirname = $controller->mRoot->mContext->mRequest->getRequest('dirname');
		$handler =& xoops_gethandler('module');
		$this->mXoopsModule =& $handler->getByDirname($dirname);
		if (!(is_object($this->mXoopsModule) && $this->mXoopsModule->get('isactive') == 0)) {
			return false;
		}
		$this->mXoopsModule->loadInfoAsVar($dirname);
		$this->_setupActionForm();
		$this->mInstaller =& $this->_getInstaller();
		$this->mInstaller->setCurrentXoopsModule($this->mXoopsModule);
		return true;
	}
	function &_getInstaller()
	{
		$dirname = $this->mXoopsModule->get('dirname');
		$installer =&  Legacy_ModuleInstallUtils::createUninstaller($dirname);
		return $installer;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_ModuleUninstallForm();
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
		$this->mInstaller->executeUninstall();
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$renderer)
	{
		if (!$this->mInstaller->mLog->hasError()) {
			$this->mUninstallSuccess->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleUninstall.' . ucfirst($this->mXoopsModule->get('dirname') . '.Success'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		else {
			$this->mUninstallFail->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleUninstall.' . ucfirst($this->mXoopsModule->get('dirname') . '.Fail'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		$renderer->setTemplateName("module_uninstall_success.html");
		$renderer->setAttribute('module',$this->mXoopsModule);
		$renderer->setAttribute('log', $this->mInstaller->mLog->mMessages);
	}
	function executeViewInput(&$controller, &$xoopsUser, &$renderer)
	{
		$renderer->setTemplateName("module_uninstall.html");
		$renderer->setAttribute('actionForm', $this->mActionForm);
		$renderer->setAttribute('module', $this->mXoopsModule);
		$renderer->setAttribute('currentVersion', round($this->mXoopsModule->get('version') / 100, 2));
	}
	function executeViewCancel(&$controller, &$xoopsUser, &$renderer)
	{
		$controller->executeForward("./index.php?action=ModuleList");
	}
}
?>
