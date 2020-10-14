<?php
class XCube_RenderCache
{
	var $mCacheId = null;
	var $mResourceName = null;
	function XCube_RenderCache()
	{
	}
	function isCache($cachetime = null)
	{
	}
	function enableCache()
	{
		return true;
	}
	function setResourceName($name)
	{
		$this->mResourceName = $name;
	}
	function getCacheId()
	{
	}
	function _getFileName()
	{
	}
	function save($renderTarget)
	{
		if ($this->enableCache()) {
			$filename = $this->_getFileName();
			$fp = fopen($filename, "wb");
			fwrite($fp, $renderTarget->getResult());
			fclose($fp);
		}
	}
	function load()
	{
		if ($this->isCache()) {
			return file_get_contents($this->_getFileName());
		}
	}
	function clear()
	{
	}
	function reset()
	{
		$this->mResourceName = null;
	}
}
?>
