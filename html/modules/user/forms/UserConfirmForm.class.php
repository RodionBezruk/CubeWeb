<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class User_UserConfirmForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		return "module.user.UserConfirmForm.TOKEN";
	}
	function prepare()
	{
	}
}
?>
