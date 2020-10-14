<?php
define("XCUBE_RENDER_MODE_NORMAL",1);
define("XCUBE_RENDER_MODE_DIALOG",2);
define("XCUBE_RENDER_TARGET_TYPE_BUFFER", null);
define("XCUBE_RENDER_TARGET_TYPE_THEME", 'theme');
define("XCUBE_RENDER_TARGET_TYPE_BLOCK", 'block');
define("XCUBE_RENDER_TARGET_TYPE_MAIN", 'main');
class XCube_RenderTarget
{
	var $mName = null;
	var $mRenderBuffer = null;
	var $mModuleName = null;
	var $mTemplateName = null;
	var $mAttributes = array();
	var $mType = XCUBE_RENDER_TARGET_TYPE_BUFFER;
	var $mCacheTime = null;
	function XCube_RenderTarget()
	{
	}
	function setName($name)
	{
		$this->mName = $name;
	}
	function getName()
	{
		return $this->mName;
	}
	function setTemplateName($name)
	{
		$this->mTemplateName = $name;
	}
	function getTemplateName()
	{
		return $this->mTemplateName;
	}
	function setAttribute($key,$value)
	{
		$this->mAttributes[$key] = $value;
	}
	function setAttributes($attr)
	{
		$this->mAttributes = $attr;
	}
	function getAttribute($key)
	{
		return isset($this->mAttributes[$key]) ? $this->mAttributes[$key] : null;
	}
	function getAttributes()
	{
		return $this->mAttributes;
	}
	function setType($type)
	{
		$this->mType = $type;
		$this->setAttribute('legacy_buffertype', $type);
	}
	function getType()
	{
		return $this->getAttribute('legacy_buffertype', $type);
	}
	function setResult(&$result)
	{
		$this->mRenderBuffer = $result;
	}
	function getResult()
	{
		return $this->mRenderBuffer;
	}
	function reset()
	{
		$this->setTemplateName(null);
		unset($this->mAttributes);
		$this->mAttributes = array();
		$this->mRenderBuffer = null;
	}
}
class XCube_RenderSystem
{
	var $mController;
	var $mRenderMode = XCUBE_RENDER_MODE_NORMAL;
	function XCube_RenderSystem()
	{
	}
	function prepare(&$controller)
	{
		$this->mController =& $controller;
	}
	function &createRenderTarget()
	{
		$renderTarget =& new XCube_RenderTarget();
		return $renderTarget;
	}
	function render(&$target)
	{
	}
}
?>
