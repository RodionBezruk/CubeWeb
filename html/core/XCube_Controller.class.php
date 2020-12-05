<?php
if (!defined('XCUBE_CORE_PATH')) define('XCUBE_CORE_PATH', dirname(__FILE__));
require_once XCUBE_CORE_PATH . '/XCube_Root.class.php';
require_once XCUBE_CORE_PATH . '/XCube_ActionFilter.class.php';
require_once XCUBE_CORE_PATH . '/XCube_RenderSystem.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Delegate.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Object.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Service.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Identity.class.php';
require_once XCUBE_CORE_PATH . '/XCube_RoleManager.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Permission.class.php';
require_once XCUBE_CORE_PATH . '/XCube_LanguageManager.class.php';
require_once XCUBE_CORE_PATH . '/XCube_ActionForm.class.php';
require_once XCUBE_CORE_PATH . '/XCube_TextFilter.class.php';
require_once XCUBE_CORE_PATH . '/XCube_Session.class.php';
class XCube_Controller
{
	var $mRoot;
	var $_mBlockChain = array();
	var $_mFilterChain = array();
	var $_mLoadedFilterNames = array();
	var $mDB;
    var $mLocale = null;
	var $mLanguage = null;
	var $mSetupUser = null;
	var $mExecute = null;
	var $mSetupTextFilter = null;
	function XCube_Controller()
	{
		$this->_mBlockChain = array();
		$this->_mFilterChain = array();
		$this->_mLoadedFilterNames = array();
		$this->mSetupUser =& new XCube_Delegate();
		$this->mExecute =& new XCube_Delegate();
		$this->mSetupTextFilter =&  new XCube_Delegate();
	    $this->mSetupTextFilter->add('XCube_TextFilter::getInstance',XCUBE_DELEGATE_PRIORITY_FINAL);
	}
	function prepare(&$root)
	{
		$this->mRoot =& $root;
		$this->mRoot->setDelegateManager($this->_createDelegateManager());
		$this->mRoot->setServiceManager($this->_createServiceManager());
		$this->mRoot->setPermissionManager($this->_createPermissionManager());
		$this->mRoot->setRoleManager($this->_createRoleManager());
		$this->mRoot->setContext($this->_createContext());
	}
	function executeCommon()
	{
		$this->_setupFilterChain();
		$this->_processFilter();
		$this->_setupEnvironment();
		$this->_setupDB();
        $this->_setupLanguage();
        $this->_setupTextFilter();
		$this->_setupConfig();
		$this->_processPreBlockFilter();	
		$this->_setupSession();
		$this->_setupUser();
	}
	function executeHeader()
	{
		$this->_setupBlock();
		$this->_processBlock();
	}
	function execute()
	{
		$this->mExecute->call(new XCube_Ref($this));
	}
	function executeView()
	{
	}
	function executeForward($url, $time = 0, $message = null)
	{
		header("location: " . $url);
		exit();
	}
	function executeRedirect($url, $time = 1, $message = null)
	{
		$this->executeForward($url, $time, $message);
	}
	function addActionFilter(&$filter)
	{
		$this->_mFilterChain[] =& $filter;
	}
	function _setupFilterChain()
	{
	}
	function _setupEnvironment()
	{
	}
	function _setupDB()
	{
	}
	function &getDB()
	{
		return $this->mDB;
	}
	function _setupLanguage()
	{
		$this->mRoot->mLanguageManager =& new XCube_LanguageManager();
	}
	function _setupTextFilter()
	{
	    $textFilter = null;
	    $this->mSetupTextFilter->call(new XCube_Ref($textFilter));
	    $this->mRoot->setTextFilter($textFilter);
	}
	function _setupConfig()
	{
	}
	function _setupSession()
	{
	    $this->mRoot->setSession(new XCube_Session());
	}
	function _setupUser()
	{
		$this->mSetupUser->call(new XCube_Ref($this->mRoot->mContext->mUser), new XCube_Ref($this), new XCube_Ref($this->mRoot->mContext));
	}
	function _processFilter()
	{
		foreach (array_keys($this->_mFilterChain) as $key) {
			$this->_mFilterChain[$key]->preFilter();
		}
	}
	function _setupBlock()
	{
	}
	function _processBlock()
	{
	}
	function _processPreBlockFilter()
	{
		foreach (array_keys($this->_mFilterChain) as $key) {
			$this->_mFilterChain[$key]->preBlockFilter();
		}
	}
	function _processPostFilter()
	{
		foreach (array_reverse(array_keys($this->_mFilterChain)) as $key) {
			$this->_mFilterChain[$key]->postFilter();
		}
	}
	function _processPreload($path)
	{
		$path = $path . "/";
		if (is_dir($path)) {
			if ($handler = opendir($path)) {
				while (($file = readdir($handler)) !== false) {
					if (preg_match("/(\w+)\.class\.php$/", $file, $matches)) {
						require_once $path . $file;
						$className = $matches[1];
						if (XC_CLASS_EXISTS($className) && !isset($this->_mLoadedFilterNames[$className])) {
							$this->_mLoadedFilterNames[$className] = true;
							$instance =& new $className($this);
							$this->addActionFilter($instance);
						}
					}
				}
				closedir($handler);
			}
		}
	}
	function &_createDelegateManager()
	{
		$delegateManager =& new XCube_DelegateManager();
		return $delegateManager;
	}
	function &_createServiceManager()
	{
		require_once XCUBE_CORE_PATH . '/XCube_ServiceManager.class.php';
		$serviceManager =& new XCube_ServiceManager();
		return $serviceManager;
	}
	function &_createPermissionManager()
	{
		$chunkName = $this->mRoot->getSiteConfig('Cube', 'PermissionManager');
		$manager =& $this->mRoot->_createInstance($this->mRoot->getSiteConfig($chunkName, 'class'), $this->mRoot->getSiteConfig($chunkName, 'path'));
		return $manager;
	}
	function &_createRoleManager()
	{
		$chunkName = $this->mRoot->getSiteConfig('Cube', 'RoleManager');
		$manager =& $this->mRoot->_createInstance($this->mRoot->getSiteConfig($chunkName, 'class'), $this->mRoot->getSiteConfig($chunkName, 'path'));
		return $manager;
	}
	function &_createContext()
	{
		$context =& new XCube_HttpContext();
		$request =& new XCube_HttpRequest();
		$context->setRequest($request);
		return $context;
	}
}
?>
