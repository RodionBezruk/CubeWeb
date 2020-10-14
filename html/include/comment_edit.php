<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
	exit();
}
require_once XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . "/include/comment_constants.php";
require_once XOOPS_MODULE_PATH . "/legacy/forms/CommentEditForm.class.php";
$t_root =& XCube_Root::getSingleton();
$langManager =& $t_root->getLanguageManager();
$langManager->loadModuleMessageCatalog("legacy");
if ('system' != $xoopsModule->getVar('dirname') && XOOPS_COMMENT_APPROVENONE == $xoopsModuleConfig['com_rule']) {
	exit();
}
$t_root->mLanguageManager->loadPageTypeMessageCatalog('comment');
$com_id = isset($_GET['com_id']) ? intval($_GET['com_id']) : 0;
$com_mode = isset($_GET['com_mode']) ? htmlspecialchars(trim($_GET['com_mode']), ENT_QUOTES) : '';
if ($com_mode == '') {
	if (is_object($xoopsUser)) {
		$com_mode = $xoopsUser->getVar('umode');
	} else {
		$com_mode = $xoopsConfig['com_mode'];
	}
}
if (!isset($_GET['com_order'])) {
	if (is_object($xoopsUser)) {
		$com_order = $xoopsUser->getVar('uorder');
	} else {
		$com_order = $xoopsConfig['com_order'];
	}
} else {
	$com_order = intval($_GET['com_order']);
}
$comment_handler =& xoops_gethandler('comment');
$comment =& $comment_handler->get($com_id);
$dohtml = $comment->getVar('dohtml');
$dosmiley = $comment->getVar('dosmiley');
$dobr = $comment->getVar('dobr');
$doxcode = $comment->getVar('doxcode');
$com_icon = $comment->getVar('com_icon');
$com_itemid = $comment->getVar('com_itemid');
$com_title = $comment->getVar('com_title', 'E');
$com_text = $comment->getVar('com_text', 'E');
$com_pid = $comment->getVar('com_pid');
$com_status = $comment->getVar('com_status');
$com_rootid = $comment->getVar('com_rootid');
$handler =& xoops_gethandler('subjecticon');
$subjectIcons =& $handler->getObjects();
if ($xoopsModule->getVar('dirname') != 'system') {
	if (is_object($xoopsUser) && $xoopsUser->isAdmin()) {
		$actionForm =& new Legacy_CommentEditForm_Admin();
	}
	else {
		$actionForm =& new Legacy_CommentEditForm();
	}
	$actionForm->prepare();
	$actionForm->load($comment);
	$renderSystem =& $t_root->getRenderSystem($t_root->mContext->mBaseRenderSystemName);
	$renderTarget =& $renderSystem->createRenderTarget('main');
	$renderTarget->setTemplateName("legacy_comment_edit.html");
	$renderTarget->setAttribute("actionForm", $actionForm);
	$renderTarget->setAttribute("subjectIcons", $subjectIcons);
	$renderTarget->setAttribute("xoopsModuleConfig", $xoopsModuleConfig);
	$renderTarget->setAttribute("com_order", $com_order);
	$renderSystem->render($renderTarget);
	print $renderTarget->getResult();
	require_once XOOPS_ROOT_PATH.'/footer.php';
} else {
	xoops_cp_header();
	require_once XOOPS_ROOT_PATH.'/include/comment_form.php';
	xoops_cp_footer();
}
?>
