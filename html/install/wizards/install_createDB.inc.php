<?php
    include_once '../mainfile.php';
    include_once './class/dbmanager.php';
    $dbm = new db_manager;
    if(! $dbm->createDB()){
        $wizard->setContent('<p>'._INSTALL_L31.'</p>');
        $wizard->setNext(array('checkDB', _INSTALL_L104));
        $wizard->setBack(array('start', _INSTALL_L103));
    } else {
        $wizard->setContent('<p>'.sprintf(_INSTALL_L43, XOOPS_DB_NAME).'</p>');
    }
    $wizard->render();
?>
