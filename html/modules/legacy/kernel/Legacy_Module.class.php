<?php
class Legacy_AbstractModule
{
	var $mAttributes = array();
	var $mXoopsModule = null;
	var $mModuleConfig = array();
	var $mCacheInfo = null;
	var $mRender = null;
	function Legacy_AbstractModule(&$module)
	{
		$this->setXoopsModule($module);
		if ($module->get('hasconfig') == 1 || $module->get('hascomments') == 1 || $module->get('hasnotification') == 1) {
			$handler =& xoops_gethandler('config');
			$this->setModuleConfig($handler->getConfigsByCat(0, $module->get('mid')));
		}
	}
	function setAttribute($key, $value)
	{
		$this->mAttributes[$key] = $value;
	}
	function hasAttribute($key)
	{
		return isset($this->mAttributes[$key]);
	}
	function getAttribute($key)
	{
		return isset($this->mAttributes[$key]) ? $this->mAttributes[$key] : null;
	}
	function setXoopsModule(&$xoopsModule)
	{
		$this->mXoopsModule =& $xoopsModule;
	}
	function &getXoopsModule()
	{
		return $this->mXoopsModule;
	}
	function setModuleConfig($config)
	{
		$this->mModuleConfig = $config;
	}
	function getModuleConfig($key = null)
	{
		if ($key == null) {
			return $this->mModuleConfig;
		}
		return isset($this->mModuleConfig[$key]) ? $this->mModuleConfig[$key] : null;
	}
	function &getCacheInfo()
	{
		if (!is_object($this->mCacheInfo)) {
			$this->_createCacheInfo();
		}
		return $this->mCacheInfo;
	}
	function _createCacheInfo()
	{
		$this->mCacheInfo =& new Legacy_ModuleCacheInformation();
		$this->mCacheInfo->mURL = xoops_getenv('REQUEST_URI');
		$this->mCacheInfo->setModule($this->mXoopsModule);
	}
	function &getRenderTarget()
	{
		if ($this->mRender == null) {
			$this->_createRenderTarget();
		}
		return $this->mRender;
	}
	function _createRenderTarget()
	{
		$renderSystem =& $this->getRenderSystem();
		$this->mRender =& $renderSystem->createRenderTarget('main');
		if ($this->mXoopsModule != null) {
			$this->mRender->setAttribute('legacy_module', $this->mXoopsModule->get('dirname'));
		}
	}
	function getRenderSystemName()
	{
		$root =& XCube_Root::getSingleton();
		return $root->mContext->mBaseRenderSystemName;
	}
	function &getRenderSystem()
	{
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem($this->getRenderSystemName());
		return $renderSystem;
	}
	function isActive()
	{
		if (!is_object($this->mXoopsModule)) {	
			return false;
		}
		return $this->mXoopsModule->get('isactive') ? true : false;
	}
	function isEnableCache()
	{
		if (xoops_getenv('REQUEST_METHOD') == 'POST') {
			return false;
		}
		$root =& XCube_Root::getSingleton();
		return is_object($this->mXoopsModule) && !empty($root->mContext->mXoopsConfig['module_cache'][$this->mXoopsModule->get('mid')]);
	}
	function &createCacheInfo()
	{
		$this->mCacheInfo =& new Legacy_ModuleCacheInformation();
		$this->mCacheInfo->mURL = xoops_getenv('REQUEST_URI');
		$this->mCacheInfo->setModule($this->mXoopsModule);
		return $this->mCacheInfo;
	}
	function startup()
	{
	}
	function doActionSearch(&$searchArgs)
	{
	}
	function doLegacyGlobalSearch($queries, $andor, $max_hit, $start, $uid)
	{
	}
	function hasAdminIndex()
	{
		return false;
	}
	function getAdminIndex()
	{
		return null;
	}
	function getAdminMenu()
	{
	}
}
class Legacy_ModuleAdapter extends Legacy_AbstractModule
{
	var $_mAdminMenuLoadedFlag = false;
	var $mAdminMenu = null;
	function doActionSearch(&$searchArgs)
	{
		if(!is_object($searchArgs)) {
			return;
		}
		$this->mXoopsModule->loadAdminMenu();
		if(count($this->mXoopsModule->adminmenu) == 0 && !isset($this->mXoopsModule->modinfo['config']) ) {
			return;
		}
		if(isset($this->mXoopsModule->modinfo['config'])&&count($this->mXoopsModule->modinfo['config'])>0) {
			$findFlag = false;
			foreach($searchArgs->getKeywords() as $word) {
				if (stristr(_PREFERENCES, $word) !== false) {
					$root =& XCube_Root::getSingleton();
					$searchArgs->addRecord($this->mXoopsModule->getVar('name'), $root->mController->getPreferenceEditUrl($this->mXoopsModule), _PREFERENCES);
					$findFlag = true;
					break;
				}
			}
			if (!$findFlag) {
				$configInfos=array();
				foreach($this->mXoopsModule->modinfo['config'] as $config) {
					if(isset($config['title']))
						$configInfos[]=@constant($config['title']);
					if(isset($config['description']))
						$configInfos[]=@constant($config['description']);
					if(isset($config['options'])&&count($config['options'])>0) {
						foreach($config['options'] as $key=>$val) {
							$configInfos[]=(@constant($key) ? @constant($key) : $key);
						}
					}
				}
				$findFlag=true;
				foreach($searchArgs->getKeywords() as $word) {
					$findFlag&=(stristr(implode(" ",$configInfos),$word)!==false);
				}
				if($findFlag) {
					$searchArgs->addRecord($this->mXoopsModule->getVar('name'),
					                  XOOPS_URL.'/modules/legacy/admin/index.php?action=PreferenceEdit&amp;confmod_id='.$this->mXoopsModule->getVar('mid'),
					                  _PREFERENCES );
				}
			}
		}
		if(count($this->mXoopsModule->adminmenu)>0) {
			foreach($this->mXoopsModule->adminmenu as $menu) {
				$findFlag=true;
				foreach($searchArgs->getKeywords() as $word) {
					$tmpFlag=false;
					$tmpFlag|=(stristr($menu['title'],$word)!==false);
					if(isset($menu['keywords'])) {
						$keyword=is_array($menu['keywords']) ? implode(" ",$menu['keywords']) : $menu['keywords'];
						$tmpFlag|=(stristr($keyword,$word)!==false);
					}
					$findFlag&=$tmpFlag;
				}
				if($findFlag) {
					$url="";
					if(isset($menu['absolute'])&&$menu['absolute']) {
						$url=$menu['link'];
					}
					else {
						$url=XOOPS_URL."/modules/".$this->mXoopsModule->getVar('dirname')."/".$menu['link'];
					}
					$searchArgs->addRecord($this->mXoopsModule->getVar('name'),$url,$menu['title']);
				}
			}
		}
		if ($this->mXoopsModule->hasHelp()) {
			$findFlag = false;
			foreach($searchArgs->getKeywords() as $word) {
				if (stristr(_HELP, $word) !== false) {
					$root =& XCube_Root::getSingleton();
					$searchArgs->addRecord($this->mXoopsModule->getVar('name'), $root->mController->getHelpViewUrl($this->mXoopsModule), _HELP);
					$findFlag = true;
					break;
				}
			}
			if (!$findFlag) {
				$root =& XCube_Root::getSingleton();
				$language = $root->mContext->getXoopsConfig('language');
				$helpfile = $this->mXoopsModule->getHelp();
				$dir = XOOPS_MODULE_PATH . "/" . $this->mXoopsModule->getVar('dirname') . "/language/" . $language. "/help";
				if (!file_exists($dir . "/" . $helpfile)) {
					$dir = XOOPS_MODULE_PATH . "/" . $this->mXoopsModule->getVar('dirname') . "/language/english/help";
						if (!file_exists($dir . "/" . $helpfile)) {
							return;
						}
				}
				$lines = file($dir . "/" . $helpfile);
				foreach ($lines as $line) {
					foreach($searchArgs->getKeywords() as $word) {
						if (stristr($line, $word) !== false) {
							$url = XOOPS_MODULE_URL . "/legacy/admin/index.php?action=Help&amp;dirname=" . $this->mXoopsModule->getVar('dirname');
							$searchArgs->addRecord($this->mXoopsModule->getVar('name'), $url, _HELP);
							return;
						}
					}
				}
			}
		}
	}
	function doLegacyGlobalSearch($queries, $andor, $max_hit, $start, $uid)
	{
		$ret = array();
		$results = $this->mXoopsModule->search($queries, $andor, $max_hit, $start, $uid);
		if (is_array($results) && count($results) > 0) {
			foreach ($results as $result) {
				$item = array();
				if (isset($result['image']) && strlen($result['image']) > 0) {
					$item['image'] = XOOPS_URL . '/modules/' . $this->mXoopsModule->get('dirname') . '/' . $result['image'];
				}
				else {
					$item['image'] = XOOPS_URL . '/images/icons/posticon2.gif';
				}
				$item['link'] = XOOPS_URL . '/modules/' . $this->mXoopsModule->get('dirname') . '/' . $result['link'];
				$item['title'] = $result['title'];
				$item['uid'] = $result['uid'];
				$item['time'] = isset($result['time']) ? $result['time'] : 0;
				$ret[] = $item;
			}
		}
		return $ret;
	}
	function hasAdminIndex()
	{
		$dmy =& $this->mXoopsModule->getInfo();
		return isset($this->mXoopsModule->modinfo['adminindex']) && $this->mXoopsModule->modinfo['adminindex'] != null;
  	}
	function getAdminIndex()
	{
		$dmy =& $this->mXoopsModule->getInfo();
		return XOOPS_MODULE_URL . '/' . $this->mXoopsModule->get('dirname') . '/' . $this->mXoopsModule->modinfo['adminindex'];
	}
	function getAdminMenu()
	{
		if ($this->_mAdminMenuLoadedFlag) {
			return $this->mAdminMenu;
		}
		$dmy =& $this->mXoopsModule->getInfo();
		$root =& XCube_Root::getSingleton();
		$this->mXoopsModule->loadAdminMenu();
		if ($this->mXoopsModule->get('hasnotification')
		    || ($this->mXoopsModule->getInfo('config') && is_array($this->mXoopsModule->getInfo('config')))
		    || ($this->mXoopsModule->getInfo('comments') && is_array($this->mXoopsModule->getInfo('comments')))) {
				$this->mXoopsModule->adminmenu[] = array(
					'link' => $root->mController->getPreferenceEditUrl($this->mXoopsModule),
					'title' => _PREFERENCES,
					'absolute' => true);
		}
		if ($this->mXoopsModule->hasHelp()) {
			$this->mXoopsModule->adminmenu[] = array('link' =>  $root->mController->getHelpViewUrl($this->mXoopsModule),
			                              'title' => _HELP,
			                              'absolute' => true);
		}
		$this->_mAdminMenuLoadedFlag = true;
		if ($this->mXoopsModule->adminmenu) {
			foreach ($this->mXoopsModule->adminmenu as $menu) {
				if (!isset($menu['absolute']) || (isset($menu['absolute']) && $menu['absolute'] != true)) {
					$menu['link'] = XOOPS_MODULE_URL . '/' . $this->mXoopsModule->get('dirname') . '/' . $menu['link'];
				}
				$this->mAdminMenu[] = $menu;
			}
		}
		return $this->mAdminMenu;
	}
}
?>
