<?php
if (!defined('XCUBE_CORE_PATH')) define('XCUBE_CORE_PATH', dirname(__FILE__));
require_once XCUBE_CORE_PATH . '/XCube_Property.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Validator.class.php';
require_once XCUBE_CORE_PATH . '/XCube_FormFile.class.php';
class XCube_ActionForm
{
	var $mContext = null;
	var $mUser = null;
	var $mFormProperties = array();
	var $mFieldProperties = array();
	var $mErrorFlag = false;
	var $mErrorMessages = array();
	var $_mToken = null;
	function XCube_ActionForm()
	{
		$root =& XCube_Root::getSingleton();
		$this->mContext =& $root->getContext();
		$this->mUser =& $this->mContext->getUser();
	}
	function prepare()
	{
	}
	function getTokenName()
	{
		return null;
	}
	function getToken()
	{
		if ($this->_mToken == null) {
			srand(microtime() * 100000);
			$root=&XCube_Root::getSingleton();
			$salt = $root->getSiteConfig('Cube', 'Salt');
			$this->_mToken = md5($salt . uniqid(rand(), true));
			$_SESSION['XCUBE_TOKEN'][$this->getTokenName()] = $this->_mToken;
		}
		return $this->_mToken;
	}
	function getTokenErrorMessage()
	{
		return _TOKEN_ERROR;	
	}
	function set()
	{
		if (isset($this->mFormProperties[func_get_arg(0)])) {
			if (func_num_args() == 2) {
				$value = func_get_arg(1);
				$this->mFormProperties[func_get_arg(0)]->setValue($value);
			}
			elseif (func_num_args() == 3) {
				$index = func_get_arg(1);
				$value = func_get_arg(2);
				$this->mFormProperties[func_get_arg(0)]->setValue($index, $value);
			}
		}
	}
	function setVar()
	{
		if (isset($this->mFormProperties[func_get_arg(0)])) {
			if (func_num_args() == 2) {
				$this->mFormProperties[func_get_arg(0)]->setValue(func_get_arg(1));
			}
			elseif (func_num_args() == 3) {
				$this->mFormProperties[func_get_arg(0)]->setValue(func_get_arg(1), func_get_arg(2));
			}
		}
	}
	function get($key, $index=null)
	{
		return isset($this->mFormProperties[$key]) ? $this->mFormProperties[$key]->getValue($index) : null;
	}
	function getVar($key,$index=null)
	{
		return $this->get($key, $index);
	}
	function &getFormProperties()
	{
		return $this->mFormProperties;
	}
	function fetch()
	{
		foreach (array_keys($this->mFormProperties) as $name) {
			if ($this->mFormProperties[$name]->hasFetchControl()) {
				$this->mFormProperties[$name]->fetch($this);
			}
			else {
				$value = $this->mContext->mRequest->getRequest($name);
				$this->mFormProperties[$name]->set($value);
			}
			$methodName = "fetch" . ucfirst($name);
			if (method_exists($this, $methodName)) {
				$this->$methodName();
			}
		}
	}
	function _validateToken()
	{
		if ($this->getTokenName() != null) {
			$key = strtr($this->getTokenName(), '.', '_');
			$token = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
			if (get_magic_quotes_gpc()) {
				$token = stripslashes($token);
			}
			$flag = true;
			if (!isset($_SESSION['XCUBE_TOKEN'][$this->getTokenName()])) {
				$flag = false;
			}
			elseif ($_SESSION['XCUBE_TOKEN'][$this->getTokenName()] != $token) {
				unset($_SESSION['XCUBE_TOKEN'][$this->getTokenName()]);
				$flag = false;
			}
			if (!$flag) {
				$message = $this->getTokenErrorMessage();
				if ($message == null) {
					$this->mErrorFlag = true;
				}
				else {
					$this->addErrorMessage($message);
				}
			}
			unset($_SESSION['XCUBE_TOKEN'][$this->getTokenName()]);
		}
	}
	function validate()
	{
		$this->_validateToken();
		foreach (array_keys($this->mFormProperties) as $name) {
			if (isset($this->mFieldProperties[$name])) {
				if ($this->mFormProperties[$name]->isArray()) {
					foreach (array_keys($this->mFormProperties[$name]->mProperties) as $_name) {
						$this->mFieldProperties[$name]->validate($this->mFormProperties[$name]->mProperties[$_name]);
					}
				}
				else {
					$this->mFieldProperties[$name]->validate($this->mFormProperties[$name]);
				}
			}
		}
		foreach (array_keys($this->mFormProperties) as $name) {
			$methodName = "validate" . ucfirst($name);
			if (method_exists($this, $methodName)) {
				$this->$methodName();
			}
		}
	}
	function hasError()
	{
		return (count($this->mErrorMessages) > 0 || $this->mErrorFlag);
	}
	function addErrorMessage($message)
	{
		$this->mErrorMessages[] = $message;
	}
	function getErrorMessages()
	{
		return $this->mErrorMessages;
	}
	function load(&$obj)
	{
	}
	function update(&$obj)
	{
	}
}
class XCube_FieldProperty
{
	var $mForm;
	var $mDepends;
	var $mMessages;
	var $mVariables;
	function XCube_FieldProperty(&$form)
	{
		$this->mForm =& $form;
	}
	function setDependsByArray($dependsArr)
	{
		foreach ($dependsArr as $dependName) {
			$instance =& XCube_DependClassFactory::factoryClass($dependName);
			if ($instance !== null) {
				$this->mDepends[$dependName] =& $instance;
			}
			unset($instance);
		}
	}
	function addMessage($name, $message)
	{
		if (func_num_args() >= 2) {
			$args = func_get_args();
			$this->mMessages[$args[0]]['message'] = $args[1];
			for ($i = 0; isset($args[$i + 2]); $i++) {
				$this->mMessages[$args[0]]['args'][$i] = $args[$i + 2];
			}
		}
	}
	function renderMessage($name)
	{
		if (!isset($this->mMessages[$name]))
			return null;
		$message = $this->mMessages[$name]['message'];
		if (isset($this->mMessages[$name]['args'])) {
			$message = XCube_Utils::formatString($message, $this->mMessages[$name]['args']);
		}
		return $message;
	}
	function addVar($name, $value)
	{
		$this->mVariables[$name] = $value;
	}
	function validate(&$form)
	{
		if (is_array($this->mDepends) && count($this->mDepends) > 0) {
			foreach ($this->mDepends as $name => $depend) {
				if (!$depend->isValid($form, $this->mVariables)) {
					$this->mForm->mErrorFlag = true;
					$this->mForm->addErrorMessage($this->renderMessage($name));
				}
				else {
				}
			}
		}
	}
}
class XCube_DependClassFactory
{
	function &factoryClass($dependName)
	{
		static $_cache;
		if (!is_array($_cache)) {
			$_cache = array();
		}
		if (!isset($_cache[$dependName])) {
			$class_name = "XCube_" . ucfirst($dependName) . "Validator";
			if (class_exists($class_name)) {
				$_cache[$dependName] =& new $class_name();
			}
			else {
				die ("This is an error message of Alpha or Beta series. ${dependName} Validator is not found.");
			}
		}
		return $_cache[$dependName];
	}
}
?>
