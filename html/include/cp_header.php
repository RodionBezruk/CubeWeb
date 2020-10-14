<?php 
if (!defined('XOOPS_ROOT_PATH')) {
	if (!file_exists("../../../mainfile.php")) exit();
	require_once "../../../mainfile.php";
}
if (!defined('XOOPS_CPFUNC_LOADED')) require_once XOOPS_ROOT_PATH . "/include/cp_functions.php";
$root =& XCube_Root::getSingleton();
require_once XOOPS_ROOT_PATH . "/modules/legacy/kernel/Legacy_AdminControllerStrategy.class.php";
$strategy =& new Legacy_AdminControllerStrategy($root->mController);
$root->mController->setStrategy($strategy);
$root->mController->setupModuleContext();
$root->mController->_mStrategy->setupModuleLanguage();	
?>
