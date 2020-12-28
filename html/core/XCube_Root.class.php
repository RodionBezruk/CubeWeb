<?php
if (!defined('XCUBE_CORE_PATH')) define('XCUBE_CORE_PATH', dirname(__FILE__));
require_once XCUBE_CORE_PATH . '/XCube_HttpContext.class.php';
function XC_CLASS_EXISTS($className)
{
	if (version_compare(PHP_VERSION, "5.0", ">=")) {
		return class_exists($className, false);
	}
	else {
		return class_exists($className);
	}
}
class XCube_Root
{
	var $mController = null;
	var $mLanguageManager = null;
	var $mDelegateManager = null;
	var $mServiceManager = null;
	var $_mRenderSystems = array();
	var $mSiteConfig = array();
	var $mPermissionManager = null;
	var $mRoleManager = null;
	var $mCacheSystem = null;
	var $mTextFilter = null;
	var $mContext = null;
	var $mSession = null;
	function XCube_Root()
	{
	}
	function &getSingleton()
	{
		static $instance;
		if (!isset($instance))
			$instance = new XCube_Root();
		return $instance;
	}
	function loadSiteConfig()
	{
		if (func_num_args() == 0) {
			die("FETAL: open error: site setting config.");
		}
		$file = func_get_arg(0);
		if(!file_exists($file)) {
			die("FETAL: open error: site setting config.");
		}
		$this->setSiteConfig(parse_ini_file($file, true));
		if (func_num_args() > 1) {
			for ($i = 1; $i < func_num_args(); $i++) {
				$overrideFile = func_get_arg($i);
				if (file_exists($overrideFile)) {
					$this->overrideSiteConfig(parse_ini_file($overrideFile, true));
				}
			}
		}
	}
	function setSiteConfig($config)
	{
		$this->mSiteConfig = $config;
	}
	function overrideSiteConfig($config)
	{
		foreach ($config as $_overKey=>$_overVal) {
			if (array_key_exists($_overKey, $this->mSiteConfig)) {
				$this->mSiteConfig[$_overKey] = array_merge($this->mSiteConfig[$_overKey], $_overVal);
			}
			else {
				$this->mSiteConfig[$_overKey] = $_overVal;
			}
		}
	}
	function getSiteConfig()
	{
		if (func_num_args() == 0) {
			return $this->mSiteConfig;
		}
		elseif (func_num_args() == 1) {
			if (isset($this->mSiteConfig[func_get_arg(0)])) {
				return $this->mSiteConfig[func_get_arg(0)];
			}
		}
		elseif (func_num_args() == 2) {
			if (isset($this->mSiteConfig[func_get_arg(0)][func_get_arg(1)])) {
				return $this->mSiteConfig[func_get_arg(0)][func_get_arg(1)];
			}
		}
		elseif (func_num_args() == 3) {
			if (isset($this->mSiteConfig[func_get_arg(0)][func_get_arg(1)])) {
				return $this->mSiteConfig[func_get_arg(0)][func_get_arg(1)];
			}
			else {
				return func_get_arg(2); 
			}
		}
		return null;
	}
	function setupController()
	{
		$controllerName = $this->mSiteConfig['Cube']['Controller'];
        if(isset($this->mSiteConfig[$controllerName]['root'])) {
            $this->mController =& $this->_createInstance($this->mSiteConfig[$controllerName]['class'], $this->mSiteConfig[$controllerName]['path'], $this->mSiteConfig[$controllerName]['root']);
        }
        else {
            $this->mController =& $this->_createInstance($this->mSiteConfig[$controllerName]['class'], $this->mSiteConfig[$controllerName]['path']);
        }
		$this->mController->prepare($this);
	}
	function &getController()
	{
		return $this->mController;
	}
	function setLanguageManager(&$languageManager)
	{
		$this->mLanguageManager =& $languageManager;
	}
	function &getLanguageManager()
	{
		return $this->mLanguageManager;
	}
	function setDelegateManager(&$delegateManager)
	{
		$this->mDelegateManager =& $delegateManager;
	}
	function &getDelegateManager()
	{
		return $this->mDelegateManager;
	}
	function setServiceManager(&$serviceManager)
	{
		$this->mServiceManager =& $serviceManager;
	}
	function &getServiceManager()
	{
		return $this->mServiceManager;
	}
	function &getRenderSystem($name)
	{
		if (isset($this->_mRenderSystems[$name])) {
			return $this->_mRenderSystems[$name];
		}
		$chunkName = $this->mSiteConfig['RenderSystems'][$name];
		if (isset($this->mSiteConfig[$chunkName]['root'])) {
			$this->_mRenderSystems[$name] =& $this->_createInstance($this->mSiteConfig[$chunkName]['class'], $this->mSiteConfig[$chunkName]['path'], $this->mSiteConfig[$chunkName]['root']);
		}
		else {
			$this->_mRenderSystems[$name] =& $this->_createInstance($this->mSiteConfig[$chunkName]['class'], $this->mSiteConfig[$chunkName]['path']);
		}
		if (!is_object($this->_mRenderSystems[$name])) {
			die("NO");
		}
		$this->_mRenderSystems[$name]->prepare($this->mController);
		return $this->_mRenderSystems[$name];
	}
	function setPermissionManager(&$manager)
	{
		$this->mPermissionManager =& $manager;
	}
	function &getPermissionManager()
	{
		return $this->mPermissionManager;
	}
	function setTextFilter(&$textFilter)
	{
		$this->mTextFilter =& $textFilter;
	}
	function &getTextFilter()
	{
	    if (!empty($this->mTextFilter)) return $this->mTextFilter;
	    if (!empty($this->mController)) { 
    	    $this->mController->mSetupTextFilter->call(new XCube_Ref($this->mTextFilter));
    	    return $this->mTextFilter;
	    }
	    $ret = null;
	    return $ret;
	}
	function setRoleManager(&$manager)
	{
		$this->mRoleManager =& $manager;
	}
	function setContext(&$context)
	{
		$this->mContext =& $context;
	}
	function &getContext()
	{
		return $this->mContext;
	}
	function setSession(&$session)
	{
		$this->mSession =& $session;
	}
	function &getSession()
	{
		return $this->mSession;
	}
	function &_createInstance($className, $classPath = null, $root = null)
	{
		$ret = null;
		if ($classPath != null) {
			if ($root == null) {
				$root = $this->mSiteConfig['Cube']['Root'];
			}
			if (is_file($root . $classPath)) {
				require_once $root . $classPath;
			}
			else {
				require_once $root . $classPath . "/" . $className . ".class.php";
			}
		}
		if (XC_CLASS_EXISTS($className)) {
			$ret =& new $className();
		}
		return $ret;
	}
}
?>
