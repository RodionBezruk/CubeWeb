<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_LEGACY_PATH . "/admin/actions/AbstractModuleInstallAction.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/class/ModuleInstallUtils.class.php";
require_once XOOPS_LEGACY_PATH . "/admin/forms/ModuleUpdateForm.class.php";
class Legacy_ModuleUpdateAction extends Legacy_Action
{
	var $mUpdateSuccess = null;
	var $mUpdateFail = null;
	var $mXoopsModule = null;
	var $mInstaller = null;
	function Legacy_ModuleUpdateAction($flag)
	{
		parent::Legacy_Action($flag);
		$this->mUpdateSuccess =& new XCube_Delegate();
		$this->mUpdateSuccess->register('Legacy_ModuleUpdateAction.UpdateSuccess');
		$this->mUpdateFail =& new XCube_Delegate();
		$this->mUpdateFail->register('Legacy_ModuleUpdateAction.UpdateFail');
	}
	function prepare(&$controller, &$xoopsUser)
	{
		$dirname = $controller->mRoot->mContext->mRequest->getRequest('dirname');
		$handler =& xoops_gethandler('module');
		$this->mXoopsModule =& $handler->getByDirname($dirname);
		if (!is_object($this->mXoopsModule)) {
			return false;
		}
		$this->_setupActionForm();
		$this->mInstaller =& $this->_getInstaller();
		$this->mInstaller->setCurrentXoopsModule($this->mXoopsModule);
        $name = $this->mXoopsModule->get('name');
		$this->mXoopsModule->loadInfoAsVar($dirname);
		$this->mXoopsModule->set('name', $name);
		$this->mInstaller->setTargetXoopsModule($this->mXoopsModule);
		return true;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_ModuleUpdateForm();
		$this->mActionForm->prepare();
	}
	function &_getInstaller()
	{
		$dirname = $this->mXoopsModule->get('dirname');
		$installer =& Legacy_ModuleInstallUtils::createUpdater($dirname);
		return $installer;
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
		$this->mInstaller->executeUpgrade();
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$renderer)
	{
		if (!$this->mInstaller->mLog->hasError()) {
			$this->mUpdateSuccess->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleUpdate.' . ucfirst($this->mXoopsModule->get('dirname') . '.Success'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		else {
			$this->mUpdateFail->call(new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
			XCube_DelegateUtils::call('Legacy.Admin.Event.ModuleUpdate.' . ucfirst($this->mXoopsModule->get('dirname') . '.Fail'), new XCube_Ref($this->mXoopsModule), new XCube_Ref($this->mInstaller->mLog));
		}
		$renderer->setTemplateName("module_update_success.html");
		$renderer->setAttribute('module', $this->mXoopsModule);
		$renderer->setAttribute('log', $this->mInstaller->mLog->mMessages);
		$renderer->setAttribute('currentVersion', round($this->mInstaller->getCurrentVersion() / 100, 2));
		$renderer->setAttribute('targetVersion', round($this->mInstaller->getTargetPhase() / 100, 2));
		$renderer->setAttribute('isPhasedMode', $this->mInstaller->hasUpgradeMethod());
		$renderer->setAttribute('isLatestUpgrade', $this->mInstaller->isLatestUpgrade());
	}
	function executeViewInput(&$controller, &$xoopsUser, &$renderer)
	{
		$renderer->setTemplateName("module_update.html");
		$renderer->setAttribute('module', $this->mXoopsModule);
		$renderer->setAttribute('actionForm', $this->mActionForm);
		$renderer->setAttribute('currentVersion', round($this->mInstaller->getCurrentVersion() / 100, 2));
		$renderer->setAttribute('targetVersion', round($this->mInstaller->getTargetPhase() / 100, 2));
		$renderer->setAttribute('isPhasedMode', $this->mInstaller->hasUpgradeMethod());
	}
	function executeViewCancel(&$controller, &$xoopsUser, &$renderer)
	{
		$controller->executeForward("./index.php?action=ModuleList");
	}
}
?>
