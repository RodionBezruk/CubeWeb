<?php
    include './language/'.$language.'/welcome.php'; 
    $wizard->assign('welcome', $content);
    $wizard->render('install_start.tpl.php');
?>
