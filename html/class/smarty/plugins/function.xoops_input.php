<?php
define ("XOOPS_INPUT_DEFID_PREFIX", "legacy_xoopsform_");
function smarty_function_xoops_input($params, &$smarty)
{
	if (isset($params['name'])) {
        $root =& XCube_Root::getSingleton();
        $textFilter =& $root->getTextFilter();
		$name = trim($params['name']);
		$key = isset($params['key']) ? trim($params['key']) : null;
		$type = isset($params['type']) ? strtolower(trim($params['type'])) : "text";
		$value = isset($params['value']) ? $textFilter->toEdit($params['value']) : null;
		$class = isset($params['class']) ? trim($params['class']) : null;
        $style = isset($params['style']) ? trim($params['style']) : null;
		$id = isset($params['id']) ? trim($params['id']) : XOOPS_INPUT_DEFID_PREFIX . $name;
		$size = isset($params['size']) ? intval($params['size']) : null;
		$maxlength = isset($params['maxlength']) ? intval($params['maxlength']) : null;
		$default = isset($params['default']) ? trim($params['default']) : null;
		$disabled = (isset($params['disabled']) && $params['disabled'] != false) ? true : false;
		if ($key != null) {
			$string = "<input name=\"${name}[${key}]\"";
		}
		else {
			$string = "<input name=\"${name}\"";
		}
		if ($class) {
			$string .= " class=\"${class}\"";
		}
        if ($style) {
            $string .= " style=\"${style}\"";
        }
		if ($type == "checkbox" || $type == "radio") {
			$string .= " id=\"{$id}_{$value}\"";
		}else {
			$string .= " id=\"{$id}\"";
		}
		if ($type) {
			$string .= " type=\"${type}\"";
		}
		if ($size) {
			$string .= " size=\"${size}\"";
		}
		if($maxlength) {
			$string .= " maxlength=\"${maxlength}\"";
		}
		if($value !== null) {
			$string .= " value=\"${value}\"";
		}
		if (isset($params['default'])) {
			$default = trim($params['default']);
			if ($value == $default) {
				if ($type == "checkbox" || $type == "radio") {
					$string .= " checked=\"checked\"";
				}
			}
		}
		if ($disabled) {
			$string .= " disabled=\"disabled\"";
		}
		$string .= " />";
		print $string;
	}
}
?>
