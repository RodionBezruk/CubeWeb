<?php
define("XCUBE_CONTEXT_TYPE_DEFAULT", "web_browser");
define("XCUBE_CONTEXT_TYPE_WEB_SERVICE", "web_service");
class XCube_HttpContext
{
	var $mAttributes = array();
	var $mRequest = null;
	var $mUser = null;
	var $mType = XCUBE_CONTEXT_TYPE_DEFAULT;
	var $mThemeName = null;
	function XCube_HttpContext()
	{
	}
	function setAttribute($key, $value)
	{
		$this->mAttributes[$key] = $value;
	}
	function hasAttribute($key)
	{
		return isset($this->mAttributes[$key]);
	}
	function getAttribute($key)
	{
		return isset($this->mAttributes[$key]) ? $this->mAttributes[$key] : null;
	}
	function setRequest(&$request)
	{
		$this->mRequest =& $request;
	}
	function &getRequest()
	{
		return $this->mRequest;
	}
	function setUser(&$principal)
	{
		$this->mUser =& $principal;
	}
	function &getUser()
	{
		return $this->mUser;
	}
	function setThemeName($theme)
	{
		$this->mThemeName = $theme;
	}
	function getThemeName()
	{
		return $this->mThemeName;
	}
}
class XCube_AbstractRequest
{
	function getRequest($key)
	{
		return null;
	}
}
class XCube_HttpRequest extends XCube_AbstractRequest
{
	function getRequest($key)
	{
		if (!isset($_GET[$key]) && !isset($_POST[$key])) {
			return null;
		}
		$value = isset($_GET[$key]) ? $_GET[$key] : $_POST[$key];
		if (!get_magic_quotes_gpc()) {
			return $value;
		}
		if (is_array($value)) {
			return $this->_getArrayRequest($value);
		}
		return stripslashes($value);
	}
	function _getArrayRequest($arr)
	{
		foreach (array_keys($arr) as $t_key) {
			if (is_array($arr[$t_key])) {
				$arr[$t_key] = $this->_getArrayRequest($arr[$t_key]);
			}
			else {
				$arr[$t_key] = stripslashes($arr[$t_key]);
			}
		}
		return $arr;
	}
}
class XCube_GenericRequest extends XCube_AbstractRequest
{
	var $mAttributes = array();
	function XCube_GenericRequest($arr = null)
	{
		if (is_array($arr)) {
			$this->mAttributes = $arr;
		}
	}
	function getRequest($key)
	{
		if (!isset($this->mAttributes[$key])) {
			return null;
		}
		return $this->mAttributes[$key];
	}
}
?>
