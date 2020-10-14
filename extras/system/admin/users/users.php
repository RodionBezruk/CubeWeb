<?php
if ( !is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid()) ) {
    exit("Access Denied");
}
include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
function system_users_error($message)
{
    xoops_cp_header();
    xoops_error($message);
    xoops_cp_footer();
    exit();
}
function displayUsers()
{
    global $xoopsDB, $xoopsConfig, $xoopsModule;
    $userstart = isset($_GET['userstart']) ? intval($_GET['userstart']) : 0;
    xoops_cp_header();
    $member_handler =& xoops_gethandler('member');
    $usercount = $member_handler->getUserCount();
    $nav = new XoopsPageNav($usercount, 200, $userstart, "userstart", "fct=users");
    $editform = new XoopsThemeForm(_AM_EDEUSER, "edituser", "admin.php", 'GET');
    $user_select = new XoopsFormSelect('', "uid");
    $criteria = new CriteriaCompo();
    $criteria->setSort('uname');
    $criteria->setOrder('ASC');
    $criteria->setLimit(200);
    $criteria->setStart($userstart);
    $user_select->addOptionArray($member_handler->getUserList($criteria));
    $user_select_tray = new XoopsFormElementTray(_AM_NICKNAME, "<br />");
    $user_select_tray->addElement($user_select);
    $user_select_nav = new XoopsFormLabel('', $nav->renderNav(4));
    $user_select_tray->addElement($user_select_nav);
    $op_select = new XoopsFormSelect("", "op");
    $op_select->addOptionArray(array("modifyUser"=>_AM_MODIFYUSER, "delUser"=>_AM_DELUSER));
    $submit_button = new XoopsFormButton("", "submit", _AM_GO, "submit");
    $fct_hidden = new XoopsFormHidden("fct", "users");
    $editform->addElement($user_select_tray);
    $editform->addElement($op_select);
    $editform->addElement($submit_button);
    $editform->addElement($fct_hidden);
    $editform->display();
    echo "<br />\n";
    $uid_value = "";
    $uname_value = "";
    $name_value = "";
    $email_value = "";
    $email_cbox_value = 0;
    $url_value = "";
    $timezone_value = $xoopsConfig['default_TZ'];
    $icq_value = "";
    $aim_value = "";
    $yim_value = "";
    $msnm_value = "";
    $location_value = "";
    $occ_value = "";
    $interest_value = "";
    $sig_value = "";
    $sig_cbox_value = 0;
    $umode_value = $xoopsConfig['com_mode'];
    $uorder_value = $xoopsConfig['com_order'];
    include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
    $notify_method_value = XOOPS_NOTIFICATION_METHOD_PM;
    $notify_mode_value = XOOPS_NOTIFICATION_MODE_SENDALWAYS;
    $bio_value = "";
    $rank_value = 0;
    $mailok_value = 0;
    $op_value = "addUser";
    $form_title = _AM_ADDUSER;
    $form_isedit = false;
    $groups = array(XOOPS_GROUP_USERS);
    include XOOPS_ROOT_PATH."/modules/system/admin/users/userform.php";
        xoops_cp_footer();
}
function modifyUser($user)
{
    global $xoopsDB, $xoopsConfig, $xoopsModule;
    xoops_cp_header();
    $member_handler =& xoops_gethandler('member');
    $user =& $member_handler->getUser($user);
    if (is_object($user)) {
        if (!$user->isActive()) {
            xoops_token_confirm(array('fct' => 'users', 'op' => 'reactivate', 'uid' => $user->getVar('uid')), 'admin.php', _AM_NOTACTIVE);
            xoops_cp_footer();
            exit();
        }
        $uid_value = $user->getVar("uid");
        $uname_value = $user->getVar("uname", "E");
        $name_value = $user->getVar("name", "E");
        $email_value = $user->getVar("email", "E");
        $email_cbox_value = $user->getVar("user_viewemail") ? 1 : 0;
        $url_value = $user->getVar("url", "E");
        $temp = $user->getVar("theme");
        $timezone_value = $user->getVar("timezone_offset");
        $icq_value = $user->getVar("user_icq", "E");
        $aim_value = $user->getVar("user_aim", "E");
        $yim_value = $user->getVar("user_yim", "E");
        $msnm_value = $user->getVar("user_msnm", "E");
        $location_value = $user->getVar("user_from", "E");
        $occ_value = $user->getVar("user_occ", "E");
        $interest_value = $user->getVar("user_intrest", "E");
        $sig_value = $user->getVar("user_sig", "E");
        $sig_cbox_value = ($user->getVar("attachsig") == 1) ? 1 : 0;
        $umode_value = $user->getVar("umode");
        $uorder_value = $user->getVar("uorder");
        $notify_method_value = $user->getVar("notify_method");
        $notify_mode_value = $user->getVar("notify_mode");
        $bio_value = $user->getVar("bio", "E");
        $rank_value = $user->rank(false);
        $mailok_value = $user->getVar('user_mailok', 'E');
        $op_value = "updateUser";
        $form_title = _AM_UPDATEUSER.": ".$user->getVar("uname");
        $form_isedit = true;
        $groups = array_values($user->getGroups());
        $token = XoopsMultiTokenHandler::quickCreate('users_synchronize');
        include XOOPS_ROOT_PATH."/modules/system/admin/users/userform.php";
        echo "<br /><b>"._AM_USERPOST."</b><br /><br />\n";
        echo "<table>\n";
        echo "<tr><td>"._AM_COMMENTS."</td><td>".$user->getVar("posts")."</td></tr>\n";
        echo "</table>\n";
        echo "<br />"._AM_PTBBTSDIYT."<br />\n";
        echo "<form action=\"admin.php\" method=\"post\">\n";
        echo $token->getHtml();
        echo "<input type=\"hidden\" name=\"id\" value=\"".$user->getVar("uid")."\" />";
        echo "<input type=\"hidden\" name=\"type\" value=\"user\" />\n";
        echo "<input type=\"hidden\" name=\"fct\" value=\"users\" />\n";
        echo "<input type=\"hidden\" name=\"op\" value=\"synchronize\" />\n";
        echo "<input type=\"submit\" value=\""._AM_SYNCHRONIZE."\" />\n";
        echo "</form>\n";
    } else {
        echo "<h4 style='text-align:left;'>";
        echo _AM_USERDONEXIT;
        echo "</h4>";
    }
    xoops_cp_footer();
}
function synchronize($id, $type)
{
    global $xoopsDB;
    switch($type) {
    case 'user':
        $id = intval($id);
        $tables = array();
        include_once XOOPS_ROOT_PATH . '/include/comment_constants.php';
        $tables[] = array ('table_name' => 'xoopscomments', 'uid_column' => 'com_uid', 'criteria' => new Criteria('com_status', XOOPS_COMMENT_ACTIVE));
        $tables[] = array ('table_name' => 'bb_posts', 'uid_column' => 'uid');
        $total_posts = 0;
        foreach ($tables as $table) {
            $criteria = new CriteriaCompo();
            $criteria->add (new Criteria($table['uid_column'], $id));
            if (!empty($table['criteria'])) {
                $criteria->add ($table['criteria']);
            }
            $sql = "SELECT COUNT(*) AS total FROM ".$xoopsDB->prefix($table['table_name']) . ' ' . $criteria->renderWhere();
            if ( $result = $xoopsDB->query($sql) ) {
                if ($row = $xoopsDB->fetchArray($result)) {
                    $total_posts = $total_posts + $row['total'];
                }
            }
        }
        $sql = "UPDATE ".$xoopsDB->prefix("users")." SET posts = $total_posts WHERE uid = $id";
        if ( !$result = $xoopsDB->query($sql) ) {
            exit(sprintf(_AM_CNUUSER %s ,$id));
        }
        break;
    case 'all users':
        $sql = "SELECT uid FROM ".$xoopsDB->prefix("users")."";
        if ( !$result = $xoopsDB->query($sql) ) {
            exit(_AM_CNGUSERID);
        }
        while ($row = $xoopsDB->fetchArray($result)) {
            $id = $row['uid'];
            synchronize($id, "user");
        }
        break;
    default:
        break;
    }
    redirect_header("admin.php?fct=users&amp;op=modifyUser&amp;uid=".$id,1,_AM_DBUPDATED);
    exit();
}
?>
