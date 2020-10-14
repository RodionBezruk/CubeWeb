<?php
define('XCUBE_PAGENAVI_START', 1);
define('XCUBE_PAGENAVI_PERPAGE', 2);
define('XCUBE_PAGENAVI_SORT', 1);
define('XCUBE_PAGENAVI_PAGE', 4);
define('XCUBE_PAGENAVI_DEFAULT_PERPAGE', 20);
class XCube_PageNavigator
{
	var $mAttributes = array();
	var $mStart = 0;
	var $mTotalItems = 0;
	var $mPerpage = XCUBE_PAGENAVI_DEFAULT_PERPAGE;
	var $mPerpageFreeze = false;
	var $mSort = array();
	var $mUrl = "";
	var $mPrefix = null;
	var $mExtra = array();
	var $mFlags = 0;
	var $mFetch = null;
	var $_mIsSpecifedTotalItems = false;
	var $mGetTotalItems = null;
	function XCube_PageNavigator($url, $flags = XCUBE_PAGENAVI_START)
	{
		$this->mUrl = $url;
		$this->mFlags = $flags;
		$this->mFetch =& new XCube_Delegate();
		$this->mFetch->add(array(&$this, 'fetchNaviControl'));
		$this->mGetTotalItems =& new XCube_Delegate();
	}
	function fetch()
	{
		$this->mFetch->call(new XCube_Ref($this));
	}
	function fetchNaviControl(&$navi)
	{	
		$root =& XCube_Root::getSingleton();
		$startKey = $navi->getStartKey();
		$perpageKey = $navi->getPerpageKey();
		if ($navi->mFlags & XCUBE_PAGENAVI_START) {
			$t_start = $root->mContext->mRequest->getRequest($navi->getStartKey());
			if ($t_start != null && intval($t_start) >= 0) {
				$navi->mStart = intval($t_start);
			}
		}
		if ($navi->mFlags & XCUBE_PAGENAVI_PERPAGE && !$navi->mPerpageFreeze) {
			$t_perpage = $root->mContext->mRequest->getRequest($navi->getPerpageKey());
			if ($t_perpage != null && intval($t_perpage) > 0) {
				$navi->mPerpage = intval($t_perpage);
			}
		}
	}
	function addExtra($key, $value)
	{
		$this->mExtra[$key] = $value;
	}
	function removeExtra($key)
	{
		if ($this->mExtra[$key]) {
			unset($this->mExtra[$key]);
		}
	}
	function getRenderBaseUrl($mask = null)
	{
		if ($mask == null) {
			$mask = array();
		}
		if (!is_array($mask)) {
			$mask = array($mask);
		}
		if(count($this->mExtra) > 0) {
			$tarr=array();
			foreach($this->mExtra as $key=>$value) {
				if (is_array($mask) && !in_array($key, $mask)) {
					$tarr[]=$key."=".urlencode($value);
				}
			}
			if (count($tarr)==0) {
				return $this->mUrl;
			}
			if(strpos($this->mUrl,"?")!==false) {
				return $this->mUrl."&amp;".implode("&amp;",$tarr);
			}
			else {
				return $this->mUrl."?".implode("&amp;",$tarr);
			}
		}
		return $this->mUrl;
	}
	function getRenderUrl($mask = null)
	{
		if ($mask != null && !is_array($mask)) {
			$mask = array($mask);
		}
		$demiliter = "?";
		$url = $this->getRenderBaseUrl($mask);
		if(strpos($url,"?")!==false) {
			$demiliter = "&amp;";
		}
		return $url . $demiliter . $this->getStartKey() . "=";
	}
	function renderUrlForSort()
	{
		if(count($this->mExtra) > 0) {
			$tarr=array();
			foreach($this->mExtra as $key=>$value) {
				$tarr[]=$key."=".urlencode($value);
			}
			$tarr[] = $this->getPerpageKey() . "=" . $this->mPerpage;
			if(strpos($this->mUrl,"?")!==false) {
				return $this->mUrl."&amp;".implode("&amp;",$tarr);
			}
			else {
				return $this->mUrl."?".implode("&amp;",$tarr);
			}
		}
		return $this->mUrl;
	}
	function renderUrlForPage($page = null)
	{
		$tarr=array();
		foreach($this->mExtra as $key=>$value) {
			$tarr[]=$key."=".urlencode($value);
		}
		foreach($this->mSort as $key=>$value) {
			$tarr[]=$key."=".urlencode($value);
		}
		$tarr[] = $this->getPerpageKey() . "=" . $this->getPerpage();
		if ($page !== null) {
			$tarr[] = $this->getStartKey() . '=' . intval($page);
		}
		if (strpos($this->mUrl,"?") !== false) {
			return $this->mUrl."&amp;".implode("&amp;",$tarr);
		}
		return $this->mUrl."?".implode("&amp;",$tarr);
	}
	function renderSortUrl($mask = null)
	{
		return $this->renderUrlForSort();
	}
	function setStart($start)
	{
		$this->mStart = intval($start);
	}
	function getStart()
	{
		return $this->mStart;
	}
	function setTotalItems($total)
	{
		$this->mTotal = intval($total);
		$this->_mIsSpecifiedTotal = true;
	}
	function getTotalItems()
	{
		if ($this->_mIsSpecifedTotalItems == false) {
			$this->mGetTotalItems->call(new XCube_Ref($this->mTotal));
			$this->_mIsSpecifedTotalItems = true;
		}
		return $this->mTotal;
	}
	function getTotalPages()
	{
		if ($this->getPerpage() > 0) {
			return ceil($this->getTotalItems() / $this->getPerpage());
		}
		return 0;
	}
	function setPerpage($perpage)
	{
		$this->mPerpage = intval($perpage);
	}
	function freezePerpage()
	{
		$this->mPerpageFreeze = true;
	}
	function getPerpage()
	{
		return $this->mPerpage;
	}
	function setPrefix($prefix)
	{
		$this->mPrefix = $prefix;
	}
	function getPrefix()
	{
		return $this->mPrefix;
	}
	function getStartKey()
	{
		return $this->mPrefix . "start";
	}
	function getPerpageKey()
	{
		return $this->mPrefix . "perpage";
	}
	function getCurrentPage()
	{
		return intval(floor(($this->getStart() + $this->getPerpage()) / $this->getPerpage()));
	}
	function hasPrivPage()
	{
		return ($this->getStart() - $this->getPerpage()) >= 0;
	}
	function getPrivStart()
	{
		$prev = $this->getStart() - $this->getPerpage();
		return ($prev > 0) ? $prev : 0;
	}
	function hasNextPage()
	{
		return $this->getTotalItems() > ($this->getStart() + $this->getPerpage());
	}
	function getNextStart()
	{
		$next = $this->getStart() + $this->getPerpage();
		return ($this->getTotalItems() > $next) ? $next : 0;
	}
}
?>
