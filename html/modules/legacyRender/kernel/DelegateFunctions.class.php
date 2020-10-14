<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_Theme.class.php";
class LegacyRender_DelegateFunctions
{
	function getInstalledThemes(&$results)
	{
		if ($handler = opendir(XOOPS_THEME_PATH)) {
			while (($dirname = readdir($handler)) !== false) {
				if ($dirname == "." || $dirname == "..") {
					continue;
				}
				$themeDir = XOOPS_THEME_PATH . "/" . $dirname;
				if (is_dir($themeDir)) {
					$theme =& new XCube_Theme();
					$theme->mDirname = $dirname;
					if ($theme->loadManifesto($themeDir . "/manifesto.ini.php")) {
						if ($theme->mRenderSystemName == 'Legacy_RenderSystem') {
							$results[] =& $theme;
						}
					}
					else {
						if (file_exists($themeDir . "/theme.html")) {
							$theme->mName = $dirname;
							$theme->mRenderSystemName = 'Legacy_RenderSystem';
							$theme->mFormat = "XOOPS2 Legacy Style";
							$results[] =& $theme;
						}
					}
					unset($theme);
				}
			}
			closedir($handler);
		}
	}
}
?>
