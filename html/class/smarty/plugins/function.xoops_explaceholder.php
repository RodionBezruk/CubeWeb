<?php
function smarty_function_xoops_explaceholder($params, &$smarty)
{
	$buf = null;
	if (isset($params['control'])) {
		XCube_DelegateUtils::call('Legacy.Event.Explaceholder.Get.' . $params['control'], new XCube_Ref($buf), $params);
		if ($buf === null) {
			XCube_DelegateUtils::call('Legacy.Event.Explaceholder.Get', new XCube_Ref($buf), $params['control'], $params);
		}
	}
	return $buf;
}
?>
