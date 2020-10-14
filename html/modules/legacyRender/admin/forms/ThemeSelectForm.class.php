<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class LegacyRender_ThemeSelectForm extends XCube_ActionForm
{
	function getTokenName()
	{
		return "module.legacyRender.ThemeSelectForm.TOKEN";
	}
	function prepare()
	{
		$this->mFormProperties['select'] =& new XCube_BoolArrayProperty('select');
		$this->mFormProperties['choose'] =& new XCube_StringArrayProperty('choose');
	}
	function getChooseTheme()
	{
		$ret = array();
		$themes = $this->get('choose');
		foreach ($themes as $theme => $dmy) {
			return $theme;
		}
		return null;
	}
	function load(&$objs)
	{
		foreach ($objs as $obj) {
			$this->set('select', $obj->get('id'), $obj->get('enable_select'));
		}
	}
	function update(&$objs)
	{
		foreach (array_keys($objs) as $key) {
			$objs[$key]->set('enable_select', $this->get('select', $objs[$key]->get('id')));
		}
	}
}
?>
