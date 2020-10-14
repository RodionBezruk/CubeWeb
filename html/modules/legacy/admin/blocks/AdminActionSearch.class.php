<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_AdminActionSearch extends Legacy_AbstractBlockProcedure
{
	function getName()
	{
		return "action_search";
	}
	function getTitle()
	{
		return "TEST: AdminActionSearch";
	}
	function getEntryIndex()
	{
		return 0;
	}
	function isEnableCache()
	{
		return false;
	}
	function execute()
	{
		$render =& $this->getRenderTarget();
		$render->setAttribute('legacy_module', 'legacy');
		$render->setTemplateName('legacy_admin_block_actionsearch.html');
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem($this->getRenderSystemName());
		$renderSystem->renderBlock($render);
	}
	function hasResult()
	{
		return true;
	}
	function &getResult()
	{
		$dmy = "dummy";
		return $dmy;
	}
	function getRenderSystemName()
	{
		return 'Legacy_AdminRenderSystem';
	}
}
?>
