<?php
if (!defined('XOOPS_ROOT_PATH'))  exit();
if (defined('XOOPS_FOOTER_INCLUDED')) exit();
$root=&XCube_Root::getSingleton();
if (!is_object($root->mController)) exit();
define('XOOPS_FOOTER_INCLUDED',1);
$xoopsLogger=&$root->mController->getLogger();
$xoopsLogger->stopTime();
require_once XOOPS_ROOT_PATH.'/include/notification_select.php';
$root->mController->executeView();
?>
