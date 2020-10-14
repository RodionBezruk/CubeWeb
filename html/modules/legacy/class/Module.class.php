<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_Module extends Legacy_ModuleAdapter
{
	function Legacy_Module(&$xoopsModule)
	{
		parent::Legacy_ModuleAdapter($xoopsModule);
		$this->mGetAdminMenu =& new XCube_Delegate();
		$this->mGetAdminMenu->register('Legacy_Module.getAdminMenu');
	}
	function getAdminMenu()
	{
		$menu = parent::getAdminMenu();
		$this->mGetAdminMenu->call(new XCube_Ref($menu));
		ksort($menu);
		return $menu;
	}
}
?>
