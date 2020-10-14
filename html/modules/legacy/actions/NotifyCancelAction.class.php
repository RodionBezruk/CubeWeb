<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_NotifyCancelAction extends Legacy_Action
{
	function getDefaultView(&$contoller, &$xoopsUser)
	{
		$contoller->executeForward(XOOPS_URL . '/');
	}
	function execute(&$contoller, &$xoopsUser)
	{
		$contoller->executeForward(XOOPS_URL . '/');
	}
}
?>
