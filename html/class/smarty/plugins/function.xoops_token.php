<?php
function smarty_function_xoops_token($params, &$smarty)
{
	$tokenName = null;
	$tokenValue = null;
	if (isset($params['form']) && is_object($params['form'])) {
		if(is_a($params['form'], 'XCube_ActionForm')) {
			$tokenName = $params['form']->getTokenName();
			$tokenValue = $params['form']->getToken();
		}
		else {
			die('You does not set ActionForm instance to place holder.');
		}
	}
	else {
		$tokenName = $params['name'];
		$tokenValue = $params['value'];
	}
	if ($tokenName != null && $tokenValue != null) {
		$tokenName = htmlspecialchars($tokenName, ENT_QUOTES);
		$tokenValue = htmlspecialchars($tokenValue, ENT_QUOTES);
		@printf('<input type="hidden" name="%s" value="%s" />', $tokenName, $tokenValue);
	}
}
?>
