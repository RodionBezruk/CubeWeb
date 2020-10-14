<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractListAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/forms/SmilesFilterForm.class.php";
class Legacy_MiscSmiliesAction extends Legacy_AbstractListAction
{
	var $mTargetName = null;
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('smiles', 'legacy');
		return $handler;
	}
	function &_getFilterForm()
	{
		$filter =& new Legacy_SmilesFilterForm($this->_getPageNavi(), $this->_getHandler());
		return $filter;
	}
	function _getBaseUrl()
	{
		return "./misc.php?type=Smilies";
	}
	function getDefaultView(&$controller, &$xoopsUser)
	{
		$this->mTargetName = xoops_getrequest('target');
		return parent::getDefaultView($controller, $xoopsUser);
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$root =& $controller->mRoot;
		$root->mLanguageManager->loadModuleMessageCatalog('legacy');
		$render->setTemplateName("legacy_misc_smilies.html");
		$render->setAttribute("objects", $this->mObjects);
		$render->setAttribute("pageNavi", $this->mFilter->mNavi);
		$render->setAttribute("targetName", $this->mTargetName);
	}
}
?>
