<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
	exit();
}
include_once XOOPS_ROOT_PATH.'/include/notification_constants.php';
include_once XOOPS_ROOT_PATH.'/include/notification_functions.php';
$root =& XCube_Root::getSingleton();
$root->mLanguageManager->loadPageTypeMessageCatalog('notification');
if (!isset($_POST['not_submit'])) {
	exit();
}
$update_list = $_POST['not_list'];
$module_id = $xoopsModule->getVar('mid');
$user_id = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
$notification_handler =& xoops_gethandler('notification');
foreach ($update_list as $update_item) {
	list($category, $item_id, $event) = split (',', $update_item['params']);
	$status = !empty($update_item['status']) ? 1 : 0;
	if (!$status) {
		$notification_handler->unsubscribe($category, $item_id, $event, $module_id, $user_id);
	} else {
		$notification_handler->subscribe($category, $item_id, $event);
	}
}
include_once XOOPS_ROOT_PATH . '/include/notification_functions.php';
$redirect_args = array();
foreach ($update_list as $update_item) {
	list($category,$item_id,$event) = split(',',$update_item['params']);
	$category_info =& notificationCategoryInfo($category);
	if (!empty($category_info['item_name'])) {
		$redirect_args[$category_info['item_name']] = $item_id;
	}
}
$argstring = '';
$first_arg = 1;
foreach (array_keys($redirect_args) as $arg) {
	if ($first_arg) {
		$argstring .= "?" . $arg . "=" . $redirect_args[$arg];
		$first_arg = 0;
	} else {
		$argstring .= "&amp;" . $arg . "=" . $redirect_args[$arg];
	}
}
redirect_header ($_POST['not_redirect'].$argstring, 3, _NOT_UPDATEOK);
exit();
?>
