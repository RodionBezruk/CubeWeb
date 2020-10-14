<?php
class XoopsObjectTree {
	var $_parentId;
	var $_myId;
	var $_rootId = null;
	var $_tree = array();
	var $_objects;
	function XoopsObjectTree(&$objectArr, $myId, $parentId, $rootId = null)
	{
		$this->_objects =& $objectArr;
		$this->_myId = $myId;
		$this->_parentId = $parentId;
		if (isset($rootId)) {
			$this->_rootId = $rootId;
		}
		$this->_initialize();
	}
	function _initialize()
	{
		foreach (array_keys($this->_objects) as $i) {
            $key1 = $this->_objects[$i]->getVar($this->_myId);
            $this->_tree[$key1]['obj'] =& $this->_objects[$i];
            $key2 = $this->_objects[$i]->getVar($this->_parentId);
            $this->_tree[$key1]['parent'] = $key2;
            $this->_tree[$key2]['child'][] = $key1;
			if (isset($this->_rootId)) {
            	$this->_tree[$key1]['root'] = $this->_objects[$i]->getVar($this->_rootId);
			}
        }
	}
	function &getTree()
	{
		return $this->_tree;
	}
	function &getByKey($key)
	{
		return $this->_tree[$key]['obj'];
	}
	function &getFirstChild($key)
	{
		$ret = array();
		if (isset($this->_tree[$key]['child'])) {
			foreach ($this->_tree[$key]['child'] as $childkey) {
				$ret[$childkey] =& $this->_tree[$childkey]['obj'];
			}
		}
		return $ret;
	}
	function &getAllChild($key, $ret = array())
	{
		if (isset($this->_tree[$key]['child'])) {
			foreach ($this->_tree[$key]['child'] as $childkey) {
				$ret[$childkey] =& $this->_tree[$childkey]['obj'];
				$children =& $this->getAllChild($childkey, $ret);
				foreach (array_keys($children) as $newkey) {
					$ret[$newkey] =& $children[$newkey];
				}
			}
		}
		return $ret;
	}
	function &getAllParent($key, $ret = array(), $uplevel = 1)
	{
		if (isset($this->_tree[$key]['parent']) && isset($this->_tree[$this->_tree[$key]['parent']]['obj'])) {
			$ret[$uplevel] =& $this->_tree[$this->_tree[$key]['parent']]['obj'];
			$parents =& $this->getAllParent($this->_tree[$key]['parent'], $ret, $uplevel+1);
			foreach (array_keys($parents) as $newkey) {
				$ret[$newkey] =& $parents[$newkey];
			}
		}
		return $ret;
	}
	function _makeSelBoxOptions($fieldName, $selected, $key, &$ret, $prefix_orig, $prefix_curr = '')
	{
        if ($key > 0) {
            $value = $this->_tree[$key]['obj']->getVar($this->_myId);
            $ret .= '<option value="'.$value.'"';
			if ($value == $selected) {
				$ret .= ' selected="selected"';
			}
			$ret .= '>'.$prefix_curr.$this->_tree[$key]['obj']->getVar($fieldName).'</option>';
            $prefix_curr .= $prefix_orig;
        }
        if (isset($this->_tree[$key]['child']) && !empty($this->_tree[$key]['child'])) {
            foreach ($this->_tree[$key]['child'] as $childkey) {
                $this->_makeSelBoxOptions($fieldName, $selected, $childkey, $ret, $prefix_orig, $prefix_curr);
            }
        }
	}
	function &makeSelBox($name, $fieldName, $prefix='-', $selected='', $addEmptyOption = false, $key=0)
    {
        $ret = '<select name="'.$name.'" id="'.$name.'">';
        if (false != $addEmptyOption) {
            $ret .= '<option value="0"></option>';
        }
        $this->_makeSelBoxOptions($fieldName, $selected, $key, $ret, $prefix);
        $ret .= "</select>";
        return $ret;
    }
}
?>
