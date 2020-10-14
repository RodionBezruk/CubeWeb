<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsGroup extends XoopsObject
{
    function XoopsGroup()
    {
        $this->XoopsObject();
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 100);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false);
        $this->initVar('group_type', XOBJ_DTYPE_OTHER, null, false);
    }
}
class XoopsGroupHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $group =& new XoopsGroup();
        if ($isNew) {
            $group->setNew();
        }
        $group->setVar('group_type', 'User');
        return $group;
    }
    function &get($id)
    {
        $ret = false;
        if (intval($id) > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('groups').' WHERE groupid='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                    $group = new XoopsGroup();
                    $group->assignVars($this->db->fetchArray($result));
                        $ret =& $group;
                }
            }
        }
        return $ret;
    }
    function insert(&$group)
    {
        if (strtolower(get_class($group)) != 'xoopsgroup') {
            return false;
        }
        if (!$group->isDirty()) {
            return true;
        }
        if (!$group->cleanVars()) {
            return false;
        }
        foreach ($group->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($group->isNew()) {
            $groupid = $this->db->genId('group_groupid_seq');
            $sql = sprintf("INSERT INTO %s (groupid, name, description, group_type) VALUES (%u, %s, %s, %s)", $this->db->prefix('groups'), $groupid, $this->db->quoteString($name), $this->db->quoteString($description), $this->db->quoteString($group_type));
        } else {
            $sql = sprintf("UPDATE %s SET name = %s, description = %s, group_type = %s WHERE groupid = %u", $this->db->prefix('groups'), $this->db->quoteString($name), $this->db->quoteString($description), $this->db->quoteString($group_type), $groupid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($groupid)) {
            $groupid = $this->db->getInsertId();
        }
        $group->assignVar('groupid', $groupid);
        return true;
    }
    function delete(&$group)
    {
        if (strtolower(get_class($group)) != 'xoopsgroup') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE groupid = %u", $this->db->prefix('groups'), $group->getVar('groupid'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('groups');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $group =& new XoopsGroup();
            $group->assignVars($myrow);
			if (!$id_as_key) {
            	$ret[] =& $group;
			} else {
				$ret[$myrow['groupid']] =& $group;
			}
            unset($group);
        }
        return $ret;
    }
}
class XoopsMembership extends XoopsObject
{
    function XoopsMembership()
    {
        $this->XoopsObject();
        $this->initVar('linkid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
    }
}
class XoopsMembershipHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $mship =& new XoopsMembership();
        if ($isNew) {
            $mship->setNew();
        }
        return $mship;
    }
    function &get($id)
    {
        $ret = false;
        if (intval($id) > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('groups_users_link').' WHERE linkid='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $mship =& new XoopsMembership();
                    $mship->assignVars($this->db->fetchArray($result));
                        $ret =& $mship;
                }
            }
        }
        return $ret;
    }
    function insert(&$mship)
    {
        if (strtolower(get_class($mship)) != 'xoopsmembership') {
            return false;
        }
        if (!$mship->isDirty()) {
            return true;
        }
        if (!$mship->cleanVars()) {
            return false;
        }
        foreach ($mship->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($mship->isNew()) {
            $linkid = $this->db->genId('groups_users_link_linkid_seq');
            $sql = sprintf("INSERT INTO %s (linkid, groupid, uid) VALUES (%u, %u, %u)", $this->db->prefix('groups_users_link'), $linkid, $groupid, $uid);
        } else {
            $sql = sprintf("UPDATE %s SET groupid = %u, uid = %u WHERE linkid = %u", $this->db->prefix('groups_users_link'), $groupid, $uid, $linkid);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($linkid)) {
            $linkid = $this->db->getInsertId();
        }
        $mship->assignVar('linkid', $linkid);
        return true;
    }
    function delete(&$mship)
    {
        if (strtolower(get_class($mship)) != 'xoopsmembership') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE linkid = %u", $this->db->prefix('groups_users_link'), $groupm->getVar('linkid'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('groups_users_link');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $mship = new XoopsMembership();
            $mship->assignVars($myrow);
			if (!$id_as_key) {
            	$ret[] =& $mship;
			} else {
				$ret[$myrow['linkid']] =& $mship;
			}
            unset($mship);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('groups_users_link');
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
        $sql = 'DELETE FROM '.$this->db->prefix('groups_users_link');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getGroupsByUser($uid)
    {
        $ret = array();
        $sql = 'SELECT groupid FROM '.$this->db->prefix('groups_users_link').' WHERE uid='.intval($uid);
        $result = $this->db->query($sql);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow['groupid'];
        }
        return $ret;
    }
    function &getUsersByGroup($groupid, $limit=0, $start=0)
    {
        $ret = array();
        $sql = 'SELECT uid FROM ' . $this->db->prefix('groups_users_link') . ' WHERE groupid='.intval($groupid);
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow['uid'];
        }
        return $ret;
    }
    function &getUsersByNoGroup($groupid, $limit=0, $start=0)
    {
        $ret = array();
        $groupid = intval($groupid);
        $usersTable = $this->db->prefix('users');
        $linkTable = $this->db->prefix('groups_users_link');
        $sql = "SELECT u.uid FROM ${usersTable} u LEFT JOIN ${linkTable} g ON u.uid=g.uid," .
                "${usersTable} u2 LEFT JOIN ${linkTable} g2 ON u2.uid=g2.uid AND g2.groupid=${groupid} " .
                "WHERE (g.groupid != ${groupid} OR g.groupid IS NULL) " .
                "AND (g2.groupid = ${groupid} OR g2.groupid IS NULL) " .
                "AND u.uid = u2.uid AND g2.uid IS NULL GROUP BY u.uid";
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow['uid'];
        }
        return $ret;
    }
}
?>
