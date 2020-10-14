<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/core/XCube_ActionForm.class.php";
class User_Mailjob_linkAdminDeletesForm extends XCube_ActionForm 
{
	function getTokenName()
	{
		return "module.user.Mailjob_linkAdminDeletesForm.TOKEN." . $this->get('mailjob_id');
	}
	function getTokenErrorMessage()
	{
		return null;
	}
	function prepare()
	{
		$this->mFormProperties['mailjob_id']=new XCube_IntProperty('mailjob_id');
		$this->mFormProperties['uid']=new XCube_IntArrayProperty('uid');
	}
}
?>
