<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_Wating extends XCube_ActionFilter {
    function preBlockFilter()
    {
        $this->mController->mRoot->mDelegateManager->add('Legacyblock.Wating.Show',array(&$this,"callbackWatingShow"));
    }
    function callbackWatingShow(&$modules) {
        $xoopsDB =& Database::getInstance();
        $module_handler =& xoops_gethandler('module');
        if ($module_handler->getCount(new Criteria('dirname', 'news'))) {
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("stories")." WHERE published=0");
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/news/admin/index.php?op=newarticle";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_SUBMS;
                $modules[] = $blockVal;
            }
        }
        if ($module_handler->getCount(new Criteria('dirname', 'mylinks'))) {
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_links")." WHERE status=0");
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mylinks/admin/index.php?op=listNewLinks";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_WLNKS;
                $modules[] = $blockVal;
            }
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_broken"));
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mylinks/admin/index.php?op=listBrokenLinks";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_BLNK;
                $modules[] = $blockVal;
            }
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_mod"));
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mylinks/admin/index.php?op=listModReq";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_MLNKS;
                $modules[] = $blockVal;
            }
        }
        if ($module_handler->getCount(new Criteria('dirname', 'mydownloads'))) {
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_downloads")." WHERE status=0");
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mydownloads/admin/index.php?op=listNewDownloads";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_WDLS;
                $modules[] = $blockVal;
            }
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_broken")."");
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mydownloads/admin/index.php?op=listBrokenDownloads";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_BFLS;
                $modules[] = $blockVal;
            }
            $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_mod")."");
            if ( $result ) {
                $blockVal = array();
                $blockVal['adminlink'] = XOOPS_URL."/modules/mydownloads/admin/index.php?op=listModReq";
                list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
                $blockVal['lang_linkname'] = _MB_LEGACY_MFLS;
                $modules[] = $blockVal;
            }
        }
        $result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("xoopscomments")." WHERE com_status=1");
        if ( $result ) {
            $blockVal = array();
            $blockVal['adminlink'] = XOOPS_URL."/modules/legacy/admin/index.php?action=CommentList&amp;com_modid=0&amp;com_status=1";
            list($blockVal['pendingnum']) = $xoopsDB->fetchRow($result);
            $blockVal['lang_linkname'] =_MB_LEGACY_COMPEND;
            $modules[] = $blockVal;
        }
    }
}
?>
