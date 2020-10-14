<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyRender_StartupXoopsTpl extends XCube_ActionFilter
{
	function postFilter()
	{
		$dmy =& $this->mRoot->getRenderSystem('Legacy_RenderSystem');
	}
}
?>
