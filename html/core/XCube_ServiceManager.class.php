<?php
if (!defined('XCUBE_CORE_PATH')) define('XCUBE_CORE_PATH', dirname(__FILE__));
require_once XCUBE_CORE_PATH . '/XCube_Delegate.class.php';
class XCube_ServiceUtils
{
	function isXSD($typeName)
	{
		if ($typeName == 'string' || $typeName == 'int') {
			return true;
		}
		return false;
	}
}
class XCube_ServiceManager
{
	var $mServices = array();
	var $mCreateClient = null;
	var $mCreateServer = null;
	function XCube_ServiceManager()
	{
		$this->mCreateClient =& new XCube_Delegate();
		$this->mCreateClient->register("XCube_ServiceManager.CreateClient");
		$this->mCreateServer =& new XCube_Delegate();
		$this->mCreateServer->register("XCube_ServiceManager.CreateServer");
	}
	function addService($name, &$service)
	{
		if (isset($this->mServices[$name])) {
			return false;
		}
		$this->mServices[$name] =& $service;
		return true;
	}
	function addWSDL($name, $url)
	{
		if (isset($this->mServices[$name])) {
			return false;
		}
		$this->mServices[$name] =& $url;
		return true;
	}
	function addXCubeService($name, &$service)
	{
		return $this->addService($name, $service);
	}
	function &getService($name)
	{
		$ret = null;
		if (isset($this->mServices[$name])) {
			return $this->mServices[$name];
		}
		return $ret;
	}
	function &searchXCubeService($name)
	{
		return $this->getService($name);
	}
	function &createClient(&$service)
	{
		$client = null;
		$this->mCreateClient->call(new XCube_Ref($client), new XCube_Ref($service));
		return $client;
	}
	function &createServer(&$service)
	{
		$server = null;
		$this->mCreateServer->call(new XCube_Ref($server), new XCube_Ref($service));
		return $server;
	}
}
?>
