<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsSimpleObject extends AbstractXoopsObject
{
	var $mVars = array();
	var $mIsNew = true;
	var $_mAllowType = array(XOBJ_DTYPE_BOOL, XOBJ_DTYPE_INT, XOBJ_DTYPE_FLOAT, XOBJ_DTYPE_STRING, XOBJ_DTYPE_TEXT);
	function XoopsSimpleObject()
	{
	}
	function setNew()
	{
		$this->mIsNew = true;
	}
	function unsetNew()
	{
		$this->mIsNew = false;
	}
	function isNew()
	{
		return $this->mIsNew;
	}
	function initVar($key, $dataType, $value = null, $required = false, $size = null)
	{
		if (!in_array($dataType, $this->_mAllowType)) {
			die();	
		}
		$this->mVars[$key] = array(
			'data_type' => $dataType,
			'value' => null,
			'required' => $required ? true : false,
			'maxlength' => $size ? intval($size) : null
		);
		$this->assignVar($key, $value);
	}
	function assignVar($key, $value)
	{
		if (!isset($this->mVars[$key])) {
			return;
		}
		switch ($this->mVars[$key]['data_type']) {
			case XOBJ_DTYPE_BOOL:
				$this->mVars[$key]['value'] = $value ? 1 : 0;
				break;
			case XOBJ_DTYPE_INT:
				$this->mVars[$key]['value'] = $value !== null ? intval($value) : null;
				break;
			case XOBJ_DTYPE_FLOAT:
				$this->mVars[$key]['value'] = $value !== null ? floatval($value) : null;
				break;
			case XOBJ_DTYPE_STRING:
				if ($this->mVars[$key]['maxlength'] !== null && strlen($value) > $this->mVars[$key]['maxlength']) {
					$this->mVars[$key]['value'] = xoops_substr($value, 0, $this->mVars[$key]['maxlength'], null);
				}
				else {
					$this->mVars[$key]['value'] = $value;
				}
				break;
			case XOBJ_DTYPE_TEXT:
				$this->mVars[$key]['value'] = $value;
				break;
		}
	}
	function assignVars($values)
	{
		foreach ($values as $key => $value) {
			$this->assignVar($key, $value);
		}
	}
	function set($key, $value)
	{
		$this->assignVar($key, $value);
	}
	function get($key)
	{
		return $this->mVars[$key]['value'];
	}
	function gets()
	{
		$ret = array();
		foreach ($this->mVars as $key => $value) {
			$ret[$key] = $value['value'];
		}
		return $ret;
	}
	function setVar($key, $value)
	{
		$this->assignVar($key, $value);
	}
	function setVars($values)
	{
		$this->assignVars($values);
	}
	function getVar($key)
	{
		return $this->getShow($key);
	}
	function getShow($key)
	{
		$value = null;
		switch ($this->mVars[$key]['data_type']) {
			case XOBJ_DTYPE_BOOL:
			case XOBJ_DTYPE_INT:
			case XOBJ_DTYPE_FLOAT:
				$value = $this->mVars[$key]['value'];
				break;
			case XOBJ_DTYPE_STRING:
				$root =& XCube_Root::getSingleton();
				$textFilter =& $root->getTextFilter();
				$value = $textFilter->toShow($this->mVars[$key]['value']);
				break;
			case XOBJ_DTYPE_TEXT:
				$root =& XCube_Root::getSingleton();
				$textFilter =& $root->getTextFilter();
				$value = $textFilter->toShowTarea($this->mVars[$key]['value'], 0, 1, 1, 1, 1);
				break;
		}
		return $value;
	}
	function getTypeInformations()
	{
		$ret = array();
		foreach (array_keys($this->mVars) as $key) {
			$ret[$key] = $this->mVars[$key]['data_type'];
		}
		return $ret;
	}
}
?>
