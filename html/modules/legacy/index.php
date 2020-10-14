<?php
require_once "../../mainfile.php";
require_once XOOPS_ROOT_PATH . "/header.php";
require_once XOOPS_MODULE_PATH . "/legacy/class/ActionFrame.class.php";
$root =& XCube_Root::getSingleton();
$actionName = isset($_GET['action']) ? trim($_GET['action']) : "Default";
$moduleRunner =& new Legacy_ActionFrame(false);
$moduleRunner->setActionName($actionName);
$root->mController->mExecute->add(array(&$moduleRunner, 'execute'));
$root->mController->execute();
require_once XOOPS_ROOT_PATH . "/footer.php";
?>
