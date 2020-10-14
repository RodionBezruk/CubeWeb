<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/modules/legacy/kernel/Legacy_Module.class.php";
class Legacy_HttpContext extends XCube_HttpContext
{
	var $mXoopsUser = null;
	var $mModule = null;
	var $mXoopsModule = null;
	var $mXoopsConfig = array();
	var $mModuleConfig = array();
	var $mBaseRenderSystemName = "";
	function getXoopsConfig($id = null)
	{
		if ($id != null) {
			return isset($this->mXoopsConfig[$id]) ? $this->mXoopsConfig[$id] : null;
		}
		return $this->mXoopsConfig;
	}
	function setThemeName($name)
	{
		parent::setThemeName($name);
		$this->mXoopsConfig['theme_set'] = $name;
		$GLOBALS['xoopsConfig']['theme_set'] = $name;
	}
}
?>
