<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/BlockFilterForm.class.php";
class Legacy_BlockInstallFilterForm extends Legacy_BlockFilterForm
{
	function _getVisible()
	{
		return 0;
	}
}
?>
