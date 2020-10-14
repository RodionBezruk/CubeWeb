<?php
class Legacy_Utils
{
	function checkSystemModules()
	{
		$root=&XCube_Root::getSingleton();
		$systemModules = array_map('trim', explode(',', $root->getSiteConfig('Cube', 'SystemModules')));
		$recommendedModules = array_map('trim', explode(',', $root->getSiteConfig('Cube', 'RecommendedModules')));
		$moduleHandler =& xoops_gethandler('module');
		$uninstalledModules = array();
		$disabledModules = array();
		foreach ($systemModules as $systemModule) {
			if (!empty($systemModule)) {
				if (!($moduleObject =& $moduleHandler->getByDirname($systemModule))) {
					$uninstalledModules[] = $systemModule;
				}
				elseif (!$moduleObject->get('isactive')) {
					$disabledModules[] = $systemModule;
				}
			}
		}
		if (count($uninstalledModules) == 0 && count($disabledModules) == 0) {
			return true;
		}
		else {
			return array('uninstalled' =>$uninstalledModules, 'disabled'=>$disabledModules, 'recommended'=>$recommendedModules);
		}
	}
	function &createModule($module)
	{
		$instance = null;
		XCube_DelegateUtils::call('Legacy_Utils.CreateModule', new XCube_Ref($instance), $module);
		if (is_object($instance) && is_a($instance, 'Legacy_AbstractModule')) {
			return $instance;
		}
		$dirname = $module->get('dirname');
		$className = ucfirst($dirname) . "_Module";
		if (!class_exists($className)) {
			$filePath = XOOPS_ROOT_PATH . "/modules/${dirname}/class/Module.class.php";
			if (file_exists($filePath)) {
				require_once $filePath;
			}
		}
		if (class_exists($className)) {
			$instance =& new $className($module);
		}
		else {
			$instance =& new Legacy_ModuleAdapter($module);
		}
		return $instance;
	}
	function &createBlockProcedure(&$block)
	{
		$retBlock = null;
		XCube_DelegateUtils::call('Legacy_Utils.CreateBlockProcedure', new XCube_Ref($retBlock), $block);
		if (is_object($retBlock) && is_a($retBlock, 'Legacy_AbstractBlockProcedure')) {
			return $retBlock;
		}
		$func = $block->get('show_func');
		if (substr($func, 0, 4) == 'cl::') {
			$className = ucfirst($block->get('dirname')) . '_' . substr($func, 4);
			if (!class_exists($className)) {
				$filePath = XOOPS_ROOT_PATH . '/modules/' . $block->get('dirname') . '/blocks/' . $block->get('func_file');
				if (!file_exists($filePath)) {
					$retBlock =& new Legacy_BlockProcedureAdapter($block);
					return $retBlock;
				}
				require_once $filePath;
				if (!class_exists($className)) {
					$retBlock =& new Legacy_BlockProcedureAdapter($block);
					return $retBlock;
				}
			}
			$retBlock =& new $className($block);
		}
		else {
			$retBlock =& new Legacy_BlockProcedureAdapter($block);
		}
		return $retBlock;
	}
	function raiseUserControlEvent()
	{
		$root =& XCube_Root::getSingleton();
		foreach (array_keys($_REQUEST) as $key) {
			if (strpos($key, 'Legacy_Event_User_') === 0) {
				$eventName = substr($key, 18);
				XCube_DelegateUtils::call('Legacy.Event.User.' . $eventName);
				$root->mContext->mAttributes['userEvent'][$eventName] = true;
			}
		}
	}
	function convertVersionFromModinfoToInt($version)
	{
		return round(100 * floatval($version));
	}
	function convertVersionIntToFloat($version)
	{
		return round(floatval(intval($version) / 100), 2);
	}
}
?>
