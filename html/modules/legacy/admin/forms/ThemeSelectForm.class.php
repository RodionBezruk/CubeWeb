<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class Legacy_ThemeSelectForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacy.ThemeSelectForm.TOKEN";
	}
	function prepare()
	{
		$this->mFormProperties['select'] =& new XCube_BoolArrayProperty('select');
		$this->mFormProperties['choose'] =& new XCube_StringArrayProperty('choose');
	}
	function getChooseTheme()
	{
		foreach ($this->get('choose') as $dirname => $dmy) {
			return $dirname;
		}
		return null;
	}
	function getSelectableTheme()
	{
		$ret = array();
		foreach ($this->get('select') as $themeName => $isSelect) {
			if ($isSelect == 1) {
				$ret[] = $themeName;
			}
		}
		return $ret;
	}
	function load(&$themeArr)
	{
		foreach ($themeArr as $themeName) {
			$this->set('select', $themeName, 1);
		}
	}
}
?>
