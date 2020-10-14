<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_Identity extends XCube_Identity
{
	function Legacy_Identity(&$xoopsUser)
	{
		parent::XCube_Identity();
		if (!is_object($xoopsUser)) {
			die('Exception');
		}
		$this->mName = $xoopsUser->get('uname');
	}
	function isAuthenticated()
	{
		return true;
	}
}
class Legacy_AnonymousIdentity extends XCube_Identity
{
	function isAuthenticated()
	{
		return false;
	}
}
class Legacy_GenericPrincipal extends XCube_Principal
{
	function addRole($roleName)
	{
		if (!$this->isInRole($roleName)) {
			$this->_mRoles[] = $roleName;
		}
	}
	function isInRole($roleName)
	{
		return in_array($roleName, $this->_mRoles);
	}
}
?>
