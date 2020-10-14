<?php
if ( !defined("XOOPS_MAINFILE_INCLUDED") ) {
    define("XOOPS_MAINFILE_INCLUDED",1);
    define('XOOPS_ROOT_PATH', '');
    define('XOOPS_TRUST_PATH', '');
    define('XOOPS_URL', 'http:
    define('XOOPS_DB_TYPE', 'mysql');
    define('XOOPS_DB_PREFIX', '');
    define('XOOPS_SALT', '');
    define('XOOPS_DB_HOST', 'localhost');
    define('XOOPS_DB_USER', '');
    define('XOOPS_DB_PASS', '');
    define('XOOPS_DB_NAME', '');
    define('XOOPS_DB_PCONNECT', 0);
    define("XOOPS_GROUP_ADMIN", "1");
    define("XOOPS_GROUP_USERS", "2");
    define("XOOPS_GROUP_ANONYMOUS", "3");
    if (!defined('_LEGACY_PREVENT_LOAD_CORE_') && XOOPS_ROOT_PATH != '') {
        include_once XOOPS_ROOT_PATH.'/include/cubecore_init.php';
        if (!isset($xoopsOption['nocommon']) && !defined('_LEGACY_PREVENT_EXEC_COMMON_')) {
            include XOOPS_ROOT_PATH.'/include/common.php';
        }
    }
}
?>
