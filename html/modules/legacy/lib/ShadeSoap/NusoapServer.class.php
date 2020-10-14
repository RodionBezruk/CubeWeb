<?php
if (!class_exists('soap_server')) exit();
class ShadeSoap_NusoapServer extends soap_server
{
	function invoke_method() {
		$this->debug('in invoke_method, methodname=' . $this->methodname . ' methodURI=' . $this->methodURI . ' SOAPAction=' . $this->SOAPAction);
		if ($this->wsdl) {
			if ($this->opData = $this->wsdl->getOperationData($this->methodname)) {
				$this->debug('in invoke_method, found WSDL operation=' . $this->methodname);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
			} elseif ($this->opData = $this->wsdl->getOperationDataForSoapAction($this->SOAPAction)) {
				$this->debug('in invoke_method, found WSDL soapAction=' . $this->SOAPAction . ' for operation=' . $this->opData['name']);
				$this->appendDebug('opData=' . $this->varDump($this->opData));
				$this->methodname = $this->opData['name'];
			} else {
				$this->debug('in invoke_method, no WSDL for operation=' . $this->methodname);
				$this->fault('Client', "Operation '" . $this->methodname . "' is not defined in the WSDL for this service");
				return;
			}
		} else {
			$this->debug('in invoke_method, no WSDL to validate method');
		}
		$class = '';
		$method = '';
		if (strpos($this->methodname, '..') > 0) {
			$delim = '..';
		} else if (strpos($this->methodname, '.') > 0) {
			$delim = '.';
		} else {
			$delim = '';
		}
		if (strlen($delim) > 0 && substr_count($this->methodname, $delim) == 1 &&
			class_exists(substr($this->methodname, 0, strpos($this->methodname, $delim)))) {
			$class = substr($this->methodname, 0, strpos($this->methodname, $delim));
			$method = substr($this->methodname, strpos($this->methodname, $delim) + strlen($delim));
			$this->debug("in invoke_method, class=$class method=$method delim=$delim");
		}
		if ($class == '') {
			if (!function_exists($this->methodname)) {
				$this->debug("in invoke_method, function '$this->methodname' not found!");
				$this->result = 'fault: method not found';
				$this->fault('Client',"method '$this->methodname' not defined in service");
				return;
			}
		} else {
			$method_to_compare = (substr(phpversion(), 0, 2) == '4.') ? strtolower($method) : $method;
			if (!in_array($method_to_compare, get_class_methods($class))) {
				$this->debug("in invoke_method, method '$this->methodname' not found in class '$class'!");
				$this->result = 'fault: method not found';
				$this->fault('Client',"method '$this->methodname' not defined in service");
				return;
			}
		}
		if(! $this->verify_method($this->methodname,$this->methodparams)){
			$this->debug('ERROR: request not verified against method signature');
			$this->result = 'fault: request failed validation against method signature';
			$this->fault('Client',"Operation '$this->methodname' not defined in service.");
			return;
		}
		$this->debug('in invoke_method, params:');
		$this->appendDebug($this->varDump($this->methodparams));
		$this->debug("in invoke_method, calling '$this->methodname'");
		if ($class == '') {
			$this->debug('in invoke_method, calling function using call_user_func_array()');
			$call_arg = "$this->methodname";	
		} elseif ($delim == '..') {
			$this->debug('in invoke_method, calling class method using call_user_func_array()');
			$call_arg = array ($class, $method);
		} else {
			$this->debug('in invoke_method, calling instance method using call_user_func_array()');
			$instance = new $class ();
			$call_arg = array(&$instance, $method);
		}
		$root =& XCube_Root::getSingleton();
		$retValue = call_user_func_array($call_arg, array($root->mContext->mUser, $this->methodparams));
		if (is_array($retValue)) {
			$retValue = $this->_encodeUTF8($retValue, $root->mLanguageManager);
		}
		else {
			$retValue = $root->mLanguageManager->encodeUTF8($retValue);
		}
		$this->methodreturn = $retValue;	
        $this->debug('in invoke_method, methodreturn:');
        $this->appendDebug($this->varDump($this->methodreturn));
		$this->debug("in invoke_method, called method $this->methodname, received $this->methodreturn of type ".gettype($this->methodreturn));
	}
	function _encodeUTF8($arr, &$languageManager)
	{
		foreach (array_keys($arr) as $key) {
			if (is_array($arr[$key])) {
				$arr[$key] = $this->_encodeUTF8($arr[$key], $languageManager);
			}
			else {
				$arr[$key] = $languageManager->encodeUTF8($arr[$key]);
			}
		}
		return $arr;
	}
}
?>
