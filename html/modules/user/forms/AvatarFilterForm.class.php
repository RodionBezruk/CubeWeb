<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/user/class/AbstractFilterForm.class.php";
class User_AvatarFilterForm extends User_AbstractFilterForm
{
	var $mSort = 0;
	function fetch()
	{
		parent::fetch();
		$this->_mCriteria->add(new Criteria('avatar_display', 1));
		$this->_mCriteria->add(new Criteria('avatar_type', 'S'));
	}
}
?>
