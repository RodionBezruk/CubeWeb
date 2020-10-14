<?php
    if (!defined('_INSTALL_L128')) {
        define('_INSTALL_L128', 'Choose language to be used for the installation process');
    }
    $langarr = getDirList('./language/');
    foreach ($langarr as $lang) {
        $wizard->addArray('languages', $lang);
        if (strtolower($lang) == $language) {
            $wizard->addArray('selected','selected="selected"');
        } else {
            $wizard->addArray('selected','');
        }
    }
    $wizard->render('install_langselect.tpl.php');
?>
