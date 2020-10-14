<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/admin/actions
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractEditAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/BlockInstallEditForm.class.php";
class Legacy_BlockInstallEditAction extends Legacy_BlockEditAction
{
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_BlockInstallEditForm();
		$this->mActionForm->prepare();
	}
	function _isEditable()
	{
		if (is_object($this->mObject)) {
			return ($this->mObject->get('visible') == 0);
		}
		else {
			return false;
		}
	}
	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		parent::executeViewInput($controller, $xoopsUser, $render);
		$render->setTemplateName("blockinstall_edit.html");
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
		$controller->executeForward("./index.php?action=BlockInstallList");
	}
}
?>
