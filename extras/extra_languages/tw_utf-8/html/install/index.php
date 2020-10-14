<?php
include_once './passwd.php';
if(INSTALL_USER != '' || INSTALL_PASSWD != ''){
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="XOOPS Installer"');
        header('HTTP/1.0 401 Unauthorized');
        echo '您沒有權限進行 XOOPS Cube 安裝。';
        exit;
    } else {
        if(INSTALL_USER != '' && $_SERVER['PHP_AUTH_USER'] != INSTALL_USER){
            header('HTTP/1.0 401 Unauthorized');
            echo '您沒有權限進行 XOOPS Cube 安裝。';
            exit;
        }
        if(INSTALL_PASSWD != $_SERVER['PHP_AUTH_PW']){
            header('HTTP/1.0 401 Unauthorized');
            echo '您沒有權限進行 XOOPS Cube 安裝。';
            exit;
        }
    }
}
include_once './class/textsanitizer.php';
$myts =& TextSanitizer::getInstance();
if ( isset($_POST) ) {
    foreach ($_POST as $k=>$v) {
        $$k = $myts->stripSlashesGPC($v);
    }
}
include_once './include/functions.php';
$language = getLanguage();
include_once './language/'.$language.'/install.php';
define('_OKIMG', '<img src="img/yes.gif" width="6" height="12" border="0" alt="OK" /> ');
define('_NGIMG', '<img src="img/no.gif" width="6" height="12" border="0" alt="NG" /> ');
include_once './class/simplewizard.php';
$wizard = new SimpleWizard;
$wizard->setBaseTemplate('./install_tpl.php');
$wizard->setTemplatePath('./templates');
$wizardSeq = new SimpleWizardSequence;
$wizardSeq->add('langselect',  _INSTALL_L0,   'start',      _INSTALL_L80);
$wizardSeq->add('start',       _INSTALL_L0,   'modcheck',   _INSTALL_L81);
$wizardSeq->add('modcheck',    _INSTALL_L82,  'dbform',     _INSTALL_L89);
$wizardSeq->add('dbform',      _INSTALL_L90,  'dbconfirm',  _INSTALL_L91);
$wizardSeq->add('dbconfirm',   _INSTALL_L53,  'dbsave',     _INSTALL_L92,  '',      _INSTALL_L93);
$wizardSeq->add('dbsave',      _INSTALL_L92,  'mainfile',   _INSTALL_L94);
$wizardSeq->add('mainfile',    _INSTALL_L94,  'initial',    _INSTALL_L102, 'start', _INSTALL_L103, true);
$wizardSeq->add('initial',     _INSTALL_L102, 'checkDB',    _INSTALL_L104, 'start', _INSTALL_L103, true);
$wizardSeq->add('checkDB',     _INSTALL_L104, 'createDB',   _INSTALL_L105, 'start', _INSTALL_L103, true);
$wizardSeq->add('createDB',    _INSTALL_L105, 'checkDB',    _INSTALL_L104);
$wizardSeq->add('createTables',_INSTALL_L40,  'siteInit',   _INSTALL_L112);
$wizardSeq->add('siteInit',    _INSTALL_L112, 'insertData', _INSTALL_L116);
$wizardSeq->add('insertData',  _INSTALL_L116, 'finish',     _INSTALL_L117);
$wizardSeq->add('finish',      _INSTALL_L32,  'nextStep',   _INSTALL_L210);
if (file_exists('./custom/custom.inc.php')) {
    include './custom/custom.inc.php';
}
$xoopsOption['nocommon'] = true;
define('XOOPS_INSTALL', 1);
if(!empty($_POST['op'])) {
    $op = $_POST['op'];
} elseif(!empty($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = 'langselect';
}
$wizard->setOp($op);
$op=basename($op);
$fname = './wizards/install_'.$op.'.inc.php';
$custom_fname = './custom/install_'.$op.'.inc.php';
if (file_exists($fname)) {
	include $fname;
} else if(file_exists($custom_fname)) {
	include $custom_fname;
} else {
    $wizard->render();
}
?>