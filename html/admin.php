<?php
include "mainfile.php";
class DefaultSystemCheckFunction
{
	function DefaultCheck()
	{
		if (ini_get('register_globals') == 1) {
		    xoops_error(sprintf(_WARNPHPENV,'register_globals','on',_WARNSECURITY),'','warning');
		}
		if (is_dir(XOOPS_ROOT_PATH."/install/" )) {
		    xoops_error(sprintf(_WARNINSTALL2,XOOPS_ROOT_PATH.'/install/'),'','warning');
		}
		if ( is_writable(XOOPS_ROOT_PATH."/mainfile.php" ) ) {
		    xoops_error(sprintf(_WARNINWRITEABLE,XOOPS_ROOT_PATH.'/mainfile.php'),'','warning');
		}
	}
}
require_once XOOPS_ROOT_PATH . "/header.php";
$root=&XCube_Root::getSingleton();
$root->mDelegateManager->add("Legacypage.Admin.SystemCheck", "DefaultSystemCheckFunction::DefaultCheck");
XCube_DelegateUtils::call("Legacypage.Admin.SystemCheck");
require_once XOOPS_ROOT_PATH . "/footer.php";
?>
