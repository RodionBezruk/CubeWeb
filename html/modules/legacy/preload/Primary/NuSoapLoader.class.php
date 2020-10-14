<?php
if (!defined("XOOPS_ROOT_PATH")) exit();
class Legacy_NuSoapLoader extends XCube_ActionFilter
{
	function preFilter()
	{
		$this->mRoot->mDelegateManager->add('XCube_ServiceManager.CreateClient', 'Legacy_NuSoapLoader::createClient');
		$this->mRoot->mDelegateManager->add('XCube_ServiceManager.CreateServer', 'Legacy_NuSoapLoader::createServer');
	}
	function createClient(&$client, $service)
	{
		if (is_object($client)) {
			return;
		}
		$root =& XCube_Root::getSingleton();
		if (is_object($service) && is_a($service, 'XCube_Service')) {
			$client = new XCube_ServiceClient($service);
		}
		else {
			require_once XOOPS_ROOT_PATH . "/modules/legacy/lib/nusoap/nusoap.php";
			require_once XOOPS_ROOT_PATH . "/modules/legacy/lib/ShadePlus/SoapClient.class.php";
			$client = new ShadePlus_SoapClient($service);
		}
	}
	function createServer(&$server, $service)
	{
		if (is_object($server) || !is_object($service)) {
			return;
		}
		require_once XOOPS_ROOT_PATH . "/modules/legacy/lib/nusoap/nusoap.php";
		require_once XOOPS_ROOT_PATH . "/modules/legacy/lib/ShadePlus/ServiceServer.class.php";
		require_once XOOPS_ROOT_PATH . "/modules/legacy/lib/ShadeSoap/NusoapServer.class.php";
		$server = new ShadePlus_ServiceServer($service);
		$server->prepare();
	}
}
?>
