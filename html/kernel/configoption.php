<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsConfigOption extends XoopsObject
{
    function XoopsConfigOption()
    {
        $this->XoopsObject();
        $this->initVar('confop_id', XOBJ_DTYPE_INT, null);
        $this->initVar('confop_name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('confop_value', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('conf_id', XOBJ_DTYPE_INT, 0);
    }
    function getOptionKey()
    {
		return defined($this->get('confop_value')) ? constant($this->get('confop_value')) : $this->get('confop_value');
	}
	function getOptionLabel()
	{
		return defined($this->get('confop_name')) ? constant($this->get('confop_name')) : $this->get('confop_name');
	}
	function isEqual(&$option)
	{
		$flag = true;
		$flag &= ($this->get('confop_name') == $option->get('confop_name'));
		$flag &= ($this->get('confop_value') == $option->get('confop_value'));
		return $flag;
	}
}
class XoopsConfigOptionHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $confoption =& new XoopsConfigOption();
        if ($isNew) {
            $confoption->setNew();
        }
        return $confoption;
    }
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('configoption').' WHERE confop_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $confoption =& new XoopsConfigOption();
                    $confoption->assignVars($this->db->fetchArray($result));
                        $ret =& $confoption;
                }
            }
        }
        return $ret;
    }
    function insert(&$confoption)
    {
        if (strtolower(get_class($confoption)) != 'xoopsconfigoption') {
            return false;
        }
        if (!$confoption->isDirty()) {
            return true;
        }
        if (!$confoption->cleanVars()) {
            return false;
        }
        foreach ($confoption->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($confoption->isNew()) {
            $confop_id = $this->db->genId('configoption_confop_id_seq');
            $sql = sprintf("INSERT INTO %s (confop_id, confop_name, confop_value, conf_id) VALUES (%u, %s, %s, %u)", $this->db->prefix('configoption'), $confop_id, $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $conf_id);
        } else {
            $sql = sprintf("UPDATE %s SET confop_name = %s, confop_value = %s WHERE confop_id = %u", $this->db->prefix('configoption'), $this->db->quoteString($confop_name), $this->db->quoteString($confop_value), $confop_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($confop_id)) {
            $confop_id = $this->db->getInsertId();
        }
        $confoption->assignVar('confop_id', $confop_id);
        return $confop_id;
    }
    function delete(&$confoption)
    {
        if (strtolower(get_class($confoption)) != 'xoopsconfigoption') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE confop_id = %u", $this->db->prefix('configoption'), $confoption->getVar('confop_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('configoption');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere().' ORDER BY confop_id '.$criteria->getOrder();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $confoption =& new XoopsConfigOption();
            $confoption->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $confoption;
            } else {
                $ret[$myrow['confop_id']] =& $confoption;
            }
            unset($confoption);
        }
        return $ret;
    }
}
?>
