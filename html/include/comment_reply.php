<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
	exit();
}
require_once XOOPS_ROOT_PATH.'/header.php';
require_once XOOPS_MODULE_PATH . "/legacy/forms/CommentEditForm.class.php";
require_once XOOPS_ROOT_PATH . "/include/comment_constants.php";
$t_root =& XCube_Root::getSingleton();
$t_root->mLanguageManager->loadModuleMessageCatalog("legacy");
$t_root->mLanguageManager->loadPageTypeMessageCatalog("comment");	
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
$r_name = XoopsUser::getUnameFromId($comment->getVar('com_uid'));
$r_text = _CM_POSTER.': <b>'.$r_name.'</b>&nbsp;&nbsp;'._CM_POSTED.': <b>'.formatTimestamp($comment->getVar('com_created')).'</b><br /><br />'.$comment->getVar('com_text');$com_title = $comment->getVar('com_title', 'E');
if (!preg_match("/^re:/i", $com_title)) {
	$com_title = "Re: ".xoops_substr($com_title, 0, 56);
}
$com_pid = $com_id;
$com_text = '';
$com_id = 0;
$dosmiley = 1;
$dohtml = 0;
$doxcode = 1;
$dobr = 1;
$doimage = 1;
$com_icon = '';
$com_rootid = $comment->getVar('com_rootid');
$com_itemid = $comment->getVar('com_itemid');
$res_comment =& $comment->createChild();
if (is_object($xoopsUser)) {
	$comment->set('uid', $xoopsUser->get('uid'));
}
else {
	$comment->set('uid', 0);
}
if (is_object($xoopsUser) && $xoopsUser->isAdmin()) {
	$actionForm =& new Legacy_CommentEditForm_Admin();
}
else {
	$actionForm =& new Legacy_CommentEditForm();
}
$actionForm->prepare();
$actionForm->load($res_comment);
$handler =& xoops_gethandler('subjecticon');
$subjectIcons =& $handler->getObjects();
themecenterposts($comment->getVar('com_title'), $r_text);
$renderSystem =& $t_root->getRenderSystem($t_root->mContext->mBaseRenderSystemName);
$renderTarget =& $renderSystem->createRenderTarget('main');
$renderTarget->setTemplateName("legacy_comment_edit.html");
$renderTarget->setAttribute("actionForm", $actionForm);
$renderTarget->setAttribute("subjectIcons", $subjectIcons);
$renderTarget->setAttribute("xoopsModuleConfig", $xoopsModuleConfig);
$renderTarget->setAttribute("com_order", $com_order);
$renderSystem->render($renderTarget);
print $renderTarget->getResult();
require_once XOOPS_ROOT_PATH . "/footer.php";
?>
