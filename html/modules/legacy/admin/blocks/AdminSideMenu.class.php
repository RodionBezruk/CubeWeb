<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_AdminSideMenu extends Legacy_AbstractBlockProcedure
{
	var $mModules = array();
	var $mCurrentModule = null;
	function getName()
	{
		return "sidemenu";
	}
	function getTitle()
	{
		return "TEST: AdminSideMenu";
	}
	function getEntryIndex()
	{
		return 0;
	}
	function isEnableCache()
	{
		return false;
	}
	function execute()
	{
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadModuleAdminMessageCatalog('legacy'); 
		$controller =& $root->mController;
		$user =& $root->mController->mRoot->mContext->mXoopsUser;
		$render =& $this->getRenderTarget();
		$render->setAttribute('legacy_module', 'legacy');
		$this->mCurrentModule =& $controller->mRoot->mContext->mXoopsModule;
		if ($this->mCurrentModule->get('dirname') == 'legacy') {
			if (xoops_getrequest('action') == "help") {
				$moduleHandler =& xoops_gethandler('module');
				$t_module =& $moduleHandler->getByDirname(xoops_gethandler('dirname'));
				if (is_object($t_module)) {
					$this->mCurrentModule =& $t_module;
				}
			}
		}
		$db=&$controller->getDB();
		$mod = $db->prefix("modules");
		$perm = $db->prefix("group_permission");
		$groups = implode(",", $user->getGroups());
		if ($root->mContext->mUser->isInRole('Site.Owner')) {
			$sql = "SELECT DISTINCT mid FROM ${mod} WHERE isactive=1 AND hasadmin=1 ORDER BY weight, mid";
		}
		else {
	        $sql = "SELECT DISTINCT ${mod}.mid FROM ${mod},${perm} " .
	               "WHERE ${mod}.isactive=1 AND ${mod}.mid=${perm}.gperm_itemid AND ${perm}.gperm_name='module_admin' AND ${perm}.gperm_groupid IN (${groups}) " .
	               "AND ${mod}.hasadmin=1 " .
	               "ORDER BY ${mod}.weight, ${mod}.mid";
		}
		$result=$db->query($sql);
		$handler =& xoops_gethandler('module');
		while($row = $db->fetchArray($result)) {
			$xoopsModule =& $handler->get($row['mid']);
			$module =& Legacy_Utils::createModule($xoopsModule);
			$this->mModules[] =& $module;
			unset($module);
		}
		$render->setTemplateName('legacy_admin_block_sidemenu.html');
		$render->setAttribute('modules', $this->mModules);
		$render->setAttribute('currentModule', $this->mCurrentModule);
		$renderSystem =& $root->getRenderSystem($this->getRenderSystemName());
		$renderSystem->renderBlock($render);
	}
}
?>
