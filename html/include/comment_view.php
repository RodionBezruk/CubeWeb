<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
	exit();
}
require_once XOOPS_ROOT_PATH.'/include/comment_constants.php';
if (XOOPS_COMMENT_APPROVENONE != $xoopsModuleConfig['com_rule']) {
	$gperm_handler = & xoops_gethandler( 'groupperm' );
	$groups = ( $xoopsUser ) ? $xoopsUser -> getGroups() : XOOPS_GROUP_ANONYMOUS;
	$xoopsTpl->assign( 'xoops_iscommentadmin', $gperm_handler->checkRight( 'system_admin', LEGACY_SYSTEM_COMMENT, $groups) );
	$t_root =& XCube_Root::getSingleton();
	$t_root->mLanguageManager->loadPageTypeMessageCatalog('comment');
	$comment_config = $xoopsModule->getInfo('comments');
	$com_itemid = (trim($comment_config['itemName']) != '' && isset($_GET[$comment_config['itemName']])) ? intval($_GET[$comment_config['itemName']]) : 0;
	if ($com_itemid > 0) {
		$com_mode = isset($_GET['com_mode']) ? htmlspecialchars(trim($_GET['com_mode']), ENT_QUOTES) : '';
		if ($com_mode == '') {
			if (is_object($xoopsUser)) {
				$com_mode = $xoopsUser->getVar('umode');
			} else {
				$com_mode = $xoopsConfig['com_mode'];
			}
		}
		$xoopsTpl->assign('comment_mode', $com_mode);
		if (!isset($_GET['com_order'])) {
			if (is_object($xoopsUser)) {
				$com_order = $xoopsUser->getVar('uorder');
			} else {
				$com_order = $xoopsConfig['com_order'];
			}
		} else {
			$com_order = intval($_GET['com_order']);
		}
		if ($com_order != XOOPS_COMMENT_OLD1ST) {
			$xoopsTpl->assign(array('comment_order' => XOOPS_COMMENT_NEW1ST, 'order_other' => XOOPS_COMMENT_OLD1ST));
			$com_dborder = 'DESC';
		} else {
			$xoopsTpl->assign(array('comment_order' => XOOPS_COMMENT_OLD1ST, 'order_other' => XOOPS_COMMENT_NEW1ST));
			$com_dborder = 'ASC';
		}
		if (is_object($xoopsUser) && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
			$admin_view = true;
		} else {
			$admin_view = false;
		}
		$com_id = isset($_GET['com_id']) ? intval($_GET['com_id']) : 0;
		$com_rootid = isset($_GET['com_rootid']) ? intval($_GET['com_rootid']) : 0;
		$comment_handler =& xoops_gethandler('comment');
		if ($com_mode == 'flat') {
			$comments =& $comment_handler->getByItemId($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);
			include_once XOOPS_ROOT_PATH.'/class/commentrenderer.php';
			$renderer =& XoopsCommentRenderer::instance($xoopsTpl);
			$renderer->setComments($comments);
			$renderer->renderFlatView($admin_view);
		} elseif ($com_mode == 'thread') {
			$comment_url = $comment_config['pageName'] . '?';
			if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
				foreach ($comment_config['extraParams'] as $extra_key) {
					if (isset($GLOBALS[$extra_key])) {
						$comment_url .= $extra_key .'='. htmlspecialchars($GLOBALS[$extra_key], ENT_NOQUOTES).'&amp;';
					} elseif (isset($_REQUEST[$extra_key])) {
						$comment_url .= $extra_key .'='. htmlspecialchars($_REQUEST[$extra_key], ENT_NOQUOTES).'&amp;';
					}
					else {
						$comment_url .= $extra_key .'=&amp;';
					}
				}
			}
			$xoopsTpl->assign('comment_url', $comment_url.$comment_config['itemName'].'='.$com_itemid.'&amp;com_mode=thread&amp;com_order='.$com_order);
			if (!empty($com_id) && !empty($com_rootid) && ($com_id != $com_rootid)) {
				$comments =& $comment_handler->getThread($com_rootid, $com_id);
				if (false != $comments) {
					require_once XOOPS_ROOT_PATH.'/class/commentrenderer.php';
					$renderer =& XoopsCommentRenderer::instance($xoopsTpl);
					$renderer->setComments($comments);
					$renderer->renderThreadView($com_id, $admin_view);
				}
			} else {
				$top_comments =& $comment_handler->getTopComments($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);
				$c_count = count($top_comments);
				if ($c_count> 0) {
					for ($i = 0; $i < $c_count; $i++) {
						$comments =& $comment_handler->getThread($top_comments[$i]->getVar('com_rootid'), $top_comments[$i]->getVar('com_id'));
						if (false != $comments) {
							require_once XOOPS_ROOT_PATH.'/class/commentrenderer.php';
							$renderer =& XoopsCommentRenderer::instance($xoopsTpl);
							$renderer->setComments($comments);
							$renderer->renderThreadView($top_comments[$i]->getVar('com_id'), $admin_view);
						}
						unset($comments);
					}
				}
			}
		} else {
			$top_comments =& $comment_handler->getTopComments($xoopsModule->getVar('mid'), $com_itemid, $com_dborder);
			$c_count = count($top_comments);
			if ($c_count> 0) {
				for ($i = 0; $i < $c_count; $i++) {
					$comments =& $comment_handler->getThread($top_comments[$i]->getVar('com_rootid'), $top_comments[$i]->getVar('com_id'));
					include_once XOOPS_ROOT_PATH.'/class/commentrenderer.php';
					$renderer =& XoopsCommentRenderer::instance($xoopsTpl);
					$renderer->setComments($comments);
					$renderer->renderNestView($top_comments[$i]->getVar('com_id'), $admin_view);
				}
			}
		}
		$renderSystem =& $t_root->getRenderSystem($t_root->mContext->mBaseRenderSystemName);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setTemplateName("legacy_comment_navi.html");
		$renderTarget->setAttribute("pageName", $comment_config['pageName']);
		$modeOptions = array("nest" => _NESTED, "flat" => _FLAT, "thread" => _THREADED);
		$renderTarget->setAttribute('modeOptions', $modeOptions);
		$renderTarget->setAttribute('com_mode', $com_mode);
		$orderOptions = array(0 => _OLDESTFIRST, 1 => _NEWESTFIRST);
		$renderTarget->setAttribute('orderOptions', $orderOptions);
		$renderTarget->setAttribute('com_order', $com_order);
		$renderTarget->setAttribute('itemName', $comment_config['itemName']);
		$renderTarget->setAttribute('com_itemid', $com_itemid);
		$renderTarget->setAttribute('com_anonpost', $xoopsModuleConfig['com_anonpost']);
		$postcomment_link = "";
		if (!empty($xoopsModuleConfig['com_anonpost']) || is_object($xoopsUser)) {
			$postcomment_link = 'comment_new.php?com_itemid=' . $com_itemid . '&com_order=' . $com_order . '&com_mode=' . $com_mode;
		}
		$link_extra = '';
		$fetchParams = array();
		if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
			foreach ($comment_config['extraParams'] as $extra_key) {
				if (isset($GLOBALS[$extra_key])) {
					$fetchParams[$extra_key] = $GLOBALS[$extra_key];
			    } elseif (isset($_REQUEST[$extra_key])) {
					$fetchParams[$extra_key] = xoops_getrequest($extra_key);
			    }
			}
			foreach ($fetchParams as $key => $value) {
				$link_extra .= "&" . $key . "=" . $value;
			}
		}
		$renderTarget->setAttribute("extraParams", $fetchParams);
		$renderTarget->setAttribute("link_extra", $link_extra);
		$renderTarget->setAttribute("postcomment_link", $postcomment_link);
		$renderSystem->render($renderTarget);
		$xoopsTpl->assign(array('commentsnav' => $renderTarget->getResult(), 'editcomment_link' => 'comment_edit.php?com_itemid='.$com_itemid.'&amp;com_order='.$com_order.'&amp;com_mode='.$com_mode.''.htmlspecialchars($link_extra, ENT_QUOTES), 'deletecomment_link' => 'comment_delete.php?com_itemid='.$com_itemid.'&amp;com_order='.$com_order.'&amp;com_mode='.$com_mode.''.$link_extra, 'replycomment_link' => 'comment_reply.php?com_itemid='.$com_itemid.'&amp;com_order='.$com_order.'&amp;com_mode='.$com_mode.''.$link_extra));
		$xoopsTpl->assign(array('lang_from' => _CM_FROM, 'lang_joined' => _CM_JOINED, 'lang_posts' => _CM_POSTS, 'lang_poster' => _CM_POSTER, 'lang_thread' => _CM_THREAD, 'lang_edit' => _EDIT, 'lang_delete' => _DELETE, 'lang_reply' => _REPLY, 'lang_subject' => _CM_REPLIES, 'lang_posted' => _CM_POSTED, 'lang_updated' => _CM_UPDATED, 'lang_notice' => _CM_NOTICE));
	}
}
?>
