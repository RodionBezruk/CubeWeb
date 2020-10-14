<?php
if (!defined('XOOPS_ROOT_PATH') || !is_object($xoopsModule)) {
    exit();
}
include_once XOOPS_ROOT_PATH.'/include/comment_constants.php';
$op = 'delete';
if (!empty($_POST)) {
    $com_mode = isset($_POST['com_mode']) ? htmlspecialchars(trim($_POST['com_mode']), ENT_QUOTES) : 'flat';
    $com_order = isset($_POST['com_order']) ? intval($_POST['com_order']) : XOOPS_COMMENT_OLD1ST;
    $com_id = isset($_POST['com_id']) ? intval($_POST['com_id']) : 0;
    $op = isset($_POST['op']) ? $_POST['op'] : 'delete';
} else {
    $com_mode = isset($_GET['com_mode']) ? htmlspecialchars(trim($_GET['com_mode']), ENT_QUOTES) : 'flat';
    $com_order = isset($_GET['com_order']) ? intval($_GET['com_order']) : XOOPS_COMMENT_OLD1ST;
    $com_id = isset($_GET['com_id']) ? intval($_GET['com_id']) : 0;
}
if ('system' == $xoopsModule->getVar('dirname')) {
    $comment_handler =& xoops_gethandler('comment');
    $comment =& $comment_handler->get($com_id);
    $module_handler =& xoops_gethandler('module');
    $module =& $module_handler->get($comment->getVar('com_modid'));
    $comment_config = $module->getInfo('comments');
    $com_modid = $module->getVar('mid');
    $redirect_page = XOOPS_URL.'/modules/system/admin.php?fct=comments&amp;com_modid='.$com_modid.'&amp;com_itemid';
    $moddir = $module->getVar('dirname');
    unset($comment);
} else {
    if (XOOPS_COMMENT_APPROVENONE == $xoopsModuleConfig['com_rule']) {
        exit();
    }
    $comment_config = $xoopsModule->getInfo('comments');
    $com_modid = $xoopsModule->getVar('mid');
    $redirect_page = $comment_config['pageName'].'?';
    $comment_confirm_extra = array();
    if (isset($comment_config['extraParams']) && is_array($comment_config['extraParams'])) {
        foreach ($comment_config['extraParams'] as $extra_param) {
            if (isset(${$extra_param})) {
                $redirect_page .= $extra_param.'='.${$extra_param}.'&amp;';
                $comment_confirm_extra [$extra_param] = ${$extra_param};
            } elseif (isset($_GET[$extra_param])) {
                $redirect_page .= $extra_param.'='.$_GET[$extra_param].'&amp;';
                $comment_confirm_extra [$extra_param] = $_GET[$extra_param];
            }
        }
    }
    $redirect_page .= $comment_config['itemName'];
    $moddir = $xoopsModule->getVar('dirname');
}
$accesserror = false;
if (!is_object($xoopsUser)) {
    $accesserror = true;
} else {
    if (!$xoopsUser->isAdmin($com_modid)) {
        $sysperm_handler =& xoops_gethandler('groupperm');
        if (!$sysperm_handler->checkRight('system_admin', LEGACY_SYSTEM_COMMENT, $xoopsUser->getGroups())) {
            $accesserror = true;
        }
    }
}
if (false != $accesserror) {
    $ref = xoops_getenv('HTTP_REFERER');
    if ($ref != '') {
        redirect_header($ref, 2, _NOPERM);
    } else {
        redirect_header($redirect_page.'?'.$comment_config['itemName'].'='.intval($com_itemid), 2, _NOPERM);
    }
    exit();
}
$t_root =& XCube_Root::getSingleton();
$t_root->mLanguageManager->loadPageTypeMessageCatalog('comment');	
switch ($op) {
case 'delete_one':
    $comment_handler = xoops_gethandler('comment');
    $comment =& $comment_handler->get($com_id);
    if (!$comment_handler->delete($comment)) {
        include XOOPS_ROOT_PATH.'/header.php';
        xoops_error(_CM_COMDELETENG.' (ID: '.$comment->getVar('com_id').')');
        include XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
    $com_itemid = $comment->getVar('com_itemid');
    if (isset($comment_config['callback']['update']) && trim($comment_config['callback']['update']) != '') {
        $skip = false;
        if (!function_exists($comment_config['callback']['update'])) {
            if (isset($comment_config['callbackFile'])) {
                $callbackfile = trim($comment_config['callbackFile']);
                if ($callbackfile != '' && file_exists(XOOPS_ROOT_PATH.'/modules/'.$moddir.'/'.$callbackfile)) {
                    include_once XOOPS_ROOT_PATH.'/modules/'.$moddir.'/'.$callbackfile;
                }
                if (!function_exists($comment_config['callback']['update'])) {
                    $skip = true;
                }
            } else {
                $skip = true;
            }
        }
        if (!$skip) {
            $criteria = new CriteriaCompo(new Criteria('com_modid', $com_modid));
            $criteria->add(new Criteria('com_itemid', $com_itemid));
            $criteria->add(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));
            $comment_count = $comment_handler->getCount($criteria);
            $comment_config['callback']['update']($com_itemid, $comment_count);
        }
    }
    if ($comment->getVar('com_uid') != 0) {
        $member_handler =& xoops_gethandler('member');
        $com_poster =& $member_handler->getUser($comment->getVar('com_uid'));
        if (is_object($com_poster)) {
            $member_handler->updateUserByField($com_poster, 'posts', $com_poster->getVar('posts') - 1);
        }
    }
    $thread_comments =& $comment_handler->getThread($comment->getVar('com_rootid'), $com_id);
    include_once XOOPS_ROOT_PATH.'/class/tree.php';
    $xot = new XoopsObjectTree($thread_comments, 'com_id', 'com_pid', 'com_rootid');
    $child_comments =& $xot->getFirstChild($com_id);
    $new_pid = $comment->getVar('com_pid');
    $errs = array();
    foreach (array_keys($child_comments) as $i) {
        $child_comments[$i]->setVar('com_pid', $new_pid);
        if (false != $comment->isRoot()) {
            $new_rootid = $child_comments[$i]->getVar('com_id');
            $child_comments[$i]->setVar('com_rootid', $child_comments[$i]->getVar('com_id'));
            if (!$comment_handler->insert($child_comments[$i])) {
                $errs[] = 'Could not change comment parent ID from <b>'.$com_id.'</b> to <b>'.$new_pid.'</b>. (ID: '.$new_rootid.')';
            } else {
                $c_child_comments =& $xot->getAllChild($new_rootid);
                $cc_count = count($c_child_comments);
                foreach (array_keys($c_child_comments) as $j) {
                    $c_child_comments[$j]->setVar('com_rootid', $new_rootid);
                    if (!$comment_handler->insert($c_child_comments[$j])) {
                        $errs[] = 'Could not change comment root ID from <b>'.$com_id.'</b> to <b>'.$new_rootid.'</b>.';
                    }
                }
            }
        } else {
            if (!$comment_handler->insert($child_comments[$i])) {
                $errs[] = 'Could not change comment parent ID from <b>'.$com_id.'</b> to <b>'.$new_pid.'</b>.';
            }
        }
    }
    if (count($errs) > 0) {
        include XOOPS_ROOT_PATH.'/header.php';
        xoops_error($errs);
        include XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
    redirect_header($redirect_page.'='.$com_itemid.'&amp;com_order='.$com_order.'&amp;com_mode='.$com_mode, 1, _CM_COMDELETED);
    break;
case 'delete_all':
    $comment_handler = xoops_gethandler('comment');
    $comment =& $comment_handler->get($com_id);
    $com_rootid = $comment->getVar('com_rootid');
    $thread_comments =& $comment_handler->getThread($com_rootid, $com_id);
    include_once XOOPS_ROOT_PATH.'/class/tree.php';
    $xot = new XoopsObjectTree($thread_comments, 'com_id', 'com_pid', 'com_rootid');
    $child_comments =& $xot->getAllChild($com_id);
    $child_comments[$com_id] =& $comment;
    $msgs = array();
    $deleted_num = array();
    $member_handler =& xoops_gethandler('member');
    foreach (array_keys($child_comments) as $i) {
        if (!$comment_handler->delete($child_comments[$i])) {
            $msgs[] = _CM_COMDELETENG.' (ID: '.$child_comments[$i]->getVar('com_id').')';
        } else {
            $msgs[] = _CM_COMDELETED.' (ID: '.$child_comments[$i]->getVar('com_id').')';
            $poster_id = $child_comments[$i]->getVar('com_uid');
            if ($poster_id > 0) {
                $deleted_num[$poster_id] = !isset($deleted_num[$poster_id]) ? 1 : ($deleted_num[$poster_id] + 1);
            }
        }
    }
    foreach ($deleted_num as $user_id => $post_num) {
        $com_poster = $member_handler->getUser($user_id);
        if (is_object($com_poster)) {
            $member_handler->updateUserByField($com_poster, 'posts', $com_poster->getVar('posts') - $post_num);
        }
    }
    $com_itemid = $comment->getVar('com_itemid');
    if (isset($comment_config['callback']['update']) && trim($comment_config['callback']['update']) != '') {
        $skip = false;
        if (!function_exists($comment_config['callback']['update'])) {
            if (isset($comment_config['callbackFile'])) {
                $callbackfile = trim($comment_config['callbackFile']);
                if ($callbackfile != '' && file_exists(XOOPS_ROOT_PATH.'/modules/'.$moddir.'/'.$callbackfile)) {
                    include_once XOOPS_ROOT_PATH.'/modules/'.$moddir.'/'.$callbackfile;
                }
                if (!function_exists($comment_config['callback']['update'])) {
                    $skip = true;
                }
            } else {
                $skip = true;
            }
        }
        if (!$skip) {
            $criteria = new CriteriaCompo(new Criteria('com_modid', $com_modid));
            $criteria->add(new Criteria('com_itemid', $com_itemid));
            $criteria->add(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));
            $comment_count = $comment_handler->getCount($criteria);
            $comment_config['callback']['update']($com_itemid, $comment_count);
        }
    }
    include XOOPS_ROOT_PATH.'/header.php';
    xoops_result($msgs);
    echo '<br /><a href="'.$redirect_page.'='.$com_itemid.'&amp;com_order='.$com_order.'&amp;com_mode='.$com_mode.'">'._BACK.'</a>';
    include XOOPS_ROOT_PATH.'/footer.php';
    break;
case 'delete':
default:
    include XOOPS_ROOT_PATH.'/header.php';
    $comment_confirm = array('com_id' => $com_id, 'com_mode' => $com_mode, 'com_order' => $com_order, 'op' => array(_CM_DELETEONE => 'delete_one', _CM_DELETEALL => 'delete_all'));
    if (!empty($comment_confirm_extra) && is_array($comment_confirm_extra)) {
        $comment_confirm = $comment_confirm + $comment_confirm_extra;
    }
    xoops_confirm($comment_confirm, 'comment_delete.php', _CM_DELETESELECT);
    include XOOPS_ROOT_PATH.'/footer.php';
    break;
}
?>