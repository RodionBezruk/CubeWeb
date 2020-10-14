<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
define("GROUPPERM_VAL_MODREAD",   "module_read");
define("GROUPPERM_VAL_MODADMIN",  "module_admin");
define("GROUPPERM_VAL_BLOCKREAD", "block_read");
class XoopsGroupPerm extends XoopsObject
{
    function XoopsGroupPerm()
    {
        $this->XoopsObject();
        $this->initVar('gperm_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gperm_groupid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gperm_itemid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gperm_modid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('gperm_name', XOBJ_DTYPE_OTHER, null, false);
    }
    function cleanVars()
    {
    	if (!parent::cleanVars()) {
    		return false;
    	}
    	$gHandler =& xoops_gethandler('group');
    	$group =& $gHandler->get($this->get('gperm_groupid'));
    	if (!is_object($group)) {
    		return false;
    	}
    	$mHandler =& xoops_gethandler('module');
    	if ($this->get('gperm_modid') != 1) {
			$module =& $mHandler->get($this->get('gperm_modid'));
			if (!is_object($module)) {
				return false;
			}
    	}
    	if ($this->get('gperm_name') == GROUPPERM_VAL_MODREAD
    	    || $this->get('gperm_name') == GROUPPERM_VAL_MODADMIN)
    	{
    		$mHandler =& xoops_gethandler('module');
    		$module =& $mHandler->get($this->get('gperm_itemid'));
    		if (!is_object($module)) {
    			return false;
	    	}
    	}
    	else if ($this->get('gperm_name') == GROUPPERM_VAL_BLOCKREAD) {
    		$bHandler =& xoops_gethandler('block');
    		$block =& $bHandler->get($this->get('gperm_itemid'));
    		if (!is_object($block)) {
    			return false;
	    	}
    	}
    	return true;
    }
}
class XoopsGroupPermHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $perm =& new XoopsGroupPerm();
        if ($isNew) {
            $perm->setNew();
        }
        return $perm;
    }
    function &get($id)
    {
        $ret = false;
        if (intval($id) > 0) {
            $sql = sprintf("SELECT * FROM %s WHERE gperm_id = %u", $this->db->prefix('group_permission'), $id);
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ( $numrows == 1 ) {
                        $perm =& new XoopsGroupPerm();
                    $perm->assignVars($this->db->fetchArray($result));
                        $ret =& $perm;
                }
            }
        }
        return $ret;
    }
    function insert(&$perm)
    {
        if ( strtolower(get_class($perm)) != 'xoopsgroupperm' ) {
            return false;
        }
        if ( !$perm->isDirty() ) {
            return true;
        }
        if (!$perm->cleanVars()) {
            return false;
        }
        foreach ($perm->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($perm->isNew()) {
            $gperm_id = $this->db->genId('group_permission_gperm_id_seq');
            $sql = sprintf("INSERT INTO %s (gperm_id, gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (%u, %u, %u, %u, %s)", $this->db->prefix('group_permission'), $gperm_id, $gperm_groupid, $gperm_itemid, $gperm_modid, $this->db->quoteString($gperm_name));
        } else {
            $sql = sprintf("UPDATE %s SET gperm_groupid = %u, gperm_itemid = %u, gperm_modid = %u WHERE gperm_id = %u", $this->db->prefix('group_permission'), $gperm_groupid, $gperm_itemid, $gperm_modid, $gperm_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($gperm_id)) {
            $gperm_id = $this->db->getInsertId();
        }
        $perm->assignVar('gperm_id', $gperm_id);
        return true;
    }
    function delete(&$perm)
    {
        if (strtolower(get_class($perm)) != 'xoopsgroupperm') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE gperm_id = %u", $this->db->prefix('group_permission'), $perm->getVar('gperm_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('group_permission');
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
            $perm =& new XoopsGroupPerm();
            $perm->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $perm;
            } else {
                $ret[$myrow['gperm_id']] =& $perm;
            }
            unset($perm);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('group_permission');
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
        $sql = sprintf("DELETE FROM %s", $this->db->prefix('group_permission'));		if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function deleteByGroup($gperm_groupid, $gperm_modid = null)
    {
        $criteria = new CriteriaCompo(new Criteria('gperm_groupid', intval($gperm_groupid)));
		if (isset($gperm_modid)) {
            $criteria->add(new Criteria('gperm_modid', intval($gperm_modid)));
        }
        return $this->deleteAll($criteria);
    }
    function deleteByModule($gperm_modid, $gperm_name = null, $gperm_itemid = null)
    {
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', intval($gperm_modid)));
		if (isset($gperm_name)) {
			$criteria->add(new Criteria('gperm_name', $gperm_name));
			if (isset($gperm_itemid)) {
				$criteria->add(new Criteria('gperm_itemid', intval($gperm_itemid)));
			}
		}
        return $this->deleteAll($criteria);
    }
	function deleteBasicPermission($gperm_groupid)
	{
		$criteria = new CriteriaCompo(new Criteria('gperm_groupid', $gperm_groupid));
		$criteria->add(new Criteria('gperm_modid', 1));
		$criteria2 = new CriteriaCompo(new Criteria('gperm_name', 'system_admin'));
		$criteria2->add(new Criteria('gperm_name', 'module_admin'), 'OR');
		$criteria2->add(new Criteria('gperm_name', 'module_read'), 'OR');
		$criteria2->add(new Criteria('gperm_name', 'block_read'), 'OR');
		$criteria->add($criteria2);
		$this->deleteAll($criteria);
	}
    function checkRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1, $bypass_admincheck = false)
    {
        if (($bypass_admincheck == false) &&
            ((is_array($gperm_groupid) && in_array(XOOPS_GROUP_ADMIN, $gperm_groupid))||
            (XOOPS_GROUP_ADMIN == $gperm_groupid))) {
            return true;
        }
        $criteria =& $this->getCriteria($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid);
        if ($this->getCount($criteria) > 0) {
            return true;
        }
        return false;
    }
    function addRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $criteria =& $this->getCriteria($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid);
        $count = $this->getCount($criteria);
        if ($count == 1) {
            return true;    
        } else if ($count > 1) {
            $this->removeRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid);
        }
        $perm =& $this->create();
        $perm->setVar('gperm_name', $gperm_name);
        $perm->setVar('gperm_groupid', $gperm_groupid);
        $perm->setVar('gperm_itemid', $gperm_itemid);
        $perm->setVar('gperm_modid', $gperm_modid);
        return $this->insert($perm);
    }
    function removeRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
		$criteria =& $this->getCriteria($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid);
		return $this->deleteAll($criteria);
    }
	function getItemIds($gperm_name, $gperm_groupid, $gperm_modid = 1)
	{
		$ret = array();
		$criteria =& $this->getCriteria($gperm_name, 0, $gperm_groupid, $gperm_modid);
		$perms =& $this->getObjects($criteria, true);
		foreach (array_keys($perms) as $i) {
			$ret[] = $perms[$i]->getVar('gperm_itemid');
		}
		return array_unique($ret);
	}
	function getGroupIds($gperm_name, $gperm_itemid, $gperm_modid = 1)
	{
		$ret = array();
		$criteria =& $this->getCriteria($gperm_name, $gperm_itemid, array(), $gperm_modid);
		$perms =& $this->getObjects($criteria, true);
		foreach (array_keys($perms) as $i) {
			$ret[] = $perms[$i]->getVar('gperm_groupid');
		}
		return $ret;
	}
    function &getCriteria($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', intval($gperm_modid)));
        $criteria->add(new Criteria('gperm_name', $gperm_name));
        $gperm_itemid = intval($gperm_itemid);
        if ($gperm_itemid > 0) {
            $criteria->add(new Criteria('gperm_itemid', $gperm_itemid));
        }
        if (is_array($gperm_groupid)) {
            if (count($gperm_groupid) > 0) {
                $criteria2 = new CriteriaCompo();
                foreach ($gperm_groupid as $gid) {
                    $criteria2->add(new Criteria('gperm_groupid', intval($gid)), 'OR');
                }
                $criteria->add($criteria2);
            }
        } else {
            $criteria->add(new Criteria('gperm_groupid', intval($gperm_groupid)));
        }
        return $criteria;
    }
}
?>
