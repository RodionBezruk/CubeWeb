<?php
include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
$form = new XoopsThemeForm(_MD_EDITTEMPLATE, 'template_form', 'admin.php');
$form->addElement(new XoopsFormLabel(_MD_FILENAME, $tform['tpl_file']));
$form->addElement(new XoopsFormLabel(_MD_FILEDESC, $tform['tpl_desc']));
$form->addElement(new XoopsFormLabel(_MD_LASTMOD, formatTimestamp($tform['tpl_lastmodified'], 'l')));
$form->addElement(new XoopsFormTextArea(_MD_FILEHTML, 'html', $tform['tpl_source'], 25, 70));
$form->addElement(new XoopsFormHidden('id', $tform['tpl_id']));
$form->addElement(new XoopsFormHidden('op', 'edittpl_go'));
$form->addElement(new XoopsFormToken(XoopsMultiTokenHandler::quickCreate('tplform')));
$form->addElement(new XoopsFormHidden('redirect', 'edittpl'));
$form->addElement(new XoopsFormHidden('fct', 'tplsets'));
$form->addElement(new XoopsFormHidden('moddir', $tform['tpl_module']));
if ($tform['tpl_tplset'] != 'default') {
    $button_tray = new XoopsFormElementTray('');
    $button_tray->addElement(new XoopsFormButton('', 'previewtpl', _PREVIEW, 'submit'));
    $button_tray->addElement(new XoopsFormButton('', 'submittpl', _SUBMIT, 'submit'));
    $form->addElement($button_tray);
} else {
    $form->addElement(new XoopsFormButton('', 'previewtpl', _MD_VIEW, 'submit'));
}
?>
