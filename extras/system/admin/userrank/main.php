<?php
if ( !is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid()) ) {
    exit("Access Denied");
}
$op = 'RankForumAdmin';
if (isset($_GET['op'])) {
    $op = $_GET['op'];
} elseif (isset($_POST['op'])) {
    $op = $_POST['op'];
}
switch ($op) {
case "RankForumEdit":
    $rank_id = isset($_GET['rank_id']) ? intval($_GET['rank_id']) : 0;
    if ($rank_id > 0) {
        include_once XOOPS_ROOT_PATH."/modules/system/admin/userrank/userrank.php";
        RankForumEdit($rank_id);
    }
    break;
case "RankForumDel":
    $rank_id = isset($_GET['rank_id']) ? intval($_GET['rank_id']) : 0;
    if ($rank_id > 0) {
        xoops_cp_header();
        xoops_token_confirm(array('fct' => 'userrank', 'op' => 'RankForumDelGo', 'rank_id' => $rank_id), 'admin.php', _AM_WAYSYWTDTR);
        xoops_cp_footer();
    }
    break;
case "RankForumDelGo":
    $rank_id = isset($_POST['rank_id']) ? intval($_POST['rank_id']) : 0;
    if ($rank_id <= 0 || !xoops_confirm_validate()) {
        redirect_header("admin.php?fct=userrank");
    }
    $db =& Database::getInstance();
    $sql = sprintf("DELETE FROM %s WHERE rank_id = %u", $db->prefix("ranks"), $rank_id);
    $db->query($sql);
    redirect_header("admin.php?fct=userrank&amp;op=ForumAdmin",1,_AM_DBUPDATED);
    break;
case "RankForumAdd":
    if (!XoopsMultiTokenHandler::quickValidate('userrank_RankForumAdd')) {
        redirect_header("admin.php?fct=userrank");
    }
    $db =& Database::getInstance();
    $myts =& MyTextSanitizer::getInstance();
    $rank_special = isset($_POST['rank_special']) && intval($_POST['rank_special']) ? 1 : 0;
    $rank_title = $myts->stripSlashesGPC($_POST['rank_title']);
    $rank_image = '';
    include_once XOOPS_ROOT_PATH.'/class/uploader.php';
    $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png'), 100000, 120, 120);
    $uploader->setAllowedExtensions(array('gif', 'jpeg', 'jpg', 'png'));
    $uploader->setPrefix('rank');
    if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
        if ($uploader->upload()) {
            $rank_image = $uploader->getSavedFileName();
        }
    }
    $newid = $db->genId($db->prefix("ranks")."_rank_id_seq");
    if ($rank_special == 1) {
        $sql = "INSERT INTO ".$db->prefix("ranks")." (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ($newid, ".$db->quoteString($rank_title).", -1, -1, 1, ".$db->quoteString($rank_image).")";
    } else {
        $sql = "INSERT INTO ".$db->prefix("ranks")." (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ($newid, ".$db->quoteString($rank_title).", ".intval($_POST['rank_min'])." , ".intval($_POST['rank_max'])." , 0, ".$db->quoteString($rank_image).")";
    }
    if (!$db->query($sql)) {
        xoops_cp_header();
        xoops_error('Failed storing rank data into the database');
        xoops_cp_footer();
    } else {
        redirect_header("admin.php?fct=userrank&amp;op=RankForumAdmin",1,_AM_DBUPDATED);
    }
    break;
case "RankForumSave":
    $rank_id = isset($_POST['rank_id']) ? intval($_POST['rank_id']) : 0;
    if ($rank_id <= 0 || !XoopsMultiTokenHandler::quickValidate('userrank_RankForumSave')) {
        redirect_header("admin.php?fct=userrank");
    }
    $db =& Database::getInstance();
    $myts =& MyTextSanitizer::getInstance();
    $rank_special = isset($_POST['rank_special']) && intval($_POST['rank_special']) ? 1 : 0;
    $rank_title = $myts->stripSlashesGPC($_POST['rank_title']);
    $delete_old_image = false;
    include_once XOOPS_ROOT_PATH.'/class/uploader.php';
    $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png'), 100000, 120, 120);
    $uploader->setAllowedExtensions(array('gif', 'jpeg', 'jpg', 'png'));
    $uploader->setPrefix('rank');
    if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
        if ($uploader->upload()) {
            $rank_image = $uploader->getSavedFileName();
            $delete_old_image = true;
        }
    }
    if ($rank_special > 0) {
        $_POST['rank_min'] = $_POST['rank_max'] = -1;
    }
    $sql = "UPDATE ".$db->prefix("ranks")." SET rank_title = ".$db->quoteString($rank_title).", rank_min = ".intval($_POST['rank_min']).", rank_max = ".intval($_POST['rank_max']).", rank_special = ".$rank_special;
    if ($delete_old_image) {
        $sql .= ", rank_image = ".$db->quoteString($rank_image);
    }
    $sql .= " WHERE rank_id = ".$rank_id;
    if (!$db->query($sql)) {
        xoops_cp_header();
        xoops_error('Failed storing rank data into the database');
        xoops_cp_footer();
    } else {
        if ($delete_old_image) {
            $old_rank_path = str_replace("\\", "/", realpath(XOOPS_UPLOAD_PATH.'/'.trim($_POST['old_rank'])));
            if (0 === strpos($old_rank_path, XOOPS_UPLOAD_PATH) && is_file($old_rank_path)) {
                unlink($old_rank_path);
            }
        }
        redirect_header("admin.php?fct=userrank&amp;op=RankForumAdmin",1,_AM_DBUPDATED);
    }
    break;
default:
    include_once XOOPS_ROOT_PATH."/modules/system/admin/userrank/userrank.php";
    RankForumAdmin();
    break;
}
?>
