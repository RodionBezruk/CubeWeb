<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractDeleteAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/CustomBlockDeleteForm.class.php";
class Legacy_CustomBlockDeleteAction extends Legacy_AbstractDeleteAction
{
	function _getId()
	{
		return isset($_REQUEST['bid']) ? $_REQUEST['bid'] : 0;
	}
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('newblocks');
		return $handler;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_CustomBlockDeleteForm();
		$this->mActionForm->prepare();
	}
	function _isDeletable()
	{
		if (is_object($this->mObject)) {
			return ($this->mObject->get('block_type') == 'C' && $this->mObject->get('visible') == 0);
		}
		else {
			return false;
		}
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		if (!$this->_isDeletable()) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		return parent::getDefaultView($controller, $xoopsUser);
	}
	function execute(&$controller, &$xoopsUser)
	{
		if (!$this->_isDeletable()) {
			return LEGACY_FRAME_VIEW_ERROR;
		}
		return parent::execute($controller, $xoopsUser);
	}
	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		$render->setTemplateName("customblock_delete.html");
		$render->setAttribute('actionForm', $this->mActionForm);
		$this->mObject->loadModule();
		$this->mObject->loadColumn();
		$this->mObject->loadCachetime();
		$render->setAttribute('object', $this->mObject);
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeForward("./index.php?action=BlockInstallList");
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect("./index.php?action=BlockInstallList", 1, _MD_LEGACY_ERROR_DBUPDATE_FAILED);
	}
	function executeViewCancel(&$controller, &$xoopsUser, &$render)
	{
		if ($this->mObject->isNew()) {
			$controller->executeForward("./index.php?action=BlockInstallList");
		}
		else {
			$controller->executeForward("./index.php?action=BlockList");
		}
	}
}
?>
