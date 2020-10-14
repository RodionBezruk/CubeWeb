<?php
class XCube_ActionFilter
{
	var $mController;
	var $mRoot;
	function XCube_ActionFilter(&$controller)
	{
		$this->mController =& $controller;
		$this->mRoot =& $this->mController->mRoot;
	}
	function preFilter()
	{
	}
	function preBlockFilter()
	{
	}
	function postFilter()
	{
	}
}
?>
