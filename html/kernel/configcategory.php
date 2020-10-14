<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsConfigCategory extends XoopsObject
{
    function XoopsConfigCategory()
    {
        $this->XoopsObject();
        $this->initVar('confcat_id', XOBJ_DTYPE_INT, null);
        $this->initVar('confcat_name', XOBJ_DTYPE_OTHER, null);
        $this->initVar('confcat_order', XOBJ_DTYPE_INT, 0);
    }
    function getName()
    {
		return defined($this->get('confcat_name')) ? constant($this->get('confcat_name')) : $this->get('confcat_name');
	}
}
class XoopsConfigCategoryHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $confcat =& new XoopsConfigCategory();
        if ($isNew) {
            $confcat->setNew();
        }
        return $confcat;
    }
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('configcategory').' WHERE confcat_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $confcat =& new XoopsConfigCategory();
                    $confcat->assignVars($this->db->fetchArray($result), false);
                        $ret =& $confcat;
                }
            }
        }
        return $ret;
    }
    function insert(&$confcat)
    {
        if (strtolower(get_class($confcat)) != 'xoopsconfigcategory') {
            return false;
        }
        if (!$confcat->isDirty()) {
            return true;
        }
        if (!$confcat->cleanVars()) {
            return false;
        }
        foreach ($confcat->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($confcat->isNew()) {
            $confcat_id = $this->db->genId('configcategory_confcat_id_seq');
            $sql = sprintf("INSERT INTO %s (confcat_id, confcat_name, confcat_order) VALUES (%u, %s, %u)", $this->db->prefix('configcategory'), $confcat_id, $this->db->quoteString($confcat_name), $confcat_order);
        } else {
            $sql = sprintf("UPDATE %s SET confcat_name = %s, confcat_order = %u WHERE confcat_id = %u", $this->db->prefix('configcategory'), $this->db->quoteString($confcat_name), $confcat_order, $confcat_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($confcat_id)) {
            $confcat_id = $this->db->getInsertId();
        }
        $confcat->assignVar('confcat_id', $confcat_id);
        return $confcat_id;
    }
    function delete(&$confcat)
    {
        if (strtolower(get_class($confcat)) != 'xoopsconfigcategory') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE confcat_id = %u", $this->db->prefix('configcategory'), $confcat->getVar('confcat_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('configcategory');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $sort = !in_array($criteria->getSort(), array('confcat_id', 'confcat_name', 'confcat_order')) ? 'confcat_order' : $criteria->getSort();
            $sql .= ' ORDER BY '.$sort.' '.$criteria->getOrder();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $confcat =& new XoopsConfigCategory();
            $confcat->assignVars($myrow, false);
            if (!$id_as_key) {
                $ret[] =& $confcat;
            } else {
                $ret[$myrow['confcat_id']] =& $confcat;
            }
            unset($confcat);
        }
        return $ret;
    }
}
?>
