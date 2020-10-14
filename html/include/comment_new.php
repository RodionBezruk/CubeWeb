<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
	exit();
}
require_once XOOPS_MODULE_PATH . "/legacy/forms/CommentEditForm.class.php";
$t_root =& XCube_Root::getSingleton();
$t_root->mLanguageManager->loadModuleMessageCatalog("legacy");
require_once XOOPS_ROOT_PATH.'/include/comment_constants.php';
if ('system' != $xoopsModule->getVar('dirname') && XOOPS_COMMENT_APPROVENONE == $xoopsModuleConfig['com_rule']) {
	exit();
}
$t_root->mLanguageManager->loadPageTypeMessageCatalog('comment');	
$com_itemid = isset($_GET['com_itemid']) ? intval($_GET['com_itemid']) : 0;
if ($com_itemid > 0) {
	include XOOPS_ROOT_PATH.'/header.php';
	if (isset($com_replytitle)) {
		if (isset($com_replytext)) {
			themecenterposts($com_replytitle, $com_replytext);
		}
		$myts =& MyTextSanitizer::getInstance();
		$com_title = $myts->htmlSpecialChars($com_replytitle);
		if (!preg_match("/^re:/i", $com_title)) {
			$com_title = "Re: ".xoops_substr($com_title, 0, 56);
		}
	} else {
		$com_title = '';
	}
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
	$noname = 0;
$handler =& xoops_gethandler('comment');
$comment =& $handler->create();
$comment->set("com_itemid", $com_itemid);
$comment->set("com_modid", $xoopsModule->get('mid'));
$comment->set("com_title", $com_title);
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
$actionForm->load($comment);
$handler =& xoops_gethandler('subjecticon');
$subjectIcons =& $handler->getObjects();
$renderSystem =& $t_root->getRenderSystem($t_root->mContext->mBaseRenderSystemName);
$renderTarget =& $renderSystem->createRenderTarget('main');
$renderTarget->setTemplateName("legacy_comment_edit.html");
$renderTarget->setAttribute("actionForm", $actionForm);
$renderTarget->setAttribute("subjectIcons", $subjectIcons);
$renderTarget->setAttribute("xoopsModuleConfig", $xoopsModuleConfig);
$renderTarget->setAttribute("com_order", $com_order);
$extraParams = array();
if ('system' != $xoopsModule->get('dirname')) {
	$comment_config = $xoopsModule->getInfo('comments');
	if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
		foreach ($comment_config['extraParams'] as $extra_param) {
			$extraParams[$extra_param] = xoops_getrequest($extra_param);
		}
	}
}
$renderTarget->setAttribute('extraParams', $extraParams);
$renderSystem->render($renderTarget);
print $renderTarget->getResult();
require_once XOOPS_ROOT_PATH . "/footer.php";
}
?>
