<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsUser extends XoopsObject
{
    var $_groups = array();
    var $_isAdmin = null;
    var $_rank = null;
    var $_isOnline = null;
    function XoopsUser($id = null)
    {
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 60);
        $this->initVar('uname', XOBJ_DTYPE_TXTBOX, null, true, 25);
        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, true, 60);
        $this->initVar('url', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('user_avatar', XOBJ_DTYPE_TXTBOX, null, false, 30);
        $this->initVar('user_regdate', XOBJ_DTYPE_INT, null, false);
        $this->initVar('user_icq', XOBJ_DTYPE_TXTBOX, null, false, 15);
        $this->initVar('user_from', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('user_sig', XOBJ_DTYPE_TXTAREA, null, false, null);
        $this->initVar('user_viewemail', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('actkey', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('user_aim', XOBJ_DTYPE_TXTBOX, null, false, 18);
        $this->initVar('user_yim', XOBJ_DTYPE_TXTBOX, null, false, 25);
        $this->initVar('user_msnm', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('pass', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('posts', XOBJ_DTYPE_INT, null, false);
        $this->initVar('attachsig', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rank', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('level', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('theme', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('timezone_offset', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('last_login', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('umode', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('uorder', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('notify_method', XOBJ_DTYPE_OTHER, 1, false);
        $this->initVar('notify_mode', XOBJ_DTYPE_OTHER, 0, false); 
        $this->initVar('user_occ', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('bio', XOBJ_DTYPE_TXTAREA, null, false, null);
        $this->initVar('user_intrest', XOBJ_DTYPE_TXTBOX, null, false, 150);
        $this->initVar('user_mailok', XOBJ_DTYPE_INT, 1, false);
        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            } else {
                $member_handler =& xoops_gethandler('member');
                $user =& $member_handler->getUser($id);
                foreach ($user->vars as $k => $v) {
                    $this->assignVar($k, $v['value']);
                }
            }
        }
    }
	function isGuest()
	{
		return false;
	}
    function getUnameFromId( $userid, $usereal = 0 )
    {
		$userid = intval($userid);
		$usereal = intval($usereal);
		if ($userid > 0) {
            $member_handler =& xoops_gethandler('member');
            $user =& $member_handler->getUser($userid);
            if (is_object($user)) {
                $ts =& MyTextSanitizer::getInstance();
                if ( $usereal ) { 
					return $ts->htmlSpecialChars($user->getVar('name'));
            	} else {
					return $ts->htmlSpecialChars($user->getVar('uname'));
				}
            }
        }
        return $GLOBALS['xoopsConfig']['anonymous'];
    }
    function incrementPost(){
        $member_handler =& xoops_gethandler('member');
        return $member_handler->updateUserByField($this, 'posts', $this->getVar('posts') + 1);
    }
	function setGroups($groupsArr)
	{
		if (is_array($groupsArr)) {
			$this->_groups =& $groupsArr;
		}
	}
    function getGroups($bReget = false)
    {
    	if ($bReget) {
    		unset($this->_groups);
    	}
        if (empty($this->_groups)) {
            $member_handler =& xoops_gethandler('member');
            $this->_groups = $member_handler->getGroupsByUser($this->getVar('uid'));
        }
        return $this->_groups;
    }
    function getNumGroups()
	{
		if (empty($this->_groups)) {
			$this->getGroups();
		}
		return count($this->_groups);
	}
    function groups()
    {
        return $this->getGroups();
    }
    function isAdmin( $module_id = null ) {
		if ( is_null( $module_id ) ) {
			$module_id = isset($GLOBALS['xoopsModule']) ? $GLOBALS['xoopsModule']->getVar( 'mid', 'n' ) : 1;
		} elseif ( intval($module_id) < 1 ) {
			$module_id = 0;
		}
        $moduleperm_handler =& xoops_gethandler('groupperm');
        return $moduleperm_handler->checkRight('module_admin', $module_id, $this->getGroups());
    }
    function rank()
    {
        if (!isset($this->_rank)) {
            $this->_rank = xoops_getrank($this->getVar('rank'), $this->getVar('posts'));
        }
        return $this->_rank;
    }
    function isActive()
    {
        if ($this->getVar('level') == 0) {
            return false;
        }
        return true;
    }
    function isOnline()
    {
        if (!isset($this->_isOnline)) {
            $onlinehandler =& xoops_gethandler('online');
            $this->_isOnline = ($onlinehandler->getCount(new Criteria('online_uid', $this->getVar('uid'))) > 0) ? true : false;
        }
        return $this->_isOnline;
    }
    function uid()
    {
        return $this->getVar("uid");
    }
    function name($format="S")
    {
        return $this->getVar("name", $format);
    }
    function uname($format="S")
    {
        return $this->getVar("uname", $format);
    }
    function email($format="S")
    {
        return $this->getVar("email", $format);
    }
    function url($format="S")
    {
        return $this->getVar("url", $format);
    }
    function user_avatar($format="S")
    {
        return $this->getVar("user_avatar");
    }
    function user_regdate()
    {
        return $this->getVar("user_regdate");
    }
    function user_icq($format="S")
    {
        return $this->getVar("user_icq", $format);
    }
    function user_from($format="S")
    {
        return $this->getVar("user_from", $format);
    }
    function user_sig($format="S")
    {
        return $this->getVar("user_sig", $format);
    }
    function user_viewemail()
    {
        return $this->getVar("user_viewemail");
    }
    function actkey()
    {
        return $this->getVar("actkey");
    }
    function user_aim($format="S")
    {
        return $this->getVar("user_aim", $format);
    }
    function user_yim($format="S")
    {
        return $this->getVar("user_yim", $format);
    }
    function user_msnm($format="S")
    {
        return $this->getVar("user_msnm", $format);
    }
    function pass()
    {
        return $this->getVar("pass");
    }
    function posts()
    {
        return $this->getVar("posts");
    }
    function attachsig()
    {
        return $this->getVar("attachsig");
    }
    function level()
    {
        return $this->getVar("level");
    }
    function theme()
    {
        return $this->getVar("theme");
    }
    function timezone()
    {
        return $this->getVar("timezone_offset");
    }
    function umode()
    {
        return $this->getVar("umode");
    }
    function uorder()
    {
        return $this->getVar("uorder");
    }
    function notify_method()
    {
        return $this->getVar("notify_method");
    }
    function notify_mode()
    {
        return $this->getVar("notify_mode");
    }
    function user_occ($format="S")
    {
        return $this->getVar("user_occ", $format);
    }
    function bio($format="S")
    {
        return $this->getVar("bio", $format);
    }
    function user_intrest($format="S")
    {
        return $this->getVar("user_intrest", $format);
    }
    function last_login()
    {
        return $this->getVar("last_login");
    }
	function hasAvatar()
	{
		$avatar=$this->getVar('user_avatar');
		if(!$avatar || $avatar=="blank.gif")
			return false;
		$file=XOOPS_UPLOAD_PATH."/".$avatar;
		return file_exists($file);
	}
	function getAvatarUrl()
	{
		if($this->hasAvatar())
			return XOOPS_UPLOAD_URL."/".$this->getVar('user_avatar');
		return null;
	}
}
class XoopsGuestUser extends XoopsUser
{
	function isGuest()
	{
		return true;
	}
	function getGroups()
	{
		return XOOPS_GROUP_ANONYMOUS;
	}
}
class XoopsUserHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $user =& new XoopsUser();
        if ($isNew) {
            $user->setNew();
        }
        return $user;
    }
    function &get($id)
    {
        $ret = false;
        if (intval($id) > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('users').' WHERE uid='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $user =& new XoopsUser();
                    $user->assignVars($this->db->fetchArray($result));
                        $ret =& $user;
                }
            }
        }
        return $ret;
    }
    function insert(&$user, $force = false)
    {
        if (strtolower(get_class($user)) != 'xoopsuser') {
            return false;
        }
        if (!$user->isDirty()) {
            return true;
        }
        if (!$user->cleanVars()) {
            return false;
        }
        foreach ($user->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($user->isNew()) {
            $uid = $this->db->genId('users_uid_seq');
            $sql = sprintf("INSERT INTO %s (uid, uname, name, email, url, user_avatar, user_regdate, user_icq, user_from, user_sig, user_viewemail, actkey, user_aim, user_yim, user_msnm, pass, posts, attachsig, rank, level, theme, timezone_offset, last_login, umode, uorder, notify_method, notify_mode, user_occ, bio, user_intrest, user_mailok) VALUES (%u, %s, %s, %s, %s, %s, %u, %s, %s, %s, %u, %s, %s, %s, %s, %s, %u, %u, %u, %u, %s, %.2f, %u, %s, %u, %u, %u, %s, %s, %s, %u)", $this->db->prefix('users'), $uid, $this->db->quoteString($uname), $this->db->quoteString($name), $this->db->quoteString($email), $this->db->quoteString($url), $this->db->quoteString($user_avatar), time(), $this->db->quoteString($user_icq), $this->db->quoteString($user_from), $this->db->quoteString($user_sig), $user_viewemail, $this->db->quoteString($actkey), $this->db->quoteString($user_aim), $this->db->quoteString($user_yim), $this->db->quoteString($user_msnm), $this->db->quoteString($pass), $posts, $attachsig, $rank, $level, $this->db->quoteString($theme), $timezone_offset, 0, $this->db->quoteString($umode), $uorder, $notify_method, $notify_mode, $this->db->quoteString($user_occ), $this->db->quoteString($bio), $this->db->quoteString($user_intrest), $user_mailok);
        } else {
            $sql = sprintf("UPDATE %s SET uname = %s, name = %s, email = %s, url = %s, user_avatar = %s, user_icq = %s, user_from = %s, user_sig = %s, user_viewemail = %u, user_aim = %s, user_yim = %s, user_msnm = %s, posts = %d,  pass = %s, attachsig = %u, rank = %u, level= %u, theme = %s, timezone_offset = %.2f, umode = %s, last_login = %u, uorder = %u, notify_method = %u, notify_mode = %u, user_occ = %s, bio = %s, user_intrest = %s, user_mailok = %u WHERE uid = %u", $this->db->prefix('users'), $this->db->quoteString($uname), $this->db->quoteString($name), $this->db->quoteString($email), $this->db->quoteString($url), $this->db->quoteString($user_avatar), $this->db->quoteString($user_icq), $this->db->quoteString($user_from), $this->db->quoteString($user_sig), $user_viewemail, $this->db->quoteString($user_aim), $this->db->quoteString($user_yim), $this->db->quoteString($user_msnm), $posts, $this->db->quoteString($pass), $attachsig, $rank, $level, $this->db->quoteString($theme), $timezone_offset, $this->db->quoteString($umode), $last_login, $uorder, $notify_method, $notify_mode, $this->db->quoteString($user_occ), $this->db->quoteString($bio), $this->db->quoteString($user_intrest), $user_mailok, $uid);
        }
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        if (empty($uid)) {
            $uid = $this->db->getInsertId();
        }
        $user->assignVar('uid', $uid);
        return true;
    }
    function delete(&$user, $force = false)
    {
        if (strtolower(get_class($user)) != 'xoopsuser') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE uid = %u", $this->db->prefix("users"), $user->getVar('uid'));
        if (false != $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('users');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $user =& new XoopsUser();
            $user->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $user;
            } else {
                $ret[$myrow['uid']] =& $user;
            }
            unset($user);
        }
        return $ret;
    }
    function &getObjectsByLevel($level=0)
    {
		$ret=array();
		$level=intval($level);
		$result = $this->db->query("SELECT * FROM ".$this->db->prefix("users")." WHERE level > $level ORDER BY uname");
		if(!$result)
			return $ret;
		while($myrow=$this->db->fetchArray($result)) {
			$user=new XoopsUser();
			$user->assignVars($myrow);
			$ret[]=&$user;
			unset($user);
		}
		return $ret;
	}
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('users');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
    function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM '.$this->db->prefix('users');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function updateAll($fieldname, $fieldvalue, $criteria = null)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname.' = '.$fieldvalue : $fieldname.' = '.$this->db->quoteString($fieldvalue);
        $sql = 'UPDATE '.$this->db->prefix('users').' SET '.$set_clause;
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
}
?>
