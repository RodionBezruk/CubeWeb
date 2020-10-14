<?php
class XCube_PropertyInterface
{
	function XCube_PropertyInterface($name)
	{
	}
	function set($value)
	{
	}
	function get()
	{
	}
	function setValue($arg0 = null, $arg1 = null)
	{
		$this->set($arg0, $arg1);
	}
	function getValue($arg0 = null)
	{
		return $this->get($arg0);
	}
	function isArray()
	{
	}
	function isNull()
	{
	}
	function toNumber()
	{
	}
	function toString()
	{
	}
	function toHTML()
	{
	}
	function hasFetchControl()
	{
	}
	function fetch(&$form)
	{
	}
}
class XCube_AbstractProperty extends XCube_PropertyInterface
{
	var $mName = null;
	var $mValue = null;
	function XCube_AbstractProperty($name)
	{
		parent::XCube_PropertyInterface($name);
		$this->mName = $name;
	}
	function set($value)
	{
		$this->mValue = $value;
	}
	function get($index = null)
	{
		return $this->mValue;
	}
	function isArray()
	{
		return false;
	}
	function isNull()
	{
		return (strlen(trim($this->mValue)) == 0);
	}
	function toNumber()
	{
		return $this->mValue;
	}
	function toString()
	{
		return $this->mValue;
	}
	function toHTML()
	{
		return htmlspecialchars($this->toString(), ENT_QUOTES);
	}
	function hasFetchControl()
	{
		return false;
	}
}
class XCube_GenericArrayProperty extends XCube_PropertyInterface
{
	var $mName = null;
	var $mProperties = array();
	var $mPropertyClassName = null;
	function XCube_GenericArrayProperty($classname, $name)
	{
		$this->mPropertyClassName = $classname;
		$this->mName = $name;
	}
	function set($arg1, $arg2 = null)
	{
		if (is_array($arg1) && $arg2 == null) {
			$this->reset();
			foreach ($arg1 as $t_key => $t_value) {
				$this->_set($t_key, $t_value);
			}
		}
		elseif ($arg1 !== null && $arg2 !== null) {
			$this->_set($arg1, $arg2);
		}
	}
	function add($arg1, $arg2 = null)
	{
		if (is_array($arg1) && $arg2 == null) {
			foreach ($arg1 as $t_key => $t_value) {
				$this->_set($t_key, $t_value);
			}
		}
		elseif ($arg1 !== null && $arg2 !== null) {
			$this->_set($arg1, $arg2);
		}
	}
	function _set($index, $value)
	{
		if (!isset($this->mProperties[$index])) {
			$this->mProperties[$index] =& new $this->mPropertyClassName($this->mName);
		}
		$this->mProperties[$index]->set($value);
	}
	function get($index = null)
	{
		if ($index === null) {
			$ret = array();
			foreach ($this->mProperties as $t_key => $t_value) {
				$ret[$t_key] = $t_value->get();
			}
			return $ret;
		}
		return isset($this->mProperties[$index]) ? $this->mProperties[$index]->get() : null;
	}
	function reset()
	{
		unset($this->mProperties);
		$this->mProperties = array();
	}
	function isArray()
	{
		return true;
	}
	function isNull()
	{
		return (count($this->mProperties) == 0);
	}
	function toNumber()
	{
		return null;
	}
	function toString()
	{
		return 'Array';
	}
	function toHTML()
	{
		return htmlspecialchars($this->toString(), ENT_QUOTES);
	}
	function hasFetchControl()
	{
		return false;
	}
}
class XCube_AbstractArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_AbstractArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty($this->mPropertyClassName, $name);
	}
}
class XCube_BoolProperty extends XCube_AbstractProperty
{
	function set($value)
	{
		if (strlen(trim($value)) > 0) {
			$this->mValue = (intval($value) > 0) ? 1 : 0;
		}
		else {
			$this->mValue = 0;
		}
	}
}
class XCube_BoolArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_BoolArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_BoolProperty", $name);
	}
}
class XCube_IntProperty extends XCube_AbstractProperty
{
	function set($value)
	{
		if (strlen(trim($value)) > 0) {
			$this->mValue = intval($value);
		}
		else {
			$this->mValue = null;
		}
	}
}
class XCube_IntArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_IntArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_IntProperty", $name);
	}
}
class XCube_FloatProperty extends XCube_AbstractProperty
{
	function set($value)
	{
		if (strlen(trim($value)) > 0) {
			$this->mValue = floatval($value);
		}
		else {
			$this->mValue = null;
		}
	}
}
class XCube_FloatArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_FloatArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_FloatProperty", $name);
	}
}
class XCube_StringProperty extends XCube_AbstractProperty
{
	function set($value)
	{
		$this->mValue = preg_replace("/[\\x00-\\x1f]/", '' , $value);
	}
	function toNumber()
	{
		return intval($this->mValue);
	}
}
class XCube_StringArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_StringArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_StringProperty", $name);
	}
}
class XCube_TextProperty extends XCube_AbstractProperty
{
	function set($value)
	{
		$matches = array();
		$this->mValue = preg_replace("/[\\x00-\\x08]|[\\x0b-\\x0c]|[\\x0e-\\x1f]/", '', $value);
	}
	function toNumber()
	{
		return intval($this->mValue);
	}
}
class XCube_TextArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_TextArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_TextProperty", $name);
	}
}
class XCube_FileProperty extends XCube_AbstractProperty
{
	var $mIndex = null;
	function XCube_FileProperty($name)
	{
		parent::XCube_AbstractProperty($name);
		$this->mValue =& new XCube_FormFile($name);
	}
	function hasFetchControl()
	{
		return true;
	}
	function fetch(&$form)
	{
		if (!is_object($this->mValue)) {
			return false;
		}
		if ($this->mIndex !== null) {
			$this->mValue->mKey = $this->mIndex;
		}
		$this->mValue->fetch();
		if (!$this->mValue->hasUploadFile()) {
			$this->mValue = null;
		}
	}
	function isNull()
	{
		if (!is_object($this->mValue)) {
			return true;
		}
		return !$this->mValue->hasUploadFile();
	}
	function toString()
	{
		return null;
	}
	function toNumber()
	{
		return null;
	}
}
class XCube_FileArrayProperty extends XCube_GenericArrayProperty
{
	function XCube_FileArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_FileProperty", $name);
	}
	function hasFetchControl()
	{
		return true;
	}
	function fetch(&$form)
	{
		unset($this->mProperties);
		$this->mProperties = array();
		if (isset($_FILES[$this->mName]) && is_array($_FILES[$this->mName]['name'])) {
			foreach ($_FILES[$this->mName]['name'] as $_key => $_val) {
				$this->mProperties[$_key] =& new $this->mPropertyClassName($this->mName);
				$this->mProperties[$_key]->mIndex = $_key;
				$this->mProperties[$_key]->fetch($form);
			}
		}
	}
}
class XCube_ImageFileProperty extends XCube_FileProperty
{
	function XCube_ImageFileProperty($name)
	{
		parent::XCube_AbstractProperty($name);
		$this->mValue =& new XCube_FormImageFile($name);
	}
}
class XCube_ImageFileArrayProperty extends XCube_FileArrayProperty
{
	function XCube_ImageFileArrayProperty($name)
	{
		parent::XCube_GenericArrayProperty("XCube_ImageFileProperty", $name);
	}
}
?>
