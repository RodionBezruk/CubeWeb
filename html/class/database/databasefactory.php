<?php
class XoopsDatabaseFactory
{
	function XoopsDatabaseFactory()
	{
	}
	function &getDatabaseConnection()
	{
		static $instance;
		if (!isset($instance)) {
			$file = XOOPS_ROOT_PATH.'/class/database/'.XOOPS_DB_TYPE.'database.php';
			require_once $file;
			if (defined("XOOPS_DB_ALTERNATIVE") && class_exists(XOOPS_DB_ALTERNATIVE)) {
				$class = XOOPS_DB_ALTERNATIVE;
			}
			else if (!defined('XOOPS_DB_PROXY')) {
				$class = 'Xoops'.ucfirst(XOOPS_DB_TYPE).'DatabaseSafe';
			} else {
				$class = 'Xoops'.ucfirst(XOOPS_DB_TYPE).'DatabaseProxy';
			}
			$instance = new $class();
			$instance->setLogger(XoopsLogger::instance());
			$instance->setPrefix(XOOPS_DB_PREFIX);
			if (!$instance->connect()) {
				trigger_error("Unable to connect to database", E_USER_ERROR);
			}
		}
		return $instance;
	}
	function &getDatabase()
	{
		static $database;
		if (!isset($database)) {
			$file = XOOPS_ROOT_PATH.'/class/database/'.XOOPS_DB_TYPE.'database.php';
			require_once $file;
			if (!defined('XOOPS_DB_PROXY')) {
				$class = 'Xoops'.ucfirst(XOOPS_DB_TYPE).'DatabaseSafe';
			} else {
				$class = 'Xoops'.ucfirst(XOOPS_DB_TYPE).'DatabaseProxy';
			}
			$database =& new $class();
		}
		return $database;
	}
}
?>
