<?php
if ( !defined("XOOPS_C_DATABASE_INCLUDED") ) {
	define("XOOPS_C_DATABASE_INCLUDED",1);
class XoopsDatabase
	{
		var $prefix = '';
		var $logger;
		function XoopsDatabase()
		{
		}
		function setLogger(&$logger)
		{
			$this->logger =& $logger;
		}
		function setPrefix($value)
		{
			$this->prefix = $value;
		}
		function prefix($tablename='')
		{
			if ( $tablename != '' ) {
				return $this->prefix .'_'. $tablename;
			} else {
				return $this->prefix;
			}
		}
	}
}
class Database
{
	function &getInstance()
	{
		$instance =& XoopsDatabaseFactory::getDatabaseConnection();
		return $instance;
	}
}
?>
