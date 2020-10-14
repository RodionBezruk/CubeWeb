<?php
function S_PUBLIC_VAR($definition)
{
	$t_str = explode(' ', trim($definition));
	return array('name' => trim($t_str[1]), 'type' => trim($t_str[0]));
}
class XCube_Object
{
	var $mProperty = array();
	function isArray()
	{
		return false;
	}
	function getPropertyDefinition()
	{
	}
	function XCube_Object()
	{
		$fileds = $this->getPropertyDefinition();
		foreach ($fileds as $t_field) {
			$this->mProperty[$t_field['name']] = array(
				'type' => $t_field['type'],
				'value' => null
			);
		}
	}
	function prepare()
	{
	}
	function toArray()
	{
		$retArray = array();
		foreach ($this->mProperty as $t_key => $t_value) {
			$retArray[$t_key] = $t_value['value'];
		}
		return $retArray;
	}
	function loadByArray($vars)
	{
		foreach ($vars as $t_key => $t_value) {
			if (isset($this->mProperty[$t_key])) {
				$this->mProperty[$t_key]['value'] = $t_value;
			}
		}
	}
}
class XCube_ObjectArray
{
	function isArray()
	{
		return true;
	}
	function getClassName()
	{
	}
}
?>
