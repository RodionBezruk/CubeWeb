<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
function xoops_session_regenerate()
{
    $root =& XCube_Root::getSingleton();
    $root->mSession->regenerate();
}
?>
