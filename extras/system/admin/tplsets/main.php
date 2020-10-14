<?php
if ( !is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid()) ) {
    exit("Access Denied");
} else {
    $myts =& MyTextsanitizer::getInstance();
    $op = 'list';
    if (isset($_GET['op'])) {
        $op = trim($_GET['op']);
        $id = $moddir = $file = $type = $tplset = null;
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        }
        if (isset($_GET['moddir'])) {
            $moddir = trim($_GET['moddir']);
        }
        if (isset($_GET['file'])) {
            $file = trim($_GET['file']);
        }
        if (isset($_GET['type'])) {
            $type = trim($_GET['type']);
        }
        if (isset($_GET['tplset'])) {
            $tplset = $myts->stripslashesGPC(trim($_GET['tplset']));
            $tplset4disp = htmlspecialchars($tplset, ENT_QUOTES);
            $tplset4url = urlencode($tplset);
        }
    } elseif (!empty($_POST['op'])) {
        $op = $_POST['op'];
    }
    if ($op == 'edittpl_go') {
        if (isset($_POST['previewtpl'])) {
            $op = 'previewtpl';
        }
    }
    switch ($op) {
    case 'list':
        $tplset_handler =& xoops_gethandler('tplset');
        $tplsets =& $tplset_handler->getObjects();
        xoops_cp_header();
        echo '<h4 style="text-align:left">'._MD_TPLMAIN.'</h4>';
        $installed = array();
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $installed_mods = $tpltpl_handler->getModuleTplCount('default');
        $tcount = count($tplsets);
        echo '<table width="100%" cellspacing="1" class="outer"><tr align="center"><th width="25%">'._MD_THMSETNAME.'</th><th>'._MD_CREATED.'</th><th>'._MD_TEMPLATES.'</th><th>'._MD_ACTION.'</th><th>&nbsp;</th></tr>';
        $class = 'even';
        for ($i = 0; $i < $tcount; $i++) {
            $tplsetname = $tplsets[$i]->getVar('tplset_name');
            $tplsetname4disp = htmlspecialchars($tplsetname, ENT_QUOTES);
            $tplsetname4url = urlencode($tplsetname);
            $class = ($class == 'even') ? 'odd' : 'even';
            echo '<tr class="'.$class.'" align="center"><td class="head">'.$tplsetname4disp.'<br /><br /><span style="font-weight:normal;">'.$tplsets[$i]->getVar('tplset_desc').'</span></td><td>'.formatTimestamp($tplsets[$i]->getVar('tplset_created'), 's').'</td><td align="left"><ul>';
            $tplstats = $tpltpl_handler->getModuleTplCount($tplsetname);
            if (count($tplstats) > 0) {
                $module_handler =& xoops_gethandler('module');
                echo '<ul>';
                foreach ($tplstats as $moddir => $filecount) {
                    $module =& $module_handler->getByDirname($moddir);
                    if (is_object($module)) {
                        if ($installed_mods[$moddir] > $filecount) {
                            $filecount = '<span style="color:#ff0000;">'.$filecount.'</span>';
                        }
                        echo '<li>'.$module->getVar('name').' [<a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset='.$tplsetname4url.'&amp;moddir='.$moddir.'">'._LIST.'</a> (<b>'.$filecount.'</b>)]</li>';
                    }
                    unset($module);
                }
                $not_installed = array_diff(array_keys($installed_mods), array_keys($tplstats));
            } else {
                $not_installed =& array_keys($installed_mods);
            }
            foreach ($not_installed as $ni) {
                $module =& $module_handler->getByDirname($ni);
                echo '<li>'.$module->getVar('name').' [<a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset='.$tplsetname4url.'&amp;moddir='.$ni.'">'._LIST.'</a> (<span style="color:#ff0000; font-weight: bold;">0</span>)] [<a href="admin.php?fct=tplsets&amp;op=generatemod&amp;tplset='.$tplsetname4url.'&amp;moddir='.$ni.'">'._MD_GENERATE.'</a>]</li>';
            }
            echo '</ul></td><td>';
            echo '[<a href="admin.php?fct=tplsets&amp;op=download&amp;method=tar&amp;tplset='.$tplsetname4url.'">'._MD_DOWNLOAD.'</a>]<br />[<a href="admin.php?fct=tplsets&amp;op=clone&amp;tplset='.$tplsetname4url.'">'._CLONE.'</a>]';
            if ($tplsetname != 'default' && $tplsetname != $xoopsConfig['template_set']) {
                echo '<br />[<a href="admin.php?fct=tplsets&amp;op=delete&amp;tplset='.$tplsetname4url.'">'._DELETE.'</a>]';
            }
            echo '</td>';
            if ($tplsetname === $xoopsConfig['template_set']) {
                echo '<td><img src="'.XOOPS_URL.'/modules/system/images/check.gif" alt="'._MD_DEFAULTTHEME.'" /></td>';
            } else {
                echo '<td>&nbsp;</td>';
            }
            echo '</tr>';
        }
        echo '</table><br />';
        include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
        $form = new XoopsThemeForm(_MD_UPLOADTAR, 'tplupload_form', 'admin.php');
        $form->setExtra('enctype="multipart/form-data"');
        $form->addElement(new XoopsFormToken(XoopsSingleTokenHandler::quickCreate('tplsets_uploadtar')));
        $form->addElement(new XoopsFormFile(_MD_CHOOSETAR.'<br /><span style="color:#ff0000;">'._MD_ONLYTAR.'</span>', 'tpl_upload', 1000000));
        $form->addElement(new XoopsFormText(_MD_NTHEMENAME.'<br /><span style="font-weight:normal;">'._MD_ENTERTH.'</span>', 'tplset_name', 20, 50));
        $form->addElement(new XoopsFormHidden('op', 'uploadtar_go'));
        $form->addElement(new XoopsFormHidden('fct', 'tplsets'));
        $form->addElement(new XoopsFormButton('', 'upload_button', _MD_UPLOAD, 'submit'));
        $form->display();
        xoops_cp_footer();
        break;
    case 'listtpl':
         if ($tplset == '') {
            redirect_header('admin.php?fct=tplsets',1);
        }
        if ($moddir == '') {
            redirect_header('admin.php?fct=tplsets',1);
        }
        xoops_cp_header();
        $module_handler =& xoops_gethandler('module');
        $module =& $module_handler->getByDirname($moddir);
        $modname = $module->getVar('name');
        echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'.$tplset4disp.'<br /><br />';
        $token=&XoopsMultiTokenHandler::quickCreate('tplsets_update');
        echo '<h4>'.$modname.'</h4><form action="admin.php" method="post" enctype="multipart/form-data"><table width="100%" class="outer" cellspacing="1"><tr><th width="40%">'._MD_FILENAME.'</th><th>'._MD_LASTMOD.'</th>';
        echo $token->getHtml();
        if ($tplset != 'default') {
            echo '<th>'._MD_LASTIMP.'</th><th colspan="2">'._MD_ACTION.'</th></tr>';
        } else {
            echo '<th>'._MD_ACTION.'</th></tr>';
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $templates =& $tpltpl_handler->find($tplset, 'module', null, $moddir);
        $inst_files = array();
        $tcount = count($templates);
        for ($i = 0; $i < $tcount; $i++) {
            if ($i % 2 == 0) {
                $class = 'even';
            } else {
                $class = 'odd';
            }
            $last_modified = $templates[$i]->getVar('tpl_lastmodified');
            $last_imported = $templates[$i]->getVar('tpl_lastimported');
            $last_imported_f = ($last_imported > 0) ? formatTimestamp($last_imported, 'l') : '';
            echo  '<tr class="'.$class.'"><td class="head">'.$templates[$i]->getVar('tpl_file').'<br /><br /><span style="font-weight:normal;">'.$templates[$i]->getVar('tpl_desc').'</span></td><td>'.formatTimestamp($last_modified, 'l').'</td>';
            $filename = $templates[$i]->getVar('tpl_file');
            if ($tplset != 'default') {
                $physical_file = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$moddir.'/'.$filename;
                if (file_exists($physical_file)) {
                    $mtime = filemtime($physical_file);
                    if ($last_imported < $mtime) {
                        if ($mtime > $last_modified) {
                            $bg = '#ff9999';
                        } elseif($mtime > $last_imported) {
                            $bg = '#99ff99';
                        }
                        echo '<td style="background-color:'.$bg.';">'.$last_imported_f.' [<a href="admin.php?fct=tplsets&amp;tplset='.$tplset4url.'&amp;moddir='.$moddir.'&amp;op=importtpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._MD_IMPORT.'</a>]';
                    } else {
                        echo '<td>'.$last_imported_f;
                    }
                } else {
                    echo '<td>'.$last_imported_f;
                }
                echo '</td><td>[<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._EDIT.'</a>] [<a href="admin.php?fct=tplsets&amp;op=deletetpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._DELETE.'</a>] [<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._MD_DOWNLOAD.'</a>]</td><td align="right"><input type="file" name="'.$filename.'" id="'.$filename.'" /><input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="'.$filename.'" /><input type="hidden" name="old_template['.$filename.']" value="'.$templates[$i]->getVar('tpl_id').'" /></td>';
            } else {
                echo '<td>[<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._MD_VIEW.'</a>] [<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id='.$templates[$i]->getVar('tpl_id').'">'._MD_DOWNLOAD.'</a>]</td>';
            }
            echo '</tr>'."\n";
            $inst_files[] = $filename;
        }
        if ($tplset != 'default') {
            include_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
            $notinst_files = array_diff(XoopsLists::getFileListAsArray(XOOPS_ROOT_PATH.'/modules/'.$moddir.'/templates/'), $inst_files);
            foreach ($notinst_files as $nfile) {
                if ($nfile != 'index.html') {
                    echo  '<tr><td style="background-color:#FFFF99; padding: 5px;">'.$nfile.'</td><td style="background-color:#FFFF99; padding: 5px;">&nbsp;</td><td style="background-color:#FFFF99; padding: 5px;">';
                    $physical_file = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$moddir.'/'.$nfile;
                    if (file_exists($physical_file)) {
                        echo '[<a href="admin.php?fct=tplsets&amp;moddir='.$moddir.'&amp;tplset='.$tplset4url.'&amp;op=importtpl&amp;file='.urlencode($nfile).'">'._MD_IMPORT.'</a>]';
                    } else {
                        echo '&nbsp;';
                    }
                    echo '</td><td style="background-color:#FFFF99; padding: 5px;">[<a href="admin.php?fct=tplsets&amp;moddir='.$moddir.'&amp;tplset='.$tplset4url.'&amp;op=generatetpl&amp;type=module&amp;file='.urlencode($nfile).'">'._MD_GENERATE.'</a>]</td><td style="background-color:#FFFF99; padding: 5px; text-align:right;"><input type="file" name="'.$nfile.'" id="'.$nfile.'" /><input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="'.$nfile.'" /></td></tr>'."\n";
                }
            }
        }
        echo '</table><br /><table width="100%" class="outer" cellspacing="1"><tr><th width="40%">'._MD_FILENAME.'</th><th>'._MD_LASTMOD.'</th>';
        if ($tplset != 'default') {
            echo '<th>'._MD_LASTIMP.'</th><th colspan="2">'._MD_ACTION.'</th></tr>';
        } else {
            echo '<th>'._MD_ACTION.'</th></tr>';
        }
        $btemplates =& $tpltpl_handler->find($tplset, 'block', null, $moddir);
        $binst_files = array();
        $btcount = count($btemplates);
        for ($j = 0; $j < $btcount; $j++) {
            $last_imported = $btemplates[$j]->getVar('tpl_lastimported');
            $last_imported_f = ($last_imported > 0) ? formatTimestamp($last_imported, 'l') : '';
            $last_modified = $btemplates[$j]->getVar('tpl_lastmodified');
            if ($j % 2 == 0) {
                $class = 'even';
            } else {
                $class = 'odd';
            }
            echo  '<tr class="'.$class.'"><td class="head"><span style="font-weight:bold;">'.$btemplates[$j]->getVar('tpl_file').'</span><br /><br /><span style="font-weight:normal;">'.$btemplates[$j]->getVar('tpl_desc').'</span></td><td>'.formatTimestamp($last_modified, 'l').'</td>';
            $filename = $btemplates[$j]->getVar('tpl_file');
            $physical_file = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$moddir.'/blocks/'.$filename;
            if ($tplset != 'default') {
                if (file_exists($physical_file)) {
                    $mtime = filemtime($physical_file);
                    if ($last_imported < $mtime) {
                        if ($mtime > $last_modified) {
                            $bg = '#ff9999';
                        } elseif($mtime > $last_imported) {
                            $bg = '#99ff99';
                        }
                        echo '<td style="background-color:'.$bg.';">'.$last_imported_f.' [<a href="admin.php?fct=tplsets&amp;tplset='.$tplset4url.'&amp;op=importtpl&amp;moddir='.$moddir.'&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._MD_IMPORT.'</a>]';
                    } else {
                        echo '<td>'.$last_imported_f;
                    }
                } else {
                    echo '<td>'.$last_imported_f;
                }
                echo '</td><td>[<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._EDIT.'</a>] [<a href="admin.php?fct=tplsets&amp;op=deletetpl&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._DELETE.'</a>] [<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._MD_DOWNLOAD.'</a>]</td><td align="right"><input type="file" name="'.$filename.'" id="'.$filename.'" /><input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="'.$filename.'" /><input type="hidden" name="old_template['.$filename.']" value="'.$btemplates[$j]->getVar('tpl_id').'" /></td>';
            } else {
                echo '<td>[<a href="admin.php?fct=tplsets&amp;op=edittpl&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._MD_VIEW.'</a>] [<a href="admin.php?fct=tplsets&amp;op=downloadtpl&amp;id='.$btemplates[$j]->getVar('tpl_id').'">'._MD_DOWNLOAD.'</a>]</td>';
            }
            echo '</tr>'."\n";
            $binst_files[] = $filename;
        }
        if ($tplset != 'default') {
            include_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
            $bnotinst_files = array_diff(XoopsLists::getFileListAsArray(XOOPS_ROOT_PATH.'/modules/'.$moddir.'/templates/blocks/'), $binst_files);
            foreach ($bnotinst_files as $nfile) {
                if ($nfile != 'index.html') {
                    echo  '<tr style="background-color:#FFFF99;"><td style="background-color:#FFFF99; padding: 5px;">'.$nfile.'</td><td style="background-color:#FFFF99; padding: 5px;">&nbsp;</td><td style="background-color:#FFFF99; padding: 5px;">';
                    $physical_file = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$moddir.'/blocks/'.$nfile;
                    if (file_exists($physical_file)) {
                        echo '[<a href="admin.php?fct=tplsets&amp;moddir='.$moddir.'&amp;tplset='.$tplset4url.'&amp;op=importtpl&amp;file='.urlencode($nfile).'">'._MD_IMPORT.'</a>]';
                    } else {
                        echo '&nbsp;';
                    }
                    echo '</td><td style="background-color:#FFFF99; padding: 5px;">[<a href="admin.php?fct=tplsets&amp;moddir='.$moddir.'&amp;tplset='.$tplset4url.'&amp;op=generatetpl&amp;type=block&amp;file='.urlencode($nfile).'">'._MD_GENERATE.'</a>]</td><td style="background-color:#FFFF99; padding: 5px; text-align: right"><input type="file" name="'.$nfile.'" id="'.$nfile.'" /><input type="hidden" name="xoops_upload_file[]" id="xoops_upload_file[]" value="'.$nfile.'" /></td></tr>'."\n";
                }
            }
        }
        echo '</table>';
        if ($tplset != 'default') {
            echo '<div style="text-align: right; margin-top: 5px;"><input type="hidden" name="fct" value="tplsets" /><input type="hidden" name="op" value="update" />';
            echo '<input type="hidden" name="moddir" value="'.$moddir.'" /><input type="hidden" name="tplset" value="'.$tplset4disp.'" /><input type="submit" value="'._MD_UPLOAD.'" /></div></form>';
        }
        xoops_cp_footer();
        break;
    case 'edittpl':
        if ($id <= 0) {
            redirect_header('admin.php?fct=tplsets', 1);
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->get($id, true);
        if (is_object($tplfile)) {
            $tplset = $tplfile->getVar('tpl_tplset');
	        $tplset4disp = htmlspecialchars($tplset, ENT_QUOTES);
	        $tplset4url = urlencode($tplset);
            $tform = array('tpl_tplset' => $tplset, 'tpl_id' => $id, 'tpl_file' => $tplfile->getVar('tpl_file'), 'tpl_desc' => $tplfile->getVar('tpl_desc'), 'tpl_lastmodified' => $tplfile->getVar('tpl_lastmodified'), 'tpl_source' => $tplfile->getVar('tpl_source', 'E'), 'tpl_module' => $tplfile->getVar('tpl_module'));
            include_once XOOPS_ROOT_PATH.'/modules/system/admin/tplsets/tplform.php';
            xoops_cp_header();
            echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$tplfile->getVar('tpl_module').'&amp;tplset='.$tplset4url.'">'.$tplset4disp.'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._MD_EDITTEMPLATE.'<br /><br />';
            $form->display();
            xoops_cp_footer();
            exit();
        } else {
            $err[] = 'Selected template (ID: $id) does not exist';
        }
        xoops_cp_header();
        xoops_error($err);
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'edittpl_go':
        $id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0 || !XoopsMultiTokenHandler::quickValidate('tplform')) {
            redirect_header('admin.php?fct=tplsets');
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->get($id, true);
        $err = array();
        if (!is_object($tplfile)) {
            $err[] = 'Selected template (ID: $id) does not exist';
        } else {
            if ($tplfile->getVar('tpl_tplset') != 'default') {
                $tplfile->setVar('tpl_source', $_POST['html']);
                $tplfile->setVar('tpl_lastmodified', time());
                if (!$tpltpl_handler->insert($tplfile)) {
                    $err[] = 'Could not insert template file to the database.';
                } else {
                    include_once XOOPS_ROOT_PATH.'/class/template.php';
                    $xoopsTpl = new XoopsTpl();
                    if ($xoopsTpl->is_cached('db:'.$tplfile->getVar('tpl_file'))) {
                        if (!$xoopsTpl->clear_cache('db:'.$tplfile->getVar('tpl_file'))) {
                        }
                    }
                    if ($tplfile->getVar('tpl_tplset') === $xoopsConfig['template_set']) {
                        xoops_template_touch($id);
                    }
                }
            } else {
                $err[] = 'Default template files cannot be edited.';
            }
        }
        if (count($err) == 0) {
            if (!empty($_POST['moddir'])) {
                redirect_header('admin.php?fct=tplsets&amp;op=edittpl&amp;id='.$tplfile->getVar('tpl_id'), 2, _MD_AM_DBUPDATED);
            } elseif (isset($_POST['redirect'])) {
                redirect_header('admin.php?fct=tplsets&amp;tplset='.urlencode($tplfile->getVar('tpl_tplset')).'&amp;op='.trim($_POST['redirect']), 2, _MD_AM_DBUPDATED);
            } else {
                redirect_header('admin.php?fct=tplsets', 2, _MD_AM_DBUPDATED);
            }
        }
        xoops_cp_header();
        xoops_error($err);
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'deletetpl':
        xoops_cp_header();
        xoops_confirm(array('id' => $id, 'op' => 'deletetpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREDELTPL, _YES);
        xoops_cp_footer();
        break;
    case 'deletetpl_go':
        $id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0 || !xoops_confirm_validate()) {
            redirect_header('admin.php?fct=tplsets', 1);
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->get($id);
        $err = array();
        if (!is_object($tplfile)) {
            $err[] = 'Selected template (ID: $id) does not exist';
        } else {
            if ($tplfile->getVar('tpl_tplset') != 'default') {
                if (!$tpltpl_handler->delete($tplfile)) {
                    $err[] = 'Could not delete '.$tplfile->getVar('tpl_file').' from the database.';
                } else {
                    if ($tplfile->getVar('tpl_tplset') === $xoopsConfig['template_set']) {
                        $defaulttpl =& $tpltpl_handler->find('default', $tplfile->getVar('tpl_type'), $tplfile->getVar('tpl_refid'), null, $tplfile->getVar('tpl_file'));
                        if (count($defaulttpl) > 0) {
                            include_once XOOPS_ROOT_PATH.'/class/template.php';
                            xoops_template_touch($defaulttpl[0]->getVar('tpl_id'), true);
                        }
                    }
                }
            } else {
                $err[] = 'Default template files cannot be deleted.';
            }
        }
        if (count($err) == 0) {
            redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$tplfile->getVar('tpl_module').'&amp;tplset='.urlencode($tplfile->getVar('tpl_tplset')), 2, _MD_AM_DBUPDATED);
        }
        xoops_cp_header();
        xoops_error($err);
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'delete':
        xoops_cp_header();
        xoops_token_confirm(array('tplset' => $tplset, 'op' => 'delete_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREDELTH, _YES);
        xoops_cp_footer();
        break;
    case 'delete_go':
        if(!xoops_confirm_validate()) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        $msgs = array();
        if ($tplset !== '' && $tplset != 'default' && $tplset !== $xoopsConfig['template_set']) {
            $tpltpl_handler =& xoops_gethandler('tplfile');
            $templates =& $tpltpl_handler->getObjects(new Criteria('tpl_tplset', addslashes($tplset)));
            $tcount = count($templates);
            if ($tcount > 0) {
                $msgs[] = 'Deleting template files...';
                for ($i = 0; $i < $tcount; $i++) {
                    if (!$tpltpl_handler->delete($templates[$i])) {
                        $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not delete template <b>'.$templates[$i]->getVar('tpl_file').'</b>. ID: <b>'.$templates[$i]->getVar('tpl_id').'</b></span>';
                    } else {
                        $msgs[] = '&nbsp;&nbsp;Template <b>'.$templates[$i]->getVar('tpl_file').'</b> deleted. ID: <b>'.$templates[$i]->getVar('tpl_id').'</b>';
                    }
                }
            }
            $tplset_handler =& xoops_gethandler('tplset');
            $tplsets =& $tplset_handler->getObjects(new Criteria('tplset_name', addslashes($tplset)));
            if (count($tplsets) > 0 && is_object($tplsets[0])) {
                $msgs[] = 'Deleting template set data...';
                if (!$tplset_handler->delete($tplsets[0])) {
                    $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Template set '.htmlspecialchars($tplset, ENT_QUOTES).' could not be deleted.</span>';
                } else {
                    $msgs[] = '&nbsp;&nbsp;Template set data <b>'.htmlspecialchars($tplset, ENT_QUOTES).'</b> removed from the database.';
                }
            }
        } else {
            $msgs[] = '<span style="color:#ff0000;">ERROR: Default template files cannot be deleted</span>';
        }
        xoops_cp_header();
        foreach ($msgs as $msg) {
            echo '<code>'.$msg.'</code><br />';
        }
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'clone':
        include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
        $form = new XoopsThemeForm(_MD_CLONETHEME, 'template_form', 'admin.php');
        $form->addElement(new XoopsFormToken(XoopsSingleTokenHandler::quickCreate('tplsets_clone')));
        $form->addElement(new XoopsFormLabel(_MD_THEMENAME, $tplset4disp));
        $form->addElement(new XoopsFormText(_MD_NEWNAME, 'newtheme', 30, 50), true);
        $form->addElement(new XoopsFormHidden('tplset', $tplset4disp));
        $form->addElement(new XoopsFormHidden('op', 'clone_go'));
        $form->addElement(new XoopsFormHidden('fct', 'tplsets'));
        $form->addElement(new XoopsFormButton('', 'tpl_button', _SUBMIT, 'submit'));
        xoops_cp_header();
        echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._MD_CLONETHEME.'<br /><br />';
        $form->display();
        xoops_cp_footer();
        break;
    case 'clone_go':
        $msgs = array();
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        $newtheme = isset($_POST['newtheme']) ? trim($_POST['newtheme']) : '';
        if ($tplset === '' || $newtheme === '') {
            redirect_header('admin.php?fct=tplsets',3,'Invalid Template Set Name');
        }
        if (preg_match('/['.preg_quote('\/:*?"<>|','/').']/', $newtheme)) {
            redirect_header('admin.php?fct=tplsets',3,'Invalid Template Set Name');
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        xoops_cp_header();
        if(!XoopsSingleTokenHandler::quickValidate('tplsets_clone')) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        } elseif ($tplset === $newtheme) {
            xoops_error('Template set name must be a different name.');
        } elseif ($tpltpl_handler->getCount(new Criteria('tpl_tplset', addslashes($newtheme))) > 0) {
            xoops_error('Template set <b>'.$newtheme.'</b> already exists.');
        } else {
            $tplset_handler =& xoops_gethandler('tplset');
            $tplsetobj =& $tplset_handler->create();
            $tplsetobj->setVar('tplset_name', $newtheme);
            $tplsetobj->setVar('tplset_created', time());
            if (!$tplset_handler->insert($tplsetobj)) {
                $msgs[] = '<span style="color:#ff0000;">ERROR: Could not create template set <b>'.htmlspecialchars($newtheme, ENT_QUOTES).'</b>.</span><br />';
            } else {
                $tplsetid = $tplsetobj->getVar('tplset_id');
                $templates =& $tpltpl_handler->getObjects(new Criteria('tpl_tplset', addslashes($tplset)), true);
                $tcount = count($templates);
                if ($tcount > 0) {
                    $msgs[] = 'Copying template files...';
                    for ($i = 0; $i < $tcount; $i++) {
                        $newtpl =& $templates[$i]->xoopsClone();
                        $newtpl->setVar('tpl_tplset', $newtheme);
                        $newtpl->setVar('tpl_id', 0);
                        $newtpl->setVar('tpl_lastimported', 0);
                        $newtpl->setVar('tpl_lastmodified', time());
                        if (!$tpltpl_handler->insert($newtpl)) {
                            $msgs[] = '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Failed copying template <b>'.$templates[$i]->getVar('tpl_file').'</b>. ID: <b>'.$templates[$i]->getVar('tpl_id').'</b></span>';
                        } else {
                            $msgs[] = '&nbsp;&nbsp;Template <b>'.$templates[$i]->getVar('tpl_file').'</b> copied. ID: <b>'.$newtpl->getVar('tpl_id').'</b>';
                        }
                        unset($newtpl);
                    }
                    $msgs[] = 'Template set <b>'.htmlspecialchars($newtheme, ENT_QUOTES).'</b> created. (ID: <b>'.$tplsetid.'</b>)<br />';
                } else {
                    $msgs[] = '<span style="color:#ff0000;">ERROR: Template files for '.$theme.' do not exist</span>';
                }
            }
        }
        foreach ($msgs as $msg) {
            echo '<code>'.$msg.'</code><br />';
        }
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'viewdefault':
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->get($id);
        $default =& $tpltpl_handler->find('default', $tplfile->getVar('tpl_type'), $tplfile->getVar('tpl_refid'), null, $tplfile->getVar('tpl_file'));
        echo "<!DOCTYPE html PUBLIC '-
        echo '<html xmlns="http:
        <head>
        <meta http-equiv="content-type" content="text/html; charset='._CHARSET.'" />
        <meta http-equiv="content-language" content="'._LANGCODE.'" />
        <title>'.htmlspecialchars($xoopsConfig['sitename']).' Administration</title>
        <link rel="stylesheet" type="text/css" media="all" href="'.XOOPS_URL.'/xoops.css" />
            <link rel="stylesheet" type="text/css" media="all" href="'.XOOPS_URL.'/modules/system/style.css" />
        </head><body>';
        if (is_object($default[0])) {
            $tpltpl_handler->loadSource($default[0]);
            $last_modified = $default[0]->getVar('tpl_lastmodified');
            $last_imported = $default[0]->getVar('tpl_lastimported');
            if ($default[0]->getVar('tpl_type') == 'block') {
                $path = XOOPS_ROOT_PATH.'/modules/'.$default[0]->getVar('tpl_module').'/blocks/'.$default[0]->getVar('tpl_file');
            } else {
                $path = XOOPS_ROOT_PATH.'/modules/'.$default[0]->getVar('tpl_module').'/'.$default[0]->getVar('tpl_file');
            }
            $colorchange = '';
            if (!file_exists($path)) {
                $filemodified_date = _MD_NOFILE;
                $lastimported_date = _MD_NOFILE;
            } else {
                $tpl_modified = filemtime($path);
                $filemodified_date = formatTimestamp($tpl_modified, 'l');
                if ($tpl_modified > $last_imported) {
                    $colorchange = ' bgcolor="#ffCC99"';
                }
                $lastimported_date = formatTimestamp($last_imported, 'l');
            }
            include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MD_VIEWDEFAULT, 'template_form', 'admin.php');
            $form->addElement(new XoopsFormTextArea(_MD_FILEHTML, 'html', $default[0]->getVar('tpl_source'), 25));
            $form->display();
        } else {
            echo 'Selected file does not exist';
        }
        echo '<div style="text-align:center;">[<a href="#" onclick="javascript:window.close();">'._CLOSE.'</a>]</div></body></html>';
        break;
    case 'downloadtpl':
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tpl =& $tpltpl_handler->get($id, true);
        if (is_object($tpl)) {
            $output = $tpl->getVar('tpl_source');
            strlen($output);
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-Type: application/force-download');
            if (preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT'])) {
                header('Content-Disposition: filename='.$tpl->getVar('tpl_file'));
            } else {
                header('Content-Disposition: attachment; filename='.$tpl->getVar('tpl_file'));
            }
            header('Content-length: '.strlen($output));
            echo $output;
        }
        break;
    case 'uploadtpl':
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tpl =& $tpltpl_handler->get($id);
        xoops_cp_header();
        echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$tpl->getVar('tpl_module').'&amp;tplset='.urlencode($tpl->getVar('tpl_tplset')).'">'.$tpl->getVar('tpl_tplset').'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._MD_UPLOAD.'<br /><br />';
        if (is_object($tpl)) {
            include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MD_UPLOAD, 'tplupload_form', 'admin.php');
            $form->setExtra('enctype="multipart/form-data"');
            $form->addElement(new XoopsFormLabel(_MD_FILENAME, $tpl->getVar('tpl_file').' ('.$tpl->getVar('tpl_tplset').')'));
            $form->addElement(new XoopsFormFile(_MD_CHOOSEFILE.'<br /><span style="color:#ff0000;">'._MD_UPWILLREPLACE.'</span>', 'tpl_upload', 200000), true);
            $form->addElement(new XoopsFormHidden('tpl_id', $id));
            $form->addElement(new XoopsFormHidden('op', 'uploadtpl_go'));
            $form->addElement(new XoopsFormHidden('fct', 'tplsets'));
            $form->addElement(new XoopsFormButton('', 'upload_button', _MD_UPLOAD, 'submit'));
            $form->display();
            xoops_cp_footer();
            exit();
        } else {
            echo 'Selected template does not exist';
        }
        xoops_cp_footer();
        break;
    case 'uploadtpl_go':
        $tpl_id = !empty($_POST['id']) ? $_POST['id'] : 0;
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tpl =& $tpltpl_handler->get($tpl_id);
        if (is_object($tpl)) {
            include_once XOOPS_ROOT_PATH.'/class/uploader.php';
            $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('text/html', 'application/x-cdf', 'text/plain'), 200000);
            $uploader->setAllowedExtensions(array('html', 'htm'));
            $uploader->setPrefix('tmp');
            if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
                if (!$uploader->upload()) {
                    $err = $uploader->getErrors();
                } else {
                    $tpl->setVar('tpl_lastmodified', time());
                    $fp = @fopen($uploader->getSavedDestination(), 'r');
                    $fsource = @fread($fp, filesize($uploader->getSavedDestination()));
                    @fclose($fp);
                    $tpl->setVar('tpl_source', $fsource, true);
                    @unlink($uploader->getSavedDestination());
                    if (!$tpltpl_handler->insert($tpl)) {
                        $err = 'Failed inserting data to database';
                    } else {
                        if ($tpl->getVar('tpl_tplset') === $xoopsConfig['template_set']) {
                            include_once XOOPS_ROOT_PATH.'/class/template.php';
                            xoops_template_touch($tpl_id, true);
                        }
                    }
                }
            } else {
                $err = 'Failed uploading file';
            }
            if (isset($err)) {
                xoops_cp_header(false);
                xoops_error($err);
                xoops_cp_footer();
                exit();
            }
            redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$tpl->getVar('tpl_module').'&amp;tplset='.urlencode($tpl->getVar('tpl_tplset')), 2, _MD_AM_DBUPDATED);
        }
        break;
    case 'uploadtpl2':
        xoops_cp_header();
        $moddir = htmlspecialchars($moddir);
        echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$moddir.'&amp;tplset='.$tplset4url.'">'.$tplset4disp.'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._MD_UPLOAD.'<br /><br />';
        include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
        $form = new XoopsThemeForm(_MD_UPLOAD, 'tplupload_form', 'admin.php');
        $form->setExtra('enctype="multipart/form-data"');
        $form->addElement(new XoopsFormLabel(_MD_FILENAME, $file));
        $form->addElement(new XoopsFormFile(_MD_CHOOSEFILE.'<br /><span style="color:#ff0000;">'._MD_UPWILLREPLACE.'</span>', 'tpl_upload', 200000), true);
        $form->addElement(new XoopsFormHidden('moddir', $moddir));
        $form->addElement(new XoopsFormHidden('tplset', $tplset4disp));
        $form->addElement(new XoopsFormHidden('file', $file));
        $form->addElement(new XoopsFormHidden('type', $type));
        $form->addElement(new XoopsFormHidden('op', 'uploadtpl2_go'));
        $form->addElement(new XoopsFormHidden('fct', 'tplsets'));
        $form->addElement(new XoopsFormButton('', 'ploadtarupload_button', _MD_UPLOAD, 'submit'));
        $form->display();
        xoops_cp_footer();
        break;
    case 'uploadtpl2_go':
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        include_once XOOPS_ROOT_PATH.'/class/uploader.php';
        $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('text/html', 'application/x-cdf', 'text/plain'), 200000);
        $uploader->setAllowedExtensions(array('html', 'htm'));
        $uploader->setPrefix('tmp');
        if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
            if (!$uploader->upload()) {
                $err = $uploader->getErrors();
            } else {        
                $tpltpl_handler =& xoops_gethandler('tplfile');
                $tplfile =& $tpltpl_handler->find('default', $_POST['type'], null, $_POST['moddir'], $_POST['file']);
                if (is_array($tplfile)) {
                    $tpl =& $tplfile[0]->xoopsClone();
                    $tpl->setVar('tpl_id', 0);
                    $tpl->setVar('tpl_tplset', $tplset);
                    $tpl->setVar('tpl_lastmodified', time());
                    $fp = @fopen($uploader->getSavedDestination(), 'r');
                    $fsource = @fread($fp, filesize($uploader->getSavedDestination()));
                    @fclose($fp);
                    $tpl->setVar('tpl_source', $fsource, true);
                    @unlink($uploader->getSavedDestination());
                    if (!$tpltpl_handler->insert($tpl)) {
                        $err = 'Failed inserting data to database';
                    } else {
                        if ($tplset === $xoopsConfig['template_set']) {
                            include_once XOOPS_ROOT_PATH.'/class/template.php';
                            xoops_template_touch($tpl->getVar('tpl_id'), true);
                        }
                    }
                } else {
                    $err = 'This template file does not need to be installed (PHP files using this template file does not exist)';
                }
            }
        } else {
            $err = 'Failed uploading file';
        }
        if (isset($err)) {
            xoops_cp_header(false);
            xoops_error($err);
            xoops_cp_footer();
            exit();
        }
        redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$_POST['moddir'].'&amp;tplset='.urlencode($tplset), 2, _MD_AM_DBUPDATED);
        break;
    case 'download':
        if (isset($tplset)) {
            if (false != extension_loaded('zlib')) {
                if (isset($_GET['method']) && $_GET['method'] == 'tar') {
                    if (@function_exists('gzencode')) {
                        require_once(XOOPS_ROOT_PATH.'/class/tardownloader.php');
                        $downloader = new XoopsTarDownloader();
                    }
                } else {
                    if (@function_exists('gzcompress')) {
                        require_once(XOOPS_ROOT_PATH.'/class/zipdownloader.php');
                        $downloader = new XoopsZipDownloader();
                    }
                }
                $tplset_handler =& xoops_gethandler('tplset');
                $tplsetobj =& $tplset_handler->getByName($tplset);
                $xml = "<"."?xml version=\"1.0\"?".">\r\n<tplset>\r\n  <name>".$tplset."</name>\r\n  <dateCreated>".$tplsetobj->getVar('tplset_created')."</dateCreated>\r\n  <credits>\r\n".$tplsetobj->getVar('tplset_credits')."\r\n  </credits>\r\n  <generator>".XOOPS_VERSION."</generator>\r\n  <templates>";
                $tpltpl_handler =& xoops_gethandler('tplfile');
                $files =& $tpltpl_handler->getObjects(new Criteria('tpl_tplset', addslashes($tplset)), true);
                $fcount = count($files);
                if ($fcount > 0) {
                    for ($i = 0; $i < $fcount; $i++) {
                        if ($files[$i]->getVar('tpl_type') == 'block') {
                            $path = $tplset.'/templates/'.$files[$i]->getVar('tpl_module').'/blocks/'.$files[$i]->getVar('tpl_file');
                            $xml .= "\r\n    <template name=\"".$files[$i]->getVar('tpl_file')."\">\r\n      <module>".$files[$i]->getVar('tpl_module')."</module>\r\n      <type>block</type>\r\n      <lastModified>".$files[$i]->getVar('tpl_lastmodified')."</lastModified>\r\n    </template>";
                        } elseif ($files[$i]->getVar('tpl_type') == 'module') {
                            $path = $tplset.'/templates/'.$files[$i]->getVar('tpl_module').'/'.$files[$i]->getVar('tpl_file');
                            $xml .= "\r\n    <template name=\"".$files[$i]->getVar('tpl_file')."\">\r\n      <module>".$files[$i]->getVar('tpl_module')."</module>\r\n      <type>module</type>\r\n      <lastModified>".$files[$i]->getVar('tpl_lastmodified')."</lastModified>\r\n    </template>";
                        }
                        $downloader->addFileData($files[$i]->getVar('tpl_source'), $path, $files[$i]->getVar('tpl_lastmodified'));
                    }
                    $xml .= "\r\n  </templates>";
                }
                $xml .= "\r\n</tplset>";
                $downloader->addFileData($xml, $tplset.'/tplset.xml', time());
                echo $downloader->download($tplset, true);
            } else {
                xoops_cp_header();
                xoops_error(_MD_NOZLIB);
                xoops_cp_footer();
            }
        }
        break;
    case 'generatetpl':
        xoops_cp_header();
        xoops_token_confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'file' => $file, 'type' => $type, 'op' => 'generatetpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_PLZGENERATE, _MD_GENERATE);
        xoops_cp_footer();
        break;
    case 'generatetpl_go':
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        if(!xoops_confirm_validate()) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->find('default', $_POST['type'], null, $_POST['moddir'], $_POST['file'], true);
        if (count($tplfile) > 0) {
            $newtpl =& $tplfile[0]->xoopsClone();
            $newtpl->setVar('tpl_id', 0);
            $newtpl->setVar('tpl_tplset', $tplset);
            $newtpl->setVar('tpl_lastmodified', time());
            $newtpl->setVar('tpl_lastimported', 0);
            if (!$tpltpl_handler->insert($newtpl)) {
                $err = 'ERROR: Could not insert template <b>'.$tplfile[0]->getVar('tpl_file').'</b> to the database.';
            } else {
                if ($tplset === $xoopsConfig['template_set']) {
                    include_once XOOPS_ROOT_PATH.'/class/template.php';
                    xoops_template_touch($newtpl->getVar('tpl_id'));
                }
            }
        } else {
            $err = 'Selected file does not exist)';
        }
        if (!isset($err)) {
            redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$newtpl->getVar('tpl_module').'&amp;tplset='.urlencode($newtpl->getVar('tpl_tplset')), 2, _MD_AM_DBUPDATED);
        }
        xoops_cp_header();
        xoops_error($err);
        echo '<br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'generatemod':
        xoops_cp_header();
        xoops_token_confirm(array('tplset' => $tplset, 'op' => 'generatemod_go', 'fct' => 'tplsets', 'moddir' => $moddir), 'admin.php', _MD_PLZGENERATE, _MD_GENERATE);
        xoops_cp_footer();
        break;
    case 'generatemod_go':
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        if(!xoops_confirm_validate()) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        $tpltpl_handler =& xoops_gethandler('tplfile');
        xoops_cp_header();
        echo '<code>';
        $tplfiles =& $tpltpl_handler->find('default', 'module', null, $_POST['moddir'], null, true);
        $fcount = count($tplfiles);
        if ($fcount > 0) {
            echo 'Installing module template files for template set '.htmlspecialchars($tplset, ENT_QUOTES).'...<br />';
            for ($i = 0; $i < $fcount; $i++) {
                $newtpl =& $tplfiles[$i]->xoopsClone();
                $newtpl->setVar('tpl_id', 0);
                $newtpl->setVar('tpl_tplset', $tplset);
                $newtpl->setVar('tpl_lastmodified', time());
                $newtpl->setVar('tpl_lastimported', 0);
                if (!$tpltpl_handler->insert($newtpl)) {
                    echo '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not insert template to the database.</span><br />';
                } else {
                    if ($tplset === $xoopsConfig['template_set']) {
                        include_once XOOPS_ROOT_PATH.'/class/template.php';
                        xoops_template_touch($newtpl->getVar('tpl_id'));
                    }
                    echo '&nbsp;&nbsp;Template <b>'.$tplfiles[$i]->getVar('tpl_file').'</b> added to the database.<br />';
                }
            }
            flush();
            unset($newtpl);
        }
        unset($tplfiles);
        $tplfiles =& $tpltpl_handler->find('default', 'block', null, $_POST['moddir'], null, true);
        $fcount = count($tplfiles);
        if ($fcount > 0) {
            echo '&nbsp;&nbsp;Installing block template files...<br />';
            for ($i = 0; $i < $fcount; $i++) {
                $newtpl =& $tplfiles[$i]->xoopsClone();
                $newtpl->setVar('tpl_id', 0);
                $newtpl->setVar('tpl_tplset', $tplset);
                $newtpl->setVar('tpl_lastmodified', time());
                $newtpl->setVar('tpl_lastimported', 0);
                if (!$tpltpl_handler->insert($newtpl)) {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not insert block template <b>'.$tplfiles[$i]->getVar('tpl_file').'</b> to the database.</span><br />';echo $newtpl->getHtmlErrors();
                } else {
                    if ($tplset == $xoopsConfig['template_set']) {
                        include_once XOOPS_ROOT_PATH.'/class/template.php';
                        xoops_template_touch($newtpl->getVar('tpl_id'));
                    }
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;Block template <b>'.$tplfiles[$i]->getVar('tpl_file').'</b> added to the database.<br />';
                }
            }
            flush();
            unset($newtpl);
        }
        echo '<br />Module template files for template set <b>'.htmlspecialchars($tplset, ENT_QUOTES).'</b> generated and installed.<br /></code><br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'uploadtar_go':
        if(!XoopsSingleTokenHandler::quickValidate('tplsets_uploadtar')) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        include_once XOOPS_ROOT_PATH.'/class/uploader.php';
        $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('application/x-gzip', 'application/gzip', 'application/gzip-compressed', 'application/x-gzip-compressed', 'application/x-tar', 'application/x-tar-compressed', 'application/octet-stream'), 1000000);
        $uploader->setAllowedExtensions(array('tar', 'tar.gz', 'tgz', 'gz'));
        $uploader->setPrefix('tmp');
        xoops_cp_header();
        echo '<code>';
        if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
            if (!$uploader->upload()) {
                xoops_error($uploader->getErrors());
            } else {
                include_once XOOPS_ROOT_PATH.'/class/class.tar.php';
                $tar = new tar();
                $tar->openTar($uploader->getSavedDestination());
                @unlink($uploader->getSavedDestination());
                $themefound = false;
                foreach ($tar->files as $id => $info) {
                    $infoarr = explode('/', str_replace("\\", '/', $info['name']));
                    if (!isset($_POST['tplset_name'])) {
                        $tplset_name = trim($infoarr[0]);
                    } else {
                        $tplset_name = trim($_POST['tplset_name']);
                        if ($tplset_name === '') {
                            $tplset_name = trim($infoarr[0]);
                        }
                    }
                    if ($tplset_name !== '') {
                        break;
                    }
                }
                if ($tplset_name === '') {
                    echo '<span style="color:#ff0000;">ERROR: Template file not found</span><br />';
                } elseif  (preg_match('/['.preg_quote('\/:*?"<>|','/').']/', $tplset_name)) {
                    echo '<span style="color:#ff0000;">ERROR: Invalid Template Set Name</span><br />';
                } else {
                    $tplset_handler =& xoops_gethandler('tplset');
                    if ($tplset_handler->getCount(new Criteria('tplset_name', addslashes($tplset_name))) > 0) {
                        echo '<span style="color:#ff0000;">ERROR: Template set <b>'.htmlspecialchars($tplset_name, ENT_QUOTES).'</b> already exists.</span><br />';
                    } else {
                        $tplset =& $tplset_handler->create();
                        $tplset->setVar('tplset_name', $tplset_name);
                        $tplset->setVar('tplset_created', time());
                        if (!$tplset_handler->insert($tplset)) {
                            echo '<span style="color:#ff0000;">ERROR: Could not create template set <b>'.htmlspecialchars($tplset_name, ENT_QUOTES).'</b>.</span><br />';
                        } else {
                            $tplsetid = $tplset->getVar('tplset_id');
                            echo 'Template set <b>'.htmlspecialchars($tplset_name, ENT_QUOTES).'</b> created. (ID: <b>'.$tplsetid.'</b>)</span><br />';
                            $tpltpl_handler = xoops_gethandler('tplfile');
                            $themeimages = array();
                            foreach ($tar->files as $id => $info) {
                                $infoarr = explode('/', str_replace("\\", '/', $info['name']));
                                if (isset($infoarr[3]) && trim($infoarr[3]) == 'blocks') {
                                    $default =& $tpltpl_handler->find('default', 'block', null, trim($infoarr[2]), trim($infoarr[4]));
                                } elseif ((!isset($infoarr[4]) || trim($infoarr[4]) == '') && $infoarr[1] == 'templates') {
                                    $default =& $tpltpl_handler->find('default', 'module', null, trim($infoarr[2]), trim($infoarr[3]));
                                } elseif (isset($infoarr[3]) && trim($infoarr[3]) == 'images') {
                                    $infoarr[2] = trim($infoarr[2]);
                                    if (preg_match("/(.*)\.(gif|jpg|jpeg|png)$/i", $infoarr[2], $match)) {
                                        $themeimages[] = array('name' => $infoarr[2], 'content' => $info['file']);
                                    }
                                }
                                if (isset($default) && count($default) > 0) {
                                    $newtpl =& $default[0]->xoopsClone();
                                    $newtpl->setVar('tpl_id', 0);
                                    $newtpl->setVar('tpl_tplset', $tplset_name);
                                    $newtpl->setVar('tpl_source', $info['file'], true);
                                    $newtpl->setVar('tpl_lastmodified', time());
                                    if (!$tpltpl_handler->insert($newtpl)) {
                                        echo '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not insert <b>'.$info['name'].'</b> to the database.</span><br />';
                                    } else {
                                        echo '&nbsp;&nbsp;<b>'.$info['name'].'</b> inserted to the database.<br />';
                                    }
                                    unset($default);
                                }
                                unset($info);
                            }
                            $icount = count($themeimages);
                            if ($icount > 0) {
                                $imageset_handler =& xoops_gethandler('imageset');
                                $imgset =& $imageset_handler->create();
                                $imgset->setVar('imgset_name', $tplset_name);
                                $imgset->setVar('imgset_refid', 0);
                                if (!$imageset_handler->insert($imgset)) {
                                    echo '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Could not create image set.</span><br />';
                                } else {
                                    $newimgsetid = $imgset->getVar('imgset_id');
                                    echo '&nbsp;&nbsp;Image set <b>'.htmlspecialchars($tplset_name, ENT_QUOTES).'</b> created. (ID: <b>'.$newimgsetid.'</b>)<br />';
                                    if (!$imageset_handler->linktplset($newimgsetid, $tplset_name)) {
                                        echo '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Failed linking image set to template set <b>'.htmlspecialchars($tplset_name, ENT_QUOTES).'</b></span><br />';
                                    }
                                    $image_handler =& xoops_gethandler('imagesetimg');
                                    for ($i = 0; $i < $icount; $i++) {
                                        if (isset($themeimages[$i]['name']) && $themeimages[$i]['name'] != '') {
                                            $image =& $image_handler->create();
                                            $image->setVar('imgsetimg_file', $themeimages[$i]['name']);
                                            $image->setVar('imgsetimg_imgset', $newimgsetid);
                                            $image->setVar('imgsetimg_body', $themeimages[$i]['content'], true);
                                            if (!$image_handler->insert($image)) {
                                                echo '&nbsp;&nbsp;<span style="color:#ff0000;">ERROR: Failed storing image file data to database.</span><br />';
                                            } else {
                                                echo '&nbsp;&nbsp;Image file data stored into database. (ID: <b>'.$image->getVar('imgsetimg_id').'</b>)<br />';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo '<span style="color:#ff0000;">ERROR: Failed uploading file</span>';
        }
        echo '</code><br /><a href="admin.php?fct=tplsets">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'previewtpl':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0 || !XoopsMultiTokenHandler::quickValidate('tplform')) {
            redirect_header('admin.php?fct=tplsets',3);
        }
        require_once XOOPS_ROOT_PATH.'/class/template.php';
        $html = !empty($_POST['html']) ? $myts->stripSlashesGPC($_POST['html']) : '';
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile =& $tpltpl_handler->get($id, true);
        $xoopsTpl = new XoopsTpl();
        if (is_object($tplfile)) {
            $moddir = $_POST['moddir'];
            $dummylayout = '<html><head><meta http-equiv="content-type" content="text/html; charset='._CHARSET.'" /><meta http-equiv="content-language" content="'._LANGCODE.'" /><title>'.htmlspecialchars($xoopsConfig['sitename']).'</title><style type="text/css" media="all">';
            $css =& $tpltpl_handler->find($xoopsConfig['template_set'], 'css', 0, null, null, true);
            $csscount = count($css);
            for ($i = 0; $i < $csscount; $i++) {
                $dummylayout .= "\n".$css[$i]->getVar('tpl_source');
            }
            $dummylayout .= "\n".'</style></head><body><{$content}></body></html>';
            if ($tplfile->getVar('tpl_type') == 'block') {
                include_once XOOPS_ROOT_PATH.'/class/xoopsblock.php';
                $block = new XoopsBlock($tplfile->getVar('tpl_refid'));
                $xoopsTpl->assign('block', $block->buildBlock());
            }
            $dummytpl = '_dummytpl_'.time().'.html';
            $fp = fopen(XOOPS_CACHE_PATH.'/'.$dummytpl, 'w');
            fwrite($fp, $html);
            fclose($fp);
            $xoopsTpl->assign('content', $xoopsTpl->fetch('file:'.XOOPS_CACHE_PATH.'/'.$dummytpl));
            $xoopsTpl->clear_compiled_tpl('file:'.XOOPS_CACHE_PATH.'/'.$dummytpl);
            unlink(XOOPS_CACHE_PATH.'/'.$dummytpl);
            $dummyfile = '_dummy_'.time().'.html';
            $fp = fopen(XOOPS_CACHE_PATH.'/'.$dummyfile, 'w');
            fwrite($fp, $dummylayout);
            fclose($fp);
            $tplset= $tplfile->getVar('tpl_tplset');
            $tform = array('tpl_tplset' => $tplset, 'tpl_id' => $id, 'tpl_file' => $tplfile->getVar('tpl_file'), 'tpl_desc' => $tplfile->getVar('tpl_desc'), 'tpl_lastmodified' => $tplfile->getVar('tpl_lastmodified'), 'tpl_source' => htmlspecialchars($html, ENT_QUOTES), 'tpl_module' => $moddir);
            include_once XOOPS_ROOT_PATH.'/modules/system/admin/tplsets/tplform.php';
            xoops_cp_header();
            echo '<a href="admin.php?fct=tplsets">'. _MD_TPLMAIN .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;<a href="./admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$moddir.'&amp;tplset='.urlencode($tplset).'">'.htmlspecialchars($tplset, ENT_QUOTES).'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._MD_EDITTEMPLATE.'<br /><br />';
            $form->display();
            xoops_cp_footer();
            echo '<script type="text/javascript">
            <!--
            preview_window = openWithSelfMain("", "xoops_system_template_preview", 680, 450, true);
            ';
            $lines = preg_split("/(\r\n|\r|\n)( *)/", $xoopsTpl->fetch('file:'.XOOPS_CACHE_PATH.'/'.$dummyfile));
            $xoopsTpl->clear_compiled_tpl('file:'.XOOPS_CACHE_PATH.'/'.$dummyfile);
            unlink(XOOPS_CACHE_PATH.'/'.$dummyfile);
            foreach ($lines as $line) {
                echo 'preview_window.document.writeln("'.str_replace('"', '\"', $line).'");';
            }
            echo '
            preview_window.document.close();
            </script>';
        }
        break;
    case 'update':
         if (!XoopsMultiTokenHandler::quickValidate('tplsets_update')) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        $moddir = $_POST['moddir'];
        include_once XOOPS_ROOT_PATH.'/class/uploader.php';
        $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('text/html', 'application/x-cdf'), 200000);
        $uploader->setAllowedExtensions(array('html', 'htm'));
        $uploader->setPrefix('tmp');
        $msg = array();
        foreach ($_POST['xoops_upload_file'] as $upload_file) {
            $upload_file2 = str_replace('.', '_', $upload_file);
            if ($uploader->fetchMedia($upload_file2)) {
                if (!$uploader->upload()) {
                    $msg[] = $uploader->getErrors();
                } else {
                    $tpltpl_handler =& xoops_gethandler('tplfile');
                    if (empty($_POST['old_template'][$upload_file])) {
                        $tplfile =& $tpltpl_handler->find('default', null, null, $moddir, $upload_file);
                        if (count($tplfile) > 0) {
                            $tpl =& $tplfile[0]->xoopsClone();
                            $tpl->setVar('tpl_id', 0);
                            $tpl->setVar('tpl_tplset', $tplset);
                        } else {
                            $msg[] = 'Template file <b>'.$upload_file.'</b> does not need to be installed (PHP files using this template file does not exist)';
                            continue;
                        }
                    } else {
                        $tpl =& $tpltpl_handler->get($_POST['old_template'][$upload_file]);
                    }
                    $tpl->setVar('tpl_lastmodified', time());
                    $fp = @fopen($uploader->getSavedDestination(), 'r');
                    $fsource = @fread($fp, filesize($uploader->getSavedDestination()));
                    @fclose($fp);
                    $tpl->setVar('tpl_source', $fsource, true);
                    @unlink($uploader->getSavedDestination());
                    if (!$tpltpl_handler->insert($tpl)) {
                        $msg[] = 'Failed inserting data for '.$upload_file.' to database';
                    } else {
                        $msg[] = 'Template file <b>'.$upload_file.'</b> updated.';
                        if ($tplset === $xoopsConfig['template_set']) {
                            include_once XOOPS_ROOT_PATH.'/class/template.php';
                            if (xoops_template_touch($tpl->getVar('tpl_id'), true)) {
                                $msg[] = 'Template file <b>'.$upload_file.'</b> compiled.';
                            }
                        }
                    }
                }
            } else {
                if ($uploader->getMediaName() == '') {
                    continue;
                } else {
                    $msg[] = $uploader->getErrors();
                }
            }
        }
        xoops_cp_header();
        echo '<code>';
        foreach ($msg as $m) {
            echo $m.'<br />';
        }
        echo '</code><br /><a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset='.urlencode($tplset).'&amp;moddir='.$moddir.'">'._MD_AM_BTOTADMIN.'</a>';
        xoops_cp_footer();
        break;
    case 'importtpl':
        xoops_cp_header();
        if (!empty($id)) {
            xoops_confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'id' => $id, 'op' => 'importtpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREIMPT, _MD_IMPORT);
        } elseif (isset($file)) {
            xoops_confirm(array('tplset' => $tplset, 'moddir' => $moddir, 'file' => $file, 'op' => 'importtpl_go', 'fct' => 'tplsets'), 'admin.php', _MD_RUSUREIMPT, _MD_IMPORT);
        }
        xoops_cp_footer();
        break;
    case 'importtpl_go':
        if (!xoops_confirm_validate()) {
            redirect_header('admin.php?fct=tplsets',3,'Ticket Error');
        }
        $tplset = isset($_POST['tplset']) ? $myts->stripslashesGPC(trim($_POST['tplset'])) : '';
        $moddir = $_POST['moddir'];
        $id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
        $file = !empty($_POST['file']) ? $_POST['file'] : null;
        $tpltpl_handler =& xoops_gethandler('tplfile');
        $tplfile = '';
        if (!empty($id)) {
            $tplfile =& $tpltpl_handler->get($id, true);
        } else {
            $tplfiles =& $tpltpl_handler->find('default', null, null, null, $file, true);
            $tplfile = (count($tplfiles) > 0) ? $tplfiles[0] : '';
        }
        $error = true;
        if (is_object($tplfile)) {
            switch ($tplfile->getVar('tpl_type')) {
                case 'module':
                    $filepath = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$tplfile->getVar('tpl_module').'/'.$tplfile->getVar('tpl_file');
                    break;
                case 'block':
                    $filepath = XOOPS_THEME_PATH.'/'.$tplset.'/templates/'.$tplfile->getVar('tpl_module').'/blocks/'.$tplfile->getVar('tpl_file');
                    break;
                default:
                    break;
            }
            if (file_exists($filepath)) {
                if (false != $fp = fopen($filepath, 'r')) {
                    $filesource = fread($fp, filesize($filepath));
                    fclose($fp);
                    $tplfile->setVar('tpl_source', $filesource, true);
                    $tplfile->setVar('tpl_tplset', $tplset);
                    $tplfile->setVar('tpl_lastmodified', time());
                    $tplfile->setVar('tpl_lastimported', time());
                    if (!$tpltpl_handler->insert($tplfile)) {
                    } else {
                        $error = false;
                    }
                }
            }
        }
        if (false != $error) {
            xoops_cp_header();
            xoops_error('Could not import file '.$filepath);
            echo '<br /><a href="admin.php?fct=tplsets&amp;op=listtpl&amp;tplset='.urlencode($tplset).'&amp;moddir='.$moddir.'">'._MD_AM_BTOTADMIN.'</a>';
            xoops_cp_footer();
            exit();
        }
        redirect_header('admin.php?fct=tplsets&amp;op=listtpl&amp;moddir='.$tplfile->getVar('tpl_module').'&amp;tplset='.urlencode($tplfile->getVar('tpl_tplset')), 2, _MD_AM_DBUPDATED);
        break;
    default:
        break;
    }
}
?>
