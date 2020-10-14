<?php
define("LEGACY_EXPRESSION_EQ", "=");
define("LEGACY_EXPRESSION_NE", "<>");
define("LEGACY_EXPRESSION_LT", "<");
define("LEGACY_EXPRESSION_LE", "<=");
define("LEGACY_EXPRESSION_GT", ">");
define("LEGACY_EXPRESSION_GE", ">=");
define("LEGACY_EXPRESSION_LIKE", "like");
define("LEGACY_EXPRESSION_IN", "in");
define("LEGACY_EXPRESSION_AND", "and");
define("LEGACY_EXPRESSION_OR", "or");
class Legacy_Criteria
{
	var $mTypeInfoArr = array();
	var $mChildlen = array();
	function Legacy_Criteria($typeInfoArr)
	{
		$this->mTypeInfoArr = $typeInfoArr;
	}
	function add($column, $value = null, $comparison = LEGACY_EXPRESSION_EQ)
	{
		$this->addAnd($column, $value, $comparison);
	}
	function addAnd($column, $value = null, $comparison = LEGACY_EXPRESSION_EQ)
	{
		$t_arr = array();
		$t_arr['condition'] = LEGACY_EXPRESSION_AND;
		if (is_object($column) && is_a($column, 'Legacy_Criteria')) {
			$t_arr['value'] = $column;
			$this->mChildlen[] = $t_arr;
		}
		elseif (!is_object($column)) {
			if ($this->_checkColumn() && $this->_castingConversion($column, $value)) {
				$t_arr['value'] = $value;
				$t_arr['comparison'] = $comparison;
				$this->mChildlen[] = $t_arr;
			}
		}
	}
	function addOr($column, $value = null, $comparison = LEGACY_EXPRESSION_EQ)
	{
		$t_arr = array();
		$t_arr['condition'] = LEGACY_EXPRESSION_OR;
		if (is_object($column) && is_a($column, 'Legacy_Criteria')) {
			$t_arr['value'] = $column;
			$this->mChildlen[] = $t_arr;
		}
		elseif (!is_object($column)) {
			if ($this->_checkColumn() && $this->_castingConversion($column, $value)) {
				$t_arr['value'] = $value;
				$t_arr['comparison'] = $comparison;
				$this->mChildlen[] = $t_arr;
			}
		}
	}
	function &createCriterion()
	{
		$criteria =& new Legacy_Criteria($this->mTypeInfoArr);
		return $criteria;
	}
	function _checkColumn($column)
	{
		return isset($this->mTypeInfoArr[$column]);
	}
	function _castingConversion($column, &$value)
	{
		if (is_array($value)) {
			foreach ($value as $_key => $_val) {
				if ($this->_castingConversion($column, $_val)) {
					$value[$_key] = $_val;
				}
				else {
					return false;
				}
			}
		}
		if (!is_object($value)) {
			switch ($this->mTypeInfoArr[$column]) {
				case XOBJ_DTYPE_BOOL:
					$value = $value ? 1 : 0;
					break;
				case XOBJ_DTYPE_INT:
					$value = intval($value);
					break;
				case XOOPS_DTYPE_FLOAT:
					$value = floatval($value);
					break;
				case XOOPS_DTYPE_STRING:
				case XOOPS_DTYPE_TEXT:
					break;
				default:
					return false;
			}
		}
		else {
			return false;
		}
		return true;
	}
}
?>
