<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_PublicControllerStrategy extends Legacy_AbstractControllerStrategy
{
	var $mStatusFlag = LEGACY_CONTROLLER_STATE_PUBLIC;
	function Legacy_PublicControllerStrategy(&$controller)
	{
		parent::Legacy_AbstractControllerStrategy($controller);
		$controller->mRoot->mContext->mBaseRenderSystemName = "Legacy_RenderSystem";
		if (!defined("LEGACY_DEPENDENCE_RENDERER")) {
			define("LEGACY_DEPENDENCE_RENDERER", "Legacy_RenderSystem");
		}
	}
	function setupBlock()
	{
		$showFlag =0;
		$mid=0;
		if($this->mController->mRoot->mContext->mModule != null) {
			$showFlag = (preg_match("/index\.php$/i", xoops_getenv('PHP_SELF')) && $this->mController->mRoot->mContext->mXoopsConfig['startpage'] == $this->mController->mRoot->mContext->mXoopsModule->get('dirname'));
			$mid = $this->mController->mRoot->mContext->mXoopsModule->get('mid');
		}
		else {
			$pathArray = parse_url(!empty($_SERVER['PATH_INFO']) ? substr($_SERVER['PHP_SELF'],0,- strlen($_SERVER['PATH_INFO'])) : $_SERVER['PHP_SELF']);
			$mid = preg_match("#(/index\.php|/)$#i", @$pathArray['path']) ? -1 : 0;
		}
        $blockHandler =& xoops_gethandler('block');
		$showCenterFlag = (SHOW_CENTERBLOCK_LEFT | SHOW_CENTERBLOCK_CENTER | SHOW_CENTERBLOCK_RIGHT);
		$showRightFlag = SHOW_SIDEBLOCK_RIGHT;
		$showFlag = SHOW_SIDEBLOCK_LEFT | $showRightFlag | $showCenterFlag;
		$groups = is_object($this->mController->mRoot->mContext->mXoopsUser) ? $this->mController->mRoot->mContext->mXoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
		$blockObjects =& $blockHandler->getBlocks($groups, $mid, $showFlag);
		foreach($blockObjects as $blockObject) {
			$block =& Legacy_Utils::createBlockProcedure($blockObject);
			if ($block->prepare() !== false) {
				$this->mController->_mBlockChain[] =& $block;
			}
			unset($block);
			unset($blockObject);
		}
	}
	function &getMainThemeObject()
	{
		$handler =& xoops_getmodulehandler('theme', 'legacy');
		$theme =& $handler->get($this->mController->mRoot->mContext->getThemeName());
		if (is_object($theme)) {
			return $theme;
		}
		$root =& XCube_Root::getSingleton();
		foreach ($root->mContext->mXoopsConfig['theme_set_allowed'] as $theme) {
			$theme =& $handler->get($theme);
			if (is_object($theme)) {
				$root->mContext->setThemeName($theme->get('dirname'));
				return $theme;
			}
		}
		$objs =& $theme->getObjects();
		if (count($objs) > 0) {
			return $objs[0];
		}
		$theme = null;
		return $theme;
	}
	function isEnableCacheFeature()
	{
		return true;
	}
	function enableAccess()
	{
		if ($this->mController->mRoot->mContext->mModule != null) {
			$dirname = $this->mController->mRoot->mContext->mXoopsModule->get('dirname');
			return $this->mController->mRoot->mContext->mUser->isInRole("Module.${dirname}.Visitor");
		}
		return true;
	}
	function setupModuleLanguage()
	{
		$root =& XCube_Root::getSingleton();
		$root->mLanguageManager->loadModuleMessageCatalog($root->mContext->mXoopsModule->get('dirname'));
	}
}
?>
