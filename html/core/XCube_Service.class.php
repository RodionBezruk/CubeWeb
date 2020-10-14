<?php
function S_PUBLIC_FUNC($definition)
{
	$ret = null;
	$pos = strpos($definition, '(');
	if ($pos > 0) {
		$func_nameArr = explode(' ', substr($definition, 0, $pos));
		$func_paramArr = explode(',', substr($definition, $pos + 1, -1));
		$params = array();
		foreach ($func_paramArr as $t_param) {
			if (strlen($t_param) > 0) {
				$t_str = explode(' ', trim($t_param));
				$params[trim($t_str[1])] = trim($t_str[0]);
			}
		}
		$ret = array();
		$ret['name'] = trim($func_nameArr[1]);
		$ret['in'] = $params;
		$ret['out'] = trim($func_nameArr[0]);
	}
	return $ret;
}
class XCube_Service
{
	var $mServiceName = "";
	var $mNameSpace = "";
	var $mClassName = "XCube_Service";
	var $_mActionStrategy = null;
	var $_mTypes = array();
	var $_mFunctions = array();
	function XCube_Service()
	{
	}
	function prepare()
	{
	}
	function addType($className)
	{
		$this->_mTypes[] = $className;
	}
	function addFunction()
	{
		$args = func_get_args();
		if (func_num_args() == 3) {
			$this->_addFunctionStandard($args[0], $args[1], $args[2]);
		}
		elseif (func_num_args() == 1 && is_array($args[0])) {
			$this->_addFunctionStandard($args[0]['name'], $args[0]['in'], $args[0]['out']);
		}
	}
	function _addFunctionStandard($name, $in, $out)
	{
		$this->_mFunctions[$name] = array(
			'out' => $out,
			'name' => $name,
			'in' => $in
		);
	}
	function register($name, &$procedure)
	{
	}
}
class XCube_AbstractServiceClient
{
	var $mService;
	var $mClientErrorStr;
	var $mUser = null;
	function XCube_AbstractServiceClient(&$service)
	{
		$this->mService =& $service;
	}
	function prepare()
	{
	}
	function setUser(&$user)
	{
		$this->mUser =& $user;
	}
	function call()
	{
	}
	function getOperationData($operation)
	{
	}
	function setError($message)
	{
		$this->mClientErrorStr = $message;
	}
	function getError()
	{
		return !empty($this->mClientErrorStr) ? $this->mClientErrorStr : $this->mService->mErrorStr;
	}
}
class XCube_ServiceClient extends XCube_AbstractServiceClient
{
	function call($operation, $params)
	{
		$this->mClientErrorStr = null;
		if(!is_object($this->mService)) {
			$this->mClientErrorStr = "This instance is not connected to service";
			return null;
		}
		$root =& XCube_Root::getSingleton();
		$request_bak =& $root->mContext->mRequest;
		unset($root->mContext->mRequest);
		$root->mContext->mRequest =& new XCube_GenericRequest($params);
		if (isset($this->mService->_mFunctions[$operation])) {
			$ret = call_user_func(array($this->mService, $operation));
			unset($root->mContext->mRequest);
			$root->mContext->mRequest =& $request_bak;
			return $ret;
		}
		else {
			$this->mClientErrorStr = "operation ${operation} not present.";
			return null;
		}
	}
}
?>
