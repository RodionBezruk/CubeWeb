<?php
define ("XOOPS_DHTMLTAREA_DEFID_PREFIX", "legacy_xoopsform_");
define ("XOOPS_DHTMLTAREA_DEFAULT_COLS", "50");
define ("XOOPS_DHTMLTAREA_DEFAULT_ROWS", "5");
function smarty_function_xoops_dhtmltarea($params, &$smarty)
{
	if (!XC_CLASS_EXISTS('xoopsformelement')) {
		require_once XOOPS_ROOT_PATH . "/class/xoopsformloader.php";
	}
	$form = null;
    $root =& XCube_Root::getSingleton();
    $textFilter =& $root->getTextFilter();
	if (isset($params['name'])) {
		$name = trim($params['name']);
		$class = isset($params['class']) ? trim($params['class']) : null;
		$cols = isset($params['cols']) ? intval($params['cols']) : XOOPS_DHTMLTAREA_DEFAULT_COLS;
		$rows = isset($params['rows']) ? intval($params['rows']) : XOOPS_DHTMLTAREA_DEFAULT_ROWS;
		$value = isset($params['value']) ? $textFilter->toEdit($params['value']) : null;
		$id = isset($params['id']) ? trim($params['id']) : XOOPS_DHTMLTAREA_DEFID_PREFIX . $name;
		$form =& new XoopsFormDhtmlTextArea($name, $name, $value, $rows, $cols);
		$form->setId($id);
		if ($class != null) {
			$form->setClass($class);
		}
		print $form->render();
	}
}
?>
