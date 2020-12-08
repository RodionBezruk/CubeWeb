<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsObjectGenericHandler extends XoopsObjectHandler
{
	var $mTable = null;
	var $mPrimary = null;
	var $mClass = null;
	var $_mDummyObj = null;
	function XoopsObjectGenericHandler(&$db)
	{
		parent::XoopsObjectHandler($db);
		$this->mTable = $this->db->prefix($this->mTable);
	}
	function &create($isNew = true)
	{
		$obj = null;
		if (XC_CLASS_EXISTS($this->mClass)) {
			$obj =& new $this->mClass();
			if($isNew)
				$obj->setNew();
		}
		return $obj;
	}
	function &get($id)
	{
		$ret = null;
		$criteria =& new Criteria($this->mPrimary, $id);
		$objArr =& $this->getObjects($criteria);
		if (count($objArr) == 1) {
			$ret =& $objArr[0];
		}
		return $ret;
	}
	function &getObjects($criteria = null, $limit = null, $start = null, $id_as_key = false)
	{
		$ret = array();
		$sql = "SELECT * FROM `" . $this->mTable . '`';
		if($criteria !== null && is_a($criteria, 'CriteriaElement')) {
			$where = $this->_makeCriteria4sql($criteria);
			if (trim($where)) {
				$sql .= " WHERE " . $where;
			}
			$sorts = array();
			foreach ($criteria->getSorts() as $sort) {
                $sorts[] = '`' . $sort['sort'] . '` ' . $sort['order']; 
			}
			if ($criteria->getSort() != '') {
				$sql .= " ORDER BY " . implode(',', $sorts);
			}
			if ($limit === null) {
				$limit = $criteria->getLimit();
			}
			if ($start === null) {
				$start = $criteria->getStart();
			}
		}
		else {
			if ($limit === null) {
				$limit = 0;
			}
			if ($start === null) {
				$start = 0;
			}
		}
		$result = $this->db->query($sql, $limit, $start);
		if (!$result) {
			return $ret;
		}
		while($row = $this->db->fetchArray($result)) {
			$obj =& new $this->mClass();
			$obj->assignVars($row);
			$obj->unsetNew();
			if ($id_as_key)	{
				$ret[$obj->get($this->mPrimary)] =& $obj;
			}
			else {
				$ret[]=&$obj;
			}
			unset($obj);
		}
		return $ret;
	}
	function getCount($criteria = null)
	{
        $sql="SELECT COUNT(*) c FROM `" . $this->mTable . '`'; 
		if($criteria !== null && is_a($criteria, 'CriteriaElement')) {
			$where = $this->_makeCriteria4sql($criteria);
			if ($where) {
				$sql .= " WHERE " . $where;
			}
		}
		return $this->_getCount($sql);
	}
	function _getCount($sql = null)
	{
		$result=$this->db->query($sql);
		if (!$result) {
			return false;
		}
		$ret = $this->db->fetchArray($result);
		return $ret['c'];
	}
	function insert(&$obj, $force = false)
	{
		if(!is_a($obj, $this->mClass)) {
			return false;
		}
		$new_flag = false;
		if ($obj->isNew()) {
			$new_flag = true;
			$sql = $this->_insert($obj);
		}
		else {
			$sql = $this->_update($obj);
		}
		$result = $force ? $this->db->queryF($sql) : $this->db->query($sql);
		if (!$result){
			return false;
		}
		if ($new_flag) {
			$obj->setVar($this->mPrimary, $this->db->getInsertId());
		}
		return true;
	}
	function _insert(&$obj) {
		$fileds=array();
		$values=array();
		$arr = $this->_makeVars4sql($obj);
		foreach($arr as $_name => $_value) {
			$fields[] = "`${_name}`";
			$values[] = $_value;
		}
		$sql = @sprintf("INSERT INTO `" . $this->mTable . "` ( %s ) VALUES ( %s )", implode(",", $fields), implode(",", $values));
		return $sql;
	}
	function _update(&$obj) {
		$set_lists=array();
		$where = "";
		$arr = $this->_makeVars4sql($obj);
		foreach ($arr as $_name => $_value) {
			if ($_name == $this->mPrimary) {
				$where = "`${_name}`=${_value}";
			}
			else {
				$set_lists[] = "`${_name}`=${_value}";
			}
		}
		$sql = @sprintf("UPDATE `" . $this->mTable . "` SET %s WHERE %s", implode(",",$set_lists), $where);
		return $sql;
	}
	function _makeVars4sql(&$obj)
	{
		$ret = array();
		foreach ($obj->gets() as $key => $value) {
            if ($value === null) {
                $ret[$key] = 'NULL';
            }
            else {
				switch ($obj->mVars[$key]['data_type']) {
					case XOBJ_DTYPE_STRING:
					case XOBJ_DTYPE_TEXT:
						$ret[$key] = $this->db->quoteString($value);
						break;
					default:
						$ret[$key] = $value;
				}
            }
		}
		return $ret;
	}
	function _makeCriteria4sql($criteria)
	{
		if ($this->_mDummyObj == null) {
			$this->_mDummyObj =& $this->create();
		}
		return $this->_makeCriteriaElement4sql($criteria, $this->_mDummyObj);
	}
	function _makeCriteriaElement4sql($criteria, &$obj)
	{
		if (is_a($criteria, "CriteriaElement")) {
			if ($criteria->hasChildElements()) {
				$queryString = "";
				$maxCount = $criteria->getCountChildElements();
	            $queryString = '('. $this->_makeCriteria4sql($criteria->getChildElement(0));
	            for ($i = 1; $i < $maxCount; $i++) {
					$queryString .= " " . $criteria->getCondition($i) . " " . $this->_makeCriteria4sql($criteria->getChildElement($i));
	            }
	            $queryString .= ')';
	            return $queryString;
			}
			else {
				$name = $criteria->getName();
				$value = $criteria->getValue();
				if ($name != null && isset($obj->mVars[$name])) {
					if ($value === null) {
						$criteria->operator = $criteria->getOperator() == '=' ? "IS" : "IS NOT";
						$value = "NULL";
					}
					else {
						switch ($obj->mVars[$name]['data_type']) {
							case XOBJ_DTYPE_BOOL:
								$value = $value ? "1" : "0";
								break;
							case XOBJ_DTYPE_INT:
								$value = intval($value);
								break;
							case XOBJ_DTYPE_FLOAT:
								$value = floatval($value);
								break;
							case XOBJ_DTYPE_STRING:
							case XOBJ_DTYPE_TEXT:
								$value = $this->db->quoteString($value);
								break;
							default:
								$value = $this->db->quoteString($value);
						}
					}
				} else {
				    $value = $this->db->quoteString($value);
				}
				if ($name != null) {
					return $name . " " . $criteria->getOperator() . " " . $value;
				}
				else {
					return null;
				}
			}
		}
	}
	function delete(&$obj, $force = false)
	{
		$criteria =& new Criteria($this->mPrimary, $obj->get($this->mPrimary));
        $sql = "DELETE FROM `" . $this->mTable . "` WHERE " . $this->_makeCriteriaElement4sql($criteria, $obj); 
		return $force ? $this->db->queryF($sql) : $this->db->query($sql);
	}
	function deleteAll($criteria, $force = false)
	{
		$objs =& $this->getObjects($criteria);
		$flag = true;
		foreach ($objs as $obj) {
			$flag &= $this->delete($obj, $force);
		}
		return $flag;
	}
}
?>
