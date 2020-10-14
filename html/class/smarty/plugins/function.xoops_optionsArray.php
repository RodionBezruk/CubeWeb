<?php
function smarty_function_xoops_optionsArray($params, &$smarty)
{
	$tags = "";
	$objectArr =& $params['from'];
	$default = isset($params['default']) ? $params['default'] : null;
	$id = isset($params['id']) ? $params['id'] : null;
    $root =& XCube_Root::getSingleton();
    $textFilter =& $root->getTextFilter();
	foreach ($objectArr as $object) {
	    $value = $textFilter->toShow($object->get($params['value']));
	    $label = $textFilter->toShow($object->get($params['label']));
		$selected = "";
		if (is_array($default) && in_array($object->get($params['value']), $default)) {
			$selected = " selected=\"selected\"";
		}
		elseif (!is_array($default) && $object->get($params['value']) == $default) {
			$selected = " selected=\"selected\"";
		}
		if ($id) {
			$t_id = XOOPS_INPUT_DEFID_PREFIX . $id."_".$value;
			$tags .= "<option id=\"${t_id}\" value=\"${value}\"${selected}>${label}</option>\n";
		}
		else {
			$tags .= "<option value=\"${value}\"${selected}>${label}</option>\n";
		}
	}
	print $tags;
}
?>
