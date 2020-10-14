<?php
if (!defined('XOOPS_ROOT_PATH')) die();
class Pm_Preload extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		require_once XOOPS_MODULE_PATH . "/pm/service/Service.class.php";
		$service =& new Pm_Service();
		$service->prepare();
		$this->mRoot->mServiceManager->addService('privateMessage', $service);
		$root =& XCube_Root::getSingleton();
		$root->mDelegateManager->add('Legacypage.Viewpmsg.Access', "Pm_Preload::accessToViewpmsg");
		$root->mDelegateManager->add('Legacypage.Readpmsg.Access', "Pm_Preload::accessToReadpmsg");
		$root->mDelegateManager->add('Legacypage.Pmlite.Access', "Pm_Preload::accessToPmlite");
	}
	function accessToReadpmsg()
	{
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('pm');
		$root->mLanguageManager->loadModuleMessageCatalog('pm');
		require_once XOOPS_MODULE_PATH . "/pm/class/ActionFrame.class.php";
		$actionName = xoops_getrequest('action') == 'DeleteOne' ? 'DeleteOne' : 'read';
		$moduleRunner =& new Pm_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function accessToViewpmsg()
	{
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('pm');
		$root->mLanguageManager->loadModuleMessageCatalog('pm');
		require_once XOOPS_MODULE_PATH . "/pm/class/ActionFrame.class.php";
		$actionName = xoops_getrequest('action') == 'delete' ? 'delete' : 'default';
		$moduleRunner =& new Pm_ActionFrame(false);
		$moduleRunner->setActionName($actionName);
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
	function accessToPmlite()
	{
		$root =& XCube_Root::getSingleton();
		$root->mController->executeHeader();
		$root->mController->setupModuleContext('pm');
		$root->mLanguageManager->loadModuleMessageCatalog('pm');
		$root->mController->setDialogMode(true);
		require_once XOOPS_MODULE_PATH . "/pm/class/ActionFrame.class.php";
		$moduleRunner =& new Pm_ActionFrame(false);
		$moduleRunner->setActionName('pmlite');
		$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
		$root->mController->execute();
		$root->mController->executeView();
	}
}
?>
