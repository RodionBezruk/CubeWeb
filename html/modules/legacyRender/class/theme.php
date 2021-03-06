<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class LegacyRenderThemeObject extends XoopsSimpleObject
{
	var $mPackage = array();
	var $mActiveResource = true;
	function LegacyRenderThemeObject()
	{
		$this->initVar('id', XOBJ_DTYPE_INT, '', true);
		$this->initVar('name', XOBJ_DTYPE_STRING, '', true, 255);
		$this->initVar('tplset_id', XOBJ_DTYPE_INT, '0', true);
		$this->initVar('enable_select', XOBJ_DTYPE_BOOL, '0', true);
	}
	function loadPackage()
	{
		$themeDir = XOOPS_THEME_PATH . "/" . $this->get('name');
		if (file_exists($mnfFile = $themeDir . "/manifesto.ini.php")) {
			$this->mPackage = parse_ini_file($mnfFile, true);
		}
		if (isset($this->mPackage['Manifesto'])) {
			if (isset($this->mPackage['Manifesto']) && isset($this->mPackage['Manifesto']['Depends'])) {
				$this->mActiveResource = ($this->mPackage['Manifesto']['Depends'] == "Legacy_RenderSystem");
			}
		}
		else {
			$file = XOOPS_THEME_PATH . "/" . $this->get('name') . "/theme.html";
			$this->mActiveResource = file_exists($file);
		}
	}
	function isActiveResource()
	{
		return $this->mActiveResource;
	}
}
class LegacyRenderThemeHandler extends XoopsObjectGenericHandler
{
	var $mTable = "legacyrender_theme";
	var $mPrimary = "id";
	var $mClass = "LegacyRenderThemeObject";
	function &getByName($themeName)
	{
		$criteria = new Criteria('name', $themeName);
		$obj =& $this->getObjects($criteria);
		if (count($obj) > 0) {
			return $obj[0];
		}
		else {
			$obj =& $this->create();
			return $obj;
		}
	}
	function searchThemes()
	{
		$themeList = array();
		if($handler=opendir(XOOPS_THEME_PATH)) {
			while(($dir=readdir($handler))!==false) {
				if($dir=="." || $dir=="..") {
					continue;
				}
				$themeDir=XOOPS_THEME_PATH."/".$dir;
				if(is_dir($themeDir)) {
					$manifesto = array();
					if (file_exists($mnfFile = $themeDir . "/manifesto.ini.php")) {
						$manifesto = parse_ini_file($mnfFile, true);
					}
					if(count($manifesto) > 0) {
						if(isset($manifesto['Manifesto']) && isset($manifesto['Manifesto']['Depends']) && preg_match('/Legacy_RenderSystem(\s|,|$)/', $manifesto['Manifesto']['Depends'])) {
							$themeList[]=$dir;
						}
					}
					else {
						$file=$themeDir."/theme.html";
						if(file_exists($file)) {
							$themeList[]=$dir;
						}
					}
				}
			}
			closedir($handler);
		}
		return $themeList;
	}
	function updateThemeList()
	{
		$diskThemeNames = $this->searchThemes();
		$DBthemes =& $this->getObjects();
		foreach ($diskThemeNames as $name) {
			$findFlag = false;
			foreach ($DBthemes as $theme) {
				if ($theme->get('name') == $name) {
					$findFlag = true;
					break;
				}
			}
			if (!$findFlag) {
				$obj =& $this->create();
				$obj->set('name', $name);
				$this->insert($obj, true);
			}
		}
		foreach ($DBthemes as $theme) {
			if (!in_array($theme->get('name'), $diskThemeNames)) {
				$this->delete($theme, true);
			}
		}
	}
}
?>
