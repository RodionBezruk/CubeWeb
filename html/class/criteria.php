<?php
define('XOOPS_CRITERIA_ASC', 'ASC');
define('XOOPS_CRITERIA_DESC', 'DESC');
define('XOOPS_CRITERIA_STARTWITH', 1);
define('XOOPS_CRITERIA_ENDWITH', 2);
define('XOOPS_CRITERIA_CONTAIN', 3);
class CriteriaElement
{
    var $order = array();
    var $sort = array();
    var $limit = 0;
    var $start = 0;
    var $groupby = '';
    function CriteriaElement()
    {
    }
    function render()
    {
    }
    function hasChildElements()
    {
		return false;
	}
    function getCountChildElements()
    {
		return 0;
	}
	function getChildElement($idx)
	{
		return null;
	}
	function getCondition($idx)
	{
		return null;
	}
	function getName()
	{
		return null;
	}
	function getValue()
	{
		return null;
	}
	function getOperator()
	{
		return null;
	}
    function setSort($sort, $order = null)
    {
        $this->sort[0] = $sort;
		if (!isset($this->order[0])) {
			$this->order[0] = 'ASC';
		}
		if ($order != null) {
			if (strtoupper($order) == 'ASC') {
				$this->order[0] = 'ASC';
			}
			elseif (strtoupper($order) == 'DESC') {
				$this->order[0] = 'DESC';
			}
		}
    }
	function addSort($sort, $order = 'ASC')
	{
        $this->sort[] = $sort;
		if (strtoupper($order) == 'ASC') {
			$this->order[] = 'ASC';
		}
		elseif (strtoupper($order) == 'DESC') {
			$this->order[] = 'DESC';
		}
	}
    function getSort()
    {
		if (isset($this->sort[0])) {
			return $this->sort[0];
		}
		else {
			return '';
		}
    }
	function getSorts()
	{
		$ret = array();
		$max = count($this->sort);
		for ($i = 0; $i < $max; $i++) {
			$ret[$i]['sort'] = $this->sort[$i];
			if (isset($this->order[$i])) {
				$ret[$i]['order'] = $this->order[$i];
			}
			else {
				$ret[$i]['order'] = 'ASC';
			}
		}
		return $ret;
	}
    function setOrder($order)
    {
        if (strtoupper($order) == 'ASC') {
            $this->order[0] = 'ASC';
        }
        elseif (strtoupper($order) == 'DESC') {
            $this->order[0] = 'DESC';
        }
    }
    function getOrder()
    {
		if (isset($this->order[0])) {
			return $this->order[0];
		}
		else {
			return 'ASC';
		}
    }
    function setLimit($limit=0)
    {
        $this->limit = intval($limit);
    }
    function getLimit()
    {
        return $this->limit;
    }
    function setStart($start=0)
    {
        $this->start = intval($start);
    }
    function getStart()
    {
        return $this->start;
    }
    function setGroupby($group){
        $this->groupby = $group;
    }
    function getGroupby(){
        return ' GROUP BY '.$this->groupby;
    }
}
class CriteriaCompo extends CriteriaElement
{
    var $criteriaElements = array();
    var $conditions = array();
    function CriteriaCompo($ele=null, $condition='AND')
    {
        if (isset($ele) && is_object($ele)) {
            $this->add($ele, $condition);
        }
    }
	function hasChildElements()
	{
		return count($this->criteriaElements) > 0;
	}
    function getCountChildElements()
    {
		return count($this->criteriaElements);
	}
	function getChildElement($idx)
	{
		return $this->criteriaElements[$idx];
	}
	function getCondition($idx)
	{
		return $this->conditions[$idx];
	}
    function &add(&$criteriaElement, $condition='AND')
    {
        $this->criteriaElements[] =& $criteriaElement;
        $this->conditions[] = $condition;
        return $this;
    }
    function render()
    {
        $ret = '';
        $count = count($this->criteriaElements);
        if ($count > 0) {
            $ret = '('. $this->criteriaElements[0]->render();
            for ($i = 1; $i < $count; $i++) {
                $ret .= ' '.$this->conditions[$i].' '.$this->criteriaElements[$i]->render();
            }
            $ret .= ')';
        }
        return $ret;
    }
    function renderWhere()
    {
        $ret = $this->render();
        $ret = ($ret != '') ? 'WHERE ' . $ret : $ret;
        return $ret;
    }
    function renderLdap(){
        $retval = '';
        $count = count($this->criteriaElements);
        if ($count > 0) {
            $retval = $this->criteriaElements[0]->renderLdap();
            for ($i = 1; $i < $count; $i++) {
                $cond = $this->conditions[$i];
                if(strtoupper($cond) == 'AND'){
                    $op = '&';
                } elseif (strtoupper($cond)=='OR'){
                    $op = '|';
                }
                $retval = "($op$retval" . $this->criteriaElements[$i]->renderLdap().")";
            }
        }
        return $retval;
    }
}
class Criteria extends CriteriaElement
{
    var $prefix;
    var $function;
    var $column;
    var $operator;
    var $value;
	var $dtype = 0;
    function Criteria($column, $value='', $operator='=', $prefix = '', $function = '') {
        $this->prefix = $prefix;
        $this->function = $function;
        $this->column = $column;
        $this->operator = $operator;
		if (is_array($value) && count($value)==2)
		{
			$this->dtype = intval($value[0]);
			$this->value = $value[1];
		}
		else
		{
			$this->value = $value;
		}
    }
    function getName()
    {
		return $this->column;
	}
	function getValue()
	{
		return $this->value;
	}
	function getOperator()
	{
		return $this->operator;
	}
    function render() {
        $value = $this->value;
        if (!in_array(strtoupper($this->operator), array('IN', 'NOT IN'))) {
            $value = "'".$value."'";
        }
        $clause = (!empty($this->prefix) ? "{$this->prefix}." : "") . $this->column;
        if ( !empty($this->function) ) {
            $clause = sprintf($this->function, $clause);
        }
        $clause .= " {$this->operator} $value";
        return $clause;
    }
    function renderLdap(){
        $clause = "(" . $this->column . $this->operator . $this->value . ")";
        return $clause;
    }
    function renderWhere() {
        $cond = $this->render();
        return empty($cond) ? '' : "WHERE $cond";
    }
}
?>
