<?php
if (!defined('XOOPS_ROOT_PATH')) die();
class Legacy_AbstractBlockProcedure
{
	var $mRender = null;
	function Legacy_AbstractBlockProcedure()
	{
	}
	function prepare()
	{
		return true;
	}
	function &getRenderTarget()
	{
		if (!is_object($this->mRender)) {
			$this->_createRenderTarget();
		}
		return $this->mRender;
	}
	function getRenderSystemName()
	{
		$root =& XCube_Root::getSingleton();
		return $root->mContext->mBaseRenderSystemName;
	}
	function &_createRenderTarget()
	{
		$this->mRender =& new XCube_RenderTarget();
		$this->mRender->setType(XCUBE_RENDER_TARGET_TYPE_BLOCK);
		return $this->mRender;
	}
	function getId()
	{
	}
	function getName()
	{
	}
	function isEnableCache()
	{
	}
	function getCacheTime()
	{
	}
	function getTitle()
	{
		return $this->_mBlock->get('title');
	}
	function getEntryIndex()
	{
	}
	function getWeight()
	{
	}
	function isDisplay()
	{
		return true;
	}
	function &createCacheInfo()
	{
		$cacheInfo =& new Legacy_BlockCacheInformation();
		$cacheInfo->setBlock($this);
		return $cacheInfo;
	}
	function execute()
	{
	}
}
class Legacy_BlockProcedure extends Legacy_AbstractBlockProcedure
{
	var $_mBlock = null;
	var $mRender = null;
	function Legacy_BlockProcedure(&$block)
	{
		$this->_mBlock =& $block;
	}
	function prepare()
	{
		return true;
	}
	function getId()
	{
		return $this->_mBlock->get('bid');
	}
	function getName()
	{
		return $this->_mBlock->get('name');
	}
	function isEnableCache()
	{
		return $this->_mBlock->get('bcachetime') > 0;
	}
	function getCacheTime()
	{
		return $this->_mBlock->get('bcachetime');
	}
	function getTitle()
	{
		return $this->_mBlock->get('title');
	}
	function getEntryIndex()
	{
		return $this->_mBlock->getVar('side');
	}
	function getWeight()
	{
		return $this->_mBlock->get('weight');
	}
	function _hasVisibleOptionForm()
	{
		return true;
	}
	function getOptionForm()
	{
		return null;
	}
}
class Legacy_BlockProcedureAdapter extends Legacy_BlockProcedure
{
	var $_mDisplayFlag = true;
	function execute()
	{
		$result =& $this->_mBlock->buildBlock();
		if (empty($result)) {
			$this->_mDisplayFlag = false;
			return;
		}
		$render =& $this->getRenderTarget();
		$render->setAttribute("mid", $this->_mBlock->get('mid'));
		$render->setAttribute("bid", $this->_mBlock->get('bid'));
		if ($this->_mBlock->get('template') == null) {
			$render->setTemplateName('system_dummy.html');
			$render->setAttribute('dummy_content', $result['content']);
		}
		else {
			$render->setTemplateName($this->_mBlock->get('template'));
			$render->setAttribute('block', $result);
		}
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem($this->getRenderSystemName());
		$renderSystem->renderBlock($render);
	}
	function isDisplay()
	{
		return $this->_mDisplayFlag;
	}
	function _hasVisibleOptionForm()
	{
		return ($this->_mBlock->get('func_file') && $this->_mBlock->get('edit_func'));
	}
	function getOptionForm()
	{
		if ($this->_mBlock->get('func_file') && $this->_mBlock->get('edit_func')) {
			$func_file = XOOPS_MODULE_PATH . "/" . $this->_mBlock->get('dirname') . "/blocks/" . $this->_mBlock->get('func_file');
			if (file_exists($func_file)) {
				require $func_file;
				$edit_func = $this->_mBlock->get('edit_func');
				$options = explode('|', $this->_mBlock->get('options'));
				if (function_exists($edit_func)) {
					$root =& XCube_Root::getSingleton();
					$langManager =& $root->getLanguageManager();
					$langManager->loadBlockMessageCatalog($this->_mBlock->get('dirname'));
					return call_user_func($edit_func, $options);
				}
			}
		}
		if ($this->_mBlock->get('options')) {
			$root =& XCube_Root::getSingleton();
	        $textFilter =& $root->getTextFilter();
			$buf = "";
			$options = explode('|', $this->_mBlock->get('options'));
			foreach ($options as $val) {
				$val = $textFilter->ToEdit($val);
				$buf .= "<input type='hidden' name='options[]' value='${val}'/>";
			}
			return $buf;
		}
		return null;
	}
}
?>
