<?php
include '../../../../mainfile.php';
include XOOPS_ROOT_PATH.'/include/cp_functions.php';
if (is_object($xoopsUser)) {
	$module_handler =& xoops_gethandler('module');
	$xoopsModule =& $module_handler->getByDirname('system');
	if (!in_array(XOOPS_GROUP_ADMIN, $xoopsUser->getGroups())) {
		$sysperm_handler =& xoops_gethandler('groupperm');
		if (!$sysperm_handler->checkRight('system_admin', XOOPS_SYSTEM_COMMENT, $xoopsUser->getGroups())) {
			redirect_header(XOOPS_URL.'/', 3, _NOPERM);;
			exit();
		}
	}
} else {
	redirect_header(XOOPS_URL.'/', 3, _NOPERM);
	exit();
}
?>
