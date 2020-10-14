<?php
class XCube_RoleManager
{
	function getRolesForUser($username = null)
	{
	}
}
class XCube_Role
{
	function getRolesForUser($username = null)
	{
		$root =& XCube_Root::getSingleton();
		return $root->mRoleManager->getRolesForUser($username);
	}
}
?>
