<?php
 if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_AbstractModuleInstallAction extends Legacy_Action
{
	var $mModuleObject = null;
	var $mLog = null;
	var $mActionForm = null;
	function prepare(&$controller, &$xoopsUser)
	{
		$this->_setupActionForm();
	}
	function _setupActionForm()
	{
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$dirname = trim(xoops_getrequest('dirname'));
		$installer =& $this->_getInstaller($dirname);
		$this->mModuleObject =& $installer->loadModuleObject($dirname);
		if (!is_object($this->mModuleObject)) {
			$this->mLog =& $installer->getLog();
			return LEGACY_FRAME_VIEW_ERROR;
		}
		$this->mActionForm->load($this->mModuleObject);
		$this->mModuleObject->loadAdminMenu();
		$this->mModuleObject->loadInfo($dirname);
		return LEGACY_FRAME_VIEW_INDEX;
	}
	function execute(&$controller, &$xoopsUser)
	{
		if (isset($_REQUEST['_form_control_cancel'])) {
			return LEGACY_FRAME_VIEW_CANCEL;
		}
		$this->mActionForm->fetch();
		$this->mActionForm->validate();
		$installer =& $this->_getInstaller($this->mActionForm->get('dirname'));
		$this->mModuleObject =& $installer->loadModuleObject($this->mActionForm->get('dirname'));
		if ($installer->hasAgree()) {
			$this->_loadAgreement();
		}
		if ($this->mActionForm->hasError()) {
			if ($installer->hasAgree()) {
				return LEGACY_FRAME_VIEW_INPUT;
			}
			else {
				return LEGACY_FRAME_VIEW_INDEX;
			}
		}
		if (!is_object($this->mModuleObject)) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		$installer->setForceMode($this->mActionForm->get('force'));
		$installer->execute($this->mActionForm->get('dirname'));
		$this->mLog =& $installer->getLog();
		return LEGACY_FRAME_VIEW_SUCCESS;
	}
	function &_getInstaller($dirname)
	{
	}
	function _loadAgreement()
	{
	}
	function executeViewError(&$controller,&$xoopsUser,&$renderer)
	{
		$renderer->setTemplateName("install_wizard_error.html");
		$renderer->setAttribute('log', $this->mLog);
	}
}
?>
