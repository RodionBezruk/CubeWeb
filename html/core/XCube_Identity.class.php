<?php
class XCube_Identity
{
	var $mName = "";
	var $_mAuthenticationType = "";
	function XCube_Identity()
	{
	}
	function setAuthenticationType($type)
	{
		$this->_mAuthenticationType = $type;
	}
	function getAuthenticationType()
	{
		return $this->_mAuthenticationType;
	}
	function setName($name)
	{
		$this->mName = $name;
	}
	function getName()
	{
		return $this->mName;
	}
	function isAuthenticated()
	{
	}
}
class XCube_Principal
{
	var $mIdentity = null;
	var $_mRoles = array();
	function XCube_Principal($identity, $roles = array())
	{
		$this->mIdentity =& $identity;
		$this->_mRoles = $roles;
	}
	function getIdentity()
	{
		return $this->mIdentity;
	}
	function isInRole($rolename)
	{
	}
}
?>
