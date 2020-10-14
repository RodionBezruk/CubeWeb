<?php
class XCube_Ref
{
	var $_mObject = null;
	function XCube_Ref(&$obj)
	{
		$this->_mObject =& $obj;
	}
	function &getObject()
	{
		return $this->_mObject;
	}
}
define("XCUBE_DELEGATE_PRIORITY_1", 10);
define("XCUBE_DELEGATE_PRIORITY_2", 20);
define("XCUBE_DELEGATE_PRIORITY_3", 30);
define("XCUBE_DELEGATE_PRIORITY_4", 40);
define("XCUBE_DELEGATE_PRIORITY_5", 50);
define("XCUBE_DELEGATE_PRIORITY_6", 60);
define("XCUBE_DELEGATE_PRIORITY_7", 70);
define("XCUBE_DELEGATE_PRIORITY_8", 80);
define("XCUBE_DELEGATE_PRIORITY_9", 90);
define("XCUBE_DELEGATE_PRIORITY_10", 100);
define("XCUBE_DELEGATE_PRIORITY_FIRST", XCUBE_DELEGATE_PRIORITY_1);
define("XCUBE_DELEGATE_PRIORITY_NORMAL", XCUBE_DELEGATE_PRIORITY_5);
define("XCUBE_DELEGATE_PRIORITY_FINAL", XCUBE_DELEGATE_PRIORITY_10);
class XCube_Delegate
{
	var $_mSignatures = array();
	var $_mCallbacks = array();
	var $_mHasCheckSignatures = false;
	var $_mIsLazyRegister = false;
	var $_mLazyRegisterName = null;
    var $_mUniqueID;
	function XCube_Delegate()
	{
		if (func_num_args() > 0) {
			$this->_setSignatures(func_get_args());
		}
		$this->_mUniqueID = md5(uniqid(rand(), true));
	}
	function _setSignatures($args)
	{
		$this->_mSignatures = $args;
		for ($i=0 ; $i<count($args) ; $i++) {
			$idx = strpos($this->_mSignatures[$i], " &");
			if ($idx !== false) {
				$this->_mSignatures[$i] = substr($this->_mSignatures[$i], 0, $idx);
			}
		}
		$this->_mHasCheckSignatures = true;
	}
	function register($delegateName)
	{
		$root =& XCube_Root::getSingleton();
		if ($root->mDelegateManager != null) {
			$this->_mIsLazyRegister = false;
			$this->_mLazyRegisterName = null;
			return $root->mDelegateManager->register($delegateName, $this);
		}
		$this->_mIsLazyRegister = true;
		$this->_mLazyRegisterName = $delegateName;
		return false;
	}
	function add($callback, $param2 = null, $param3 = null)
	{
		$priority = XCUBE_DELEGATE_PRIORITY_NORMAL;
		$filepath = null;
		if (!is_array($callback) && strstr($callback, '::') !== false) {
			$tmp = explode("::", $callback);
			if (count($tmp) == 2) {
				$callback = array ($tmp[0], $tmp[1]);
			}
		}
		if ($param2 !== null && is_int($param2)) {
			$priority = $param2;
			$filepath = ($param3 !== null && is_string($param3)) ? $param3 : null;
		}
		elseif ($param2 !== null && is_string($param2)) {
			$filepath = $param2;
		}
		$this->_mCallbacks[$priority][] = array($callback, $filepath);
        ksort($this->_mCallbacks);
	}
	function delete($delcallback)
	{
		foreach (array_keys($this->_mCallbacks) as $priority) {
            foreach (array_keys($this->_mCallbacks[$priority]) as $idx) {
                $callback = $this->_mCallbacks[$priority][$idx][0];
                if (XCube_DelegateUtils::_compareCallback($callback, $delcallback)) {
                    unset($this->_mCallbacks[$priority][$idx]);
                }
                if (count($this->_mCallbacks[$priority])==0) {
                    unset($this->_mCallbacks[$priority]);
                }
            }
        }
    }
	function reset()
	{
	    unset($this->_mCallbacks);
	    $this->_mCallbacks = array();
    }
	function call()
	{
		$args = func_get_args();
		$num = func_num_args();
		if ($this->_mIsLazyRegister) {
			$this->register($this->_mLazyRegisterName);
		}
		if ($this->_mHasCheckSignatures) {
			if (count($this->_mSignatures) != $num) {
				return false;
			}
		}
		$param = array();
		for ($i=0 ; $i<$num ;$i++) {
			if (is_a($args[$i], "XCube_Ref")) {
				$args[$i] =& $args[$i]->getObject();
			}
			if ($this->_mHasCheckSignatures) {
				if (!isset($this->_mSignatures[$i])) {
					return false;
				}
				switch ($this->_mSignatures[$i]) {
					case "void":
						break;
					case "bool":
						if (!empty($args[$i])) {
							$args[$i] = $args[$i] ? true : false;
						}
						break;
					case "int":
						if (!empty($args[$i])) {
							$args[$i] = intval($args[$i]);
						}
						break;
					case "float":
						if (!empty($args[$i])) {
							$args[$i] = floatval($args[$i]);
						}
						break;
					case "string":
						if (!empty($args[$i]) && !is_string($args[$i])) {
							return false;
						}
						break;
					default:
						if (!is_a($args[$i], $this->_mSignatures[$i])) {
							return false;
						}
				}
			}
			$param[] = '$args[' . $i . ']';
		}
		if (count($param) > 0) {
			$argstr = "(" . join($param, ",") . ");";
		}
		else {
			$argstr = "()";
		}
		foreach ($this->_mCallbacks as $callback_arrays) {
            foreach ($callback_arrays as $callback_array) {
                $callback = $callback_array[0];
               	if ($callback_array[1] != null && file_exists($callback_array[1])) {
               		require_once $callback_array[1];
               	}
               	if (is_callable($callback)) {
               		call_user_func_array($callback, $args);
               	}
            }
		}
	}
    function isEmpty()
    {
    	return (count($this->_mCallbacks) == 0);
    }
	function getID()
	{
	    return $this->_mUniqueID;
	}
}
class XCube_DelegateManager
{
	var $_mCallbacks = array();
	var $_mCallbackParameters = array();
	var $_mDelegates = array();
	function XCube_DelegateManager()
	{
	}
	function register($name, &$delegate)
	{
		if (!isset($this->_mDelegates[$name][$delegate->getID()])) {
			$this->_mDelegates[$name][$delegate->getID()] =& $delegate;
			if (isset($this->_mCallbacks[$name]) && count($this->_mCallbacks[$name]) > 0) {
				foreach (array_keys($this->_mCallbacks[$name]) as $key) {
					$delegate->add($this->_mCallbacks[$name][$key], $this->_mCallbackParameters[$name][$key][0], $this->_mCallbackParameters[$name][$key][1]);
				}
			}
			return true;
		}
		else {
			return false;
		}
	}
	function add($name, $callback, $param3 = null, $param4 = null)
	{
		if (isset($this->_mDelegates[$name])) {
		    foreach(array_keys($this->_mDelegates[$name]) as $key) {
			    $this->_mDelegates[$name][$key]->add($callback, $param3, $param4);
			}
		}
		$this->_mCallbacks[$name][] = $callback;
		$this->_mCallbackParameters[$name][] = array('0' => $param3, '1' => $param4);
	}
	function delete($name, $delcallback)
	{
		if (isset($this->_mDelegates[$name])) {
		    foreach(array_keys($this->_mDelegates[$name]) as $key) {
    			$this->_mDelegates[$name][$key]->delete($delcallback);
    	    }
    	}
	    if (isset($this->_mCallbacks[$name])) {
	        foreach(array_keys($this->_mCallbacks[$name]) as $key) {
                $callback = $this->_mCallbacks[$name][$key];
                if (XCube_DelegateUtils::_compareCallback($callback, $delcallback)) {
                    unset($this->_mCallbacks[$name][$key]);
                    unset($this->_mCallbackParameters[$name][$key]);
                }
	        }
	    }
	}
	function reset($name)
	{
		if (isset($this->_mDelegates[$name])) {
		    foreach(array_keys($this->_mDelegates[$name]) as $key) {
    			$this->_mDelegates[$name][$key]->reset();
    		}
		}
	    if (isset($this->_mCallbacks[$name])) {
	        unset($this->_mCallbacks[$name]);
	        unset($this->_mCallbackParameters[$name]);
	    }
	}
	function isEmpty($name)
	{
		if (isset($this->_mDelegates[$name])) {
			return $this->_mDelegates[$name]->isEmpty();
		}
		return isset($this->_mCallbacks[$name]) ? (count($this->_mCallbacks[$name]) == 0) : false;
	}
	function getDelegates()
	{
	    return $this->_mDelegates;
	}
}
class XCube_DelegateUtils
{
	function XCube_DelegateUtils()
	{
	}
    function call()
    {
        $args = func_get_args();
        $num = func_num_args();
        if ($num > 0) {
            $delegateName = $args[0];
            if ($num > 1) {
                array_shift($args);
            }
        } else {
            return false;
        }
        $root =& XCube_Root::getSingleton();
        if ($root->mDelegateManager != null) {
            $delegates = $root->mDelegateManager->getDelegates();
            if (isset($delegates[$delegateName])) {
                $keys = array_keys($delegates[$delegateName]);
                $delegate =& $delegates[$delegateName][$keys[0]];
            } else {
                $delegate =& new XCube_Delegate;
                $root->mDelegateManager->register($delegateName, $delegate);
            }
        }
        return call_user_func_array(array(&$delegate,'call'),$args);
    }
    function raiseEvent()
    {
        $args = func_get_args();
        $num = func_num_args();
        if ($num > 0) {
            return call_user_func_array(array('XCube_DelegateUtils','call'),$args);
        }
    }
    function applyStringFilter()
    {
        $args = func_get_args();
        $num = func_num_args();
        if ($num > 1) {
            $delegateName = $args[0];
            $string = $args[1];
            if (!empty($string) && is_string($string)) {
                return "";
            }
            $args[1] =& new XCube_Ref($string);
            call_user_func_array(array('XCube_DelegateUtils','call'),$args);
            return $string;
        } else {
            return "";
        }
    }
    function _compareCallback($callback1, $callback2)
    {
        if (!is_array($callback1) && !is_array($callback2) && ($callback1 === $callback2)) {
            return true;
        } elseif (is_array($callback1) && is_array($callback2) && (gettype($callback1[0]) === gettype($callback2[0])) 
                                                               && ($callback1[1] === $callback2[1])) {
            if (!is_object($callback1[0]) && ($callback1[0] === $callback2[0])) {
                return true;
            } elseif (is_object($callback1[0]) && (get_class($callback1[0]) === get_class($callback2[0]))) {
                return true;
            }
        }
        return false;
    }
}
?>
