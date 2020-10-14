<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_InstallListAction extends Legacy_Action
{
	var $mModuleObjects = null;
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$handler =& xoops_getmodulehandler('non_installation_module');
		$this->mModuleObjects =& $handler->getObjects();
		return LEGACY_FRAME_VIEW_INDEX;
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$renderer)
	{
		$renderer->setTemplateName("install_list.html");
		$renderer->setAttribute('moduleObjects', $this->mModuleObjects);
	}
}
?>
