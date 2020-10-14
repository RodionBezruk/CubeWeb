<?php
function smarty_function_legacy_button($params, &$smarty)
{
	if (isset($params['id'])) {
		$id = trim($params['id']);
		$name = "Legacy.Event.User.${id}";
		$text = isset($params['Text']) ? htmlspecialchars(trim($params['Text']), ENT_QUOTES) : null;
		$class = isset($params['class']) ? htmlspecialchars(trim($params['class']), ENT_QUOTES) : null;
		$string = "<input type='submit' id='${id}' name='${name}'";
		if ($text != null) {
			$string .= " value='${text}'";
		}
		if ($class != null) {
			$string .= " class='${class}'";
		}
		$string .= " />";
		print $string;
	}
}
?>
