<?php
class Legacy_RoleManager
{
	function loadRolesByModule(&$module)
	{
		static $cache;
		$root =& XCube_Root::getSingleton();
		$context =& $root->mContext;
		if ($module == null) {
			return;
		}
		if (isset($cache[$module->get('mid')])) {
			return;
		}
		$groups = is_object($context->mXoopsUser) ? $context->mXoopsUser->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
		$handler =& xoops_gethandler('groupperm');
		if ($handler->checkRight('module_read', $module->get('mid'), $groups)) {
			$context->mUser->addRole('Module.' . $module->get('dirname') . '.Visitor');
		}
		if (is_object($context->mXoopsUser) && $handler->checkRight('module_admin', $module->get('mid'), $groups)) {
			$context->mUser->addRole('Module.' . $module->get('dirname') . '.Admin');
		}
		$handler =& xoops_getmodulehandler('group_permission', 'legacy');
		$roleArr = $handler->getRolesByModule($module->get('mid'), $groups);
		foreach ($roleArr as $role) {
			$context->mUser->addRole('Module.' . $module->get('dirname') . '.' . $role);
		}
		$cache[$module->get('mid')] = true;
	}
	function loadRolesByMid($mid)
	{
		$handler =& xoops_gethandler('module');
		$module =& $handler->get($mid);
		if (is_object($module)) {
			$this->loadRolesByModule($module);
		}
	}
	function loadRolesByDirname($dirname)
	{
		$handler =& xoops_gethandler('module');
		$module =& $handler->getByDirname($dirname);
		if (is_object($module)) {
			$this->loadRolesByModule($module);
		}
	}
}
?>
