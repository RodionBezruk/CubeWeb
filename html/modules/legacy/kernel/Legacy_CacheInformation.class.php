<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_AbstractCacheInformation
{
	var $mIdentityArr = array();
	var $mGroupArr = array();
	var $_mEnableCache = false;
	var $mAttributes = array();
	function Legacy_AbstractCacheInformation()
	{
	}
	function hasSetEnable()
	{
		return $this->_mEnableCache !== false;
	}
	function setEnableCache($flag)
	{
		$this->_mEnableCache = $flag;
	}
	function isEnableCache()
	{
		return $this->_mEnableCache;
	}
	function reset()
	{
		$this->mIdentityArr = array();
		$this->mGroupArr = array();
		$this->_mEnableCache = null;
	}
	function getCacheFilePath()
	{
	}
}
class Legacy_ModuleCacheInformation extends Legacy_AbstractCacheInformation
{
	var $mModule = null;
	var $mURL = null;
	 var $mGetCacheFilePath = null;
	 function Legacy_ModuleCacheInformation()
	 {
		 parent::Legacy_AbstractCacheInformation();
		 $this->mGetCacheFilePath =& new XCube_Delegate();
		 $this->mGetCacheFilePath->register('Legacy_ModuleCacheInformation.GetCacheFilePath');
	 }
	function setModule(&$module)
	{
		$this->mModule =& $module;
	}
	function reset()
	{
		parent::reset();
		$this->mModule = null;
		$this->mURL = null;
	}
	function getCacheFilePath()
	{
		$filepath = null;
		$this->mGetCacheFilePath->call(new XCube_Ref($filepath), $this);
		if (!$filepath) {
			$id = md5(XOOPS_SALT . $this->mURL . "(" . implode("_", $this->mIdentityArr) . ")" . implode("_", $this->mGroupArr));
			$filepath = XOOPS_CACHE_PATH . "/" . $id . ".cache.html";
		}
		return $filepath;
	}
}
class Legacy_BlockCacheInformation extends Legacy_AbstractCacheInformation
{
	 var $mBlock = null;
	 var $mGetCacheFilePath = null;
	 function Legacy_BlockCacheInformation()
	 {
		 parent::Legacy_AbstractCacheInformation();
		 $this->mGetCacheFilePath =& new XCube_Delegate();
		 $this->mGetCacheFilePath->register('Legacy_BlockCachInformation.getCacheFilePath');
	 }
	 function setBlock(&$blockProcedure)
	 {
		 $this->mBlock =& $blockProcedure->_mBlock;
	 }
	 function reset()
	 {
		 parent::reset();
		 $this->mBlock = null;
	 }
	function getCacheFilePath()
	{
		$filepath = null;
		$this->mGetCacheFilePath->call(new XCube_Ref($filepath), $this);
		if (!$filepath) {
			$id = md5(XOOPS_SALT . $this->mBlock->get('bid') . "(" . implode("_", $this->mIdentityArr) . ")" . implode("_", $this->mGroupArr));
			$filepath = XOOPS_CACHE_PATH . "/" . $id . ".cache.html";
		}
		return $filepath;
	}
}
?>
