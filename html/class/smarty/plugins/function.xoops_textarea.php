<?php
define ("XOOPS_TEXTAREA_DEFID_PREFIX", "legacy_xoopsform_");
define ("XOOPS_TEXTAREA_DEFAULT_COLS", "50");
define ("XOOPS_TEXTAREA_DEFAULT_ROWS", "5");
function smarty_function_xoops_textarea($params, &$smarty)
{
    $root =& XCube_Root::getSingleton();
    $textFilter =& $root->getTextFilter();
	if (isset($params['name'])) {
		$name = trim($params['name']);
		$class = isset($params['class']) ? trim($params['class']) : null;
        $style = isset($params['style']) ? trim($params['style']) : null;
		$cols = isset($params['cols']) ? intval($params['cols']) : XOOPS_TEXTAREA_DEFAULT_COLS;
		$rows = isset($params['rows']) ? intval($params['rows']) : XOOPS_TEXTAREA_DEFAULT_ROWS;
		$value = isset($params['value']) ? $textFilter->toEdit($params['value']) : null;
		$id = isset($params['id']) ? trim($params['id']) : XOOPS_TEXTAREA_DEFID_PREFIX . $name;
		$readonly = isset($params['readonly']) ? (bool)(trim($params['readonly'])) : false;
		$string = "<textarea name=\"${name}\" cols=\"${cols}\" rows=\"${rows}\"";
		if ($class) {
			$string .= " class=\"${class}\"";
		}
        if ($style) {
            $string .= " style=\"${style}\"";
        }
		$string .= " id=\"$id\"";
		if($readonly) {
			$string .= " readonly=\"readonly\"";
		}
		$string .= ">" . $value . "</textarea>";
		print $string;
	}
}
?>
