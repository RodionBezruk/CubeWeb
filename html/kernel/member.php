<?php
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH.'/kernel/user.php';
require_once XOOPS_ROOT_PATH.'/kernel/group.php';
class XoopsMemberHandler{
    var $_gHandler;
    var $_uHandler;
    var $_mHandler;
    var $_members = array();
    function XoopsMemberHandler(&$db)
    {
        $this->_gHandler =& new XoopsGroupHandler($db);
        $this->_uHandler =& new XoopsUserHandler($db);
        $this->_mHandler =& new XoopsMembershipHandler($db);
    }
    function &createGroup()
    {
        $ret =& $this->_gHandler->create();
        return $ret;
    }
    function &createUser()
    {
        $ret =& $this->_uHandler->create();
        return $ret;
    }
    function &getGroup($id)
    {
        $ret =& $this->_gHandler->get($id);
        return $ret;
    }
    function &getUser($id)
    {
        if (!isset($this->_members[$id])) {
            $this->_members[$id] =& $this->_uHandler->get($id);
        }
        return $this->_members[$id];
    }
	function &getUserByEmail($email)
	{
		$returnUser = null;
		$myts =& MyTextSanitizer::getInstance();	
		$users =& $this->getUsers(new Criteria('email'), $myts->addSlashes($email));
		if(!is_array($users)) {
			$returnUser=null;
		}
		else if(count($users) > 0) {
			$returnUser = is_object($users[0]) ? $users[0] : null;
		}
		return $returnUser;
	}
    function delete(&$object)
    {
        if (is_a($object, "XoopsUser")) {
            return $this->deleteUser($object);
        }
        elseif (is_a($object, "XoopsGroup")) {
            return $this->deleteGroup($object);
        }
    }
    function deleteGroup(&$group)
    {
        $this->_gHandler->delete($group);
        $this->_mHandler->deleteAll(new Criteria('groupid', $group->getVar('groupid')));
        return true;
    }
    function deleteUser(&$user)
    {
        $this->_uHandler->delete($user);
        $this->_mHandler->deleteAll(new Criteria('uid', $user->getVar('uid')));
        return true;
    }
    function insertGroup(&$group)
    {
        return $this->_gHandler->insert($group);
    }
    function insertUser(&$user, $force = false)
    {
        return $this->_uHandler->insert($user, $force);
    }
    function &getGroups($criteria = null, $id_as_key = false)
    {
        $groups =& $this->_gHandler->getObjects($criteria, $id_as_key);
        return $groups;
    }
    function &getUsers($criteria = null, $id_as_key = false)
    {
        $users =& $this->_uHandler->getObjects($criteria, $id_as_key);
        return $users;
    }
    function &getGroupList($criteria = null)
    {
        $groups =& $this->_gHandler->getObjects($criteria, true);
        $ret = array();
        foreach (array_keys($groups) as $i) {
            $ret[$i] = $groups[$i]->getVar('name');
        }
        return $ret;
    }
    function getUserList($criteria = null)
    {
        $users =& $this->_uHandler->getObjects($criteria, true);
        $ret = array();
        foreach (array_keys($users) as $i) {
            $ret[$i] = $users[$i]->getVar('uname');
        }
        return $ret;
    }
    function addUserToGroup($group_id, $user_id)
    {
        $group_ids =& $this->getGroupsByUser($user_id);
        if (!in_array($group_id, $group_ids)) {
            $mship =& $this->_mHandler->create();
            $mship->setVar('groupid', $group_id);
            $mship->setVar('uid', $user_id);
            return $this->_mHandler->insert($mship);
        }
        return true;
    }
    function removeUserFromGroup($group_id, $user_id)
    {
        $user_ids = array($user_id);
        return $this->removeUsersFromGroup($group_id, $user_ids);
    }
    function removeUsersFromGroup($group_id, $user_ids = array())
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('groupid', $group_id));
        $criteria2 = new CriteriaCompo();
        foreach ($user_ids as $uid) {
            $criteria2->add(new Criteria('uid', $uid), 'OR');
        }
        $criteria->add($criteria2);
        return $this->_mHandler->deleteAll($criteria);
    }
    function &getUsersByGroup($group_id, $asobject = false, $limit = 0, $start = 0)
    {
        $user_ids =& $this->_mHandler->getUsersByGroup($group_id, $limit, $start);
        if (!$asobject) {
           return $user_ids;
        } else {
           $ret = array();
           foreach ($user_ids as $u_id) {
               $user =& $this->getUser($u_id);
                if (is_object($user)) {
                    $ret[] =& $user;
                }
                unset($user);
           }
           return $ret;
        }
    }
    function &getUsersByNoGroup($group_id, $asobject = false, $limit = 0, $start = 0)
    {
        $user_ids =& $this->_mHandler->getUsersByNoGroup($group_id, $limit, $start);
        if (!$asobject) {
           return $user_ids;
        } else {
           $ret = array();
           foreach ($user_ids as $u_id) {
               $user =& $this->getUser($u_id);
                if (is_object($user)) {
                    $ret[] =& $user;
                }
                unset($user);
           }
           return $ret;
        }
    }
    function &getGroupsByUser($user_id, $asobject = false)
    {
        $group_ids =& $this->_mHandler->getGroupsByUser($user_id);
        if (!$asobject) {
           return $group_ids;
        } else {
		   $ret = array();
           foreach ($group_ids as $g_id) {
               $ret[] =& $this->getGroup($g_id);
           }
           return $ret;
        }
    }
    function &loginUser($uname, $pwd)
    {
        $criteria = new CriteriaCompo(new Criteria('uname', $uname));
        $criteria->add(new Criteria('pass', md5($pwd)));
        $user =& $this->_uHandler->getObjects($criteria, false);
        if (!$user || count($user) != 1) {
			$ret = false;
            return $ret;
        }
        return $user[0];
    }
    function &loginUserMd5($uname, $md5pwd)
    {
        $criteria = new CriteriaCompo(new Criteria('uname', $uname));
        $criteria->add(new Criteria('pass', $md5pwd));
        $user =& $this->_uHandler->getObjects($criteria, false);
        if (!$user || count($user) != 1) {
            $ret = false;
            return $ret;
        }
        return $user[0];
    }
    function getUserCount($criteria = null)
    {
        return $this->_uHandler->getCount($criteria);
    }
    function getUserCountByGroup($group_id)
    {
        return $this->_mHandler->getCount(new Criteria('groupid', $group_id));
    }
    function getUserCountByNoGroup($group_id)
    {
        $groupid = intval($group_id);
        $usersTable = $this->_mHandler->db->prefix('users');
        $linkTable = $this->_mHandler->db->prefix('groups_users_link');
        $sql = "SELECT count(*) FROM ${usersTable} u LEFT JOIN ${linkTable} g ON u.uid=g.uid," .
                "${usersTable} u2 LEFT JOIN ${linkTable} g2 ON u2.uid=g2.uid AND g2.groupid=${groupid} " .
                "WHERE (g.groupid != ${groupid} OR g.groupid IS NULL) " .
                "AND (g2.groupid = ${groupid} OR g2.groupid IS NULL) " .
                "AND u.uid = u2.uid AND g2.uid IS NULL GROUP BY u.uid";
        $result = $this->_mHandler->db->query($sql);
        if (!$result) {
            return 0;
        }
        $count = $this->_mHandler->db->getRowsNum($result);
        return $count;
    }
    function updateUserByField(&$user, $fieldName, $fieldValue)
    {
        $user->setVar($fieldName, $fieldValue);
        return $this->insertUser($user);
    }
    function updateUsersByField($fieldName, $fieldValue, $criteria = null)
    {
        return $this->_uHandler->updateAll($fieldName, $fieldValue, $criteria);
    }
    function activateUser(&$user)
    {
        if ($user->getVar('level') != 0) {
            return true;
        }
        $user->setVar('level', 1);
        return $this->_uHandler->insert($user, true);
    }
}
?>
