<?php
    include './language/'.$language.'/finish.php'; 
    $wizard->assign('finish', $content);
    $wizard->render('install_finish.tpl.php');
?>
