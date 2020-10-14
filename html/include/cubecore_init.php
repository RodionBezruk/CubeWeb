<?php
if (!defined("XOOPS_MAINFILE_INCLUDED")) exit();
define('XOOPS_CUBE_LEGACY', true);
require_once XOOPS_ROOT_PATH . "/core/XCube_Root.class.php";
require_once XOOPS_ROOT_PATH . "/core/XCube_Controller.class.php";
define("XCUBE_SITE_SETTING_FILE", XOOPS_ROOT_PATH . "/settings/site_default.ini.php");
define("XCUBE_SITE_CUSTOM_FILE", XOOPS_ROOT_PATH . "/settings/site_custom.ini.php");
$root=&XCube_Root::getSingleton();
$root->loadSiteConfig(XCUBE_SITE_SETTING_FILE, XCUBE_SITE_CUSTOM_FILE);
$root->setupController();
?>
