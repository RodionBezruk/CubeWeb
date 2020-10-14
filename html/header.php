<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
$root=&XCube_Root::getSingleton();
if(!is_object($root->mController)) exit();
$root->mController->executeHeader();
?>
