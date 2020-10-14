<?php
define('XOOPS_CPFUNC_LOADED', 1);
function xoops_cp_header()
{
	$root=&XCube_Root::getSingleton();
	require_once XOOPS_ROOT_PATH . "/modules/legacy/kernel/Legacy_AdminControllerStrategy.class.php";
	$strategy =& new Legacy_AdminControllerStrategy($root->mController);
	$root->mController->setStrategy($strategy);
	$root->mController->setupModuleContext();
	$root->mController->_mStrategy->setupModuleLanguage();	
	require_once XOOPS_ROOT_PATH."/header.php";
}
function xoops_cp_footer()
{
	require_once XOOPS_ROOT_PATH."/footer.php";
}
function OpenTable()
{
    echo "<table width='100%' border='0' cellspacing='1' cellpadding='8' style='border: 2px solid #2F5376;'><tr class='bg4'><td valign='top'>\n";
}
function CloseTable()
{
    echo '</td></tr></table>';
}
function themecenterposts($title, $content)
{
    echo '<table cellpadding="4" cellspacing="1" width="98%" class="outer"><tr><td class="head">'.$title.'</td></tr><tr><td><br />'.$content.'<br /></td></tr></table>';
}
function myTextForm($url , $value)
{
    return '<form action="'.$url.'" method="post"><input type="submit" value="'.$value.'" /></form>';
}
function xoopsfwrite()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return false;
    } else {
    }
    if (!xoops_refcheck()) {
        return false;
    } else {
    }
    return true;
}
function xoops_module_get_admin_menu()
{
    $abscissa_step = 90;        
    $abscissa_offset = 15;        
    $rightarrow = "";
    $ordinata_step = 15;        
    $ordinata[1] = 150-$ordinata_step;
    $moveLayers = array();
    $shutdown = array();
    $firstleveltable = array();
    $js = "";
    $maxlevel = 0;
    $cnt = 1;
    $module_handler =& xoops_gethandler('module');
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('hasadmin', 1));
    $criteria->add(new Criteria('isactive', 1));
    $criteria->setSort('mid');
    $mods =& $module_handler->getObjects($criteria);
    foreach ($mods as $mod) {
            $tree[$cnt][0] = 1;
            $tree[$cnt][5] = "<img src='\".XOOPS_URL.\"/modules/".$mod->getVar('dirname')."/".$mod->getInfo('image')."' alt='' />";
            $tree[$cnt][1] = $mod->getVar('name');
            $tree[$cnt][2] = "\".XOOPS_URL.\"/modules/".$mod->getVar('dirname')."/".trim($mod->getInfo('adminindex'));
            $tree[$cnt][3] = "";
            $tree[$cnt][4] = $mod->getVar('mid');
            $tree[$cnt][6] = "<b>\"._VERSION.\":</b> ".round($mod->getVar('version')/100 , 2)."<br /><b>\"._DESCRIPTION.\":</b> ".$mod->getInfo('description');
            $layer_label[$cnt] = "L" . $cnt;
            if ( $tree[$cnt][0] > $maxlevel ) {
                $maxlevel = $tree[$cnt][0];
            }
            $cnt++;
            $adminmenu = $mod->getAdminMenu();
            if ($mod->getVar('hasnotification') || ($mod->getInfo('config') && is_array($mod->getInfo('config'))) || ($mod->getInfo('comments') && is_array($mod->getInfo('comments')))) {
                $adminmenu[] = array('link' => '".XOOPS_URL."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod='.$mod->getVar('mid'), 'title' => _PREFERENCES, 'absolute' => true);
            }
            if (!empty($adminmenu)) {
                foreach ( $adminmenu as $menuitem ) {
                    $menuitem['link'] = trim($menuitem['link']);
                    $menuitem['target'] = isset($menuitem['target']) ? trim($menuitem['target']) : '';
                    $tree[$cnt][0] = 2;
                    $tree[$cnt][1] = trim($menuitem['title']);
                    if (isset($menuitem['absolute']) && $menuitem['absolute']) {
                        $tree[$cnt][2] = (empty($menuitem['link'])) ? "#" : $menuitem['link'];
                    } else {
                        $tree[$cnt][2] = (empty($menuitem['link'])) ? "#" : "\".XOOPS_URL.\"/modules/".$mod->getVar('dirname')."/".$menuitem['link'];
                    }
                    $tree[$cnt][3] = (empty($menuitem['target'])) ? "" : $menuitem['target'];
                    $tree[$cnt][4] = $mod->getVar('mid');
                    $layer_label[$cnt] = "L" . $cnt;
                    if ($tree[$cnt][0] > $maxlevel) {
                        $maxlevel = $tree[$cnt][0];
                    }
                    $cnt++;
                }
            }
    }
    $tmpcount = count($tree);
    $tree[$tmpcount+1][0] = 0;
    for ( $i = 0; $i < $maxlevel; $i++) {
        $abscissa[$i] = $i * $abscissa_step + $abscissa_offset;
    }
    for ( $cnt = 1; $cnt <= $tmpcount; $cnt++) {        
        $layername[$tree[$cnt][0]] = $layer_label[$cnt];
        for ( $i = $tree[$cnt][0] + 1; $i < $maxlevel; $i++) {
            $ordinata[$i] = $ordinata[$i-1] + 1.5*$ordinata_step;
        }
        if ($tree[$cnt][0] < $maxlevel) {
            $ordinata[$tree[$cnt][0]] += $ordinata_step;
        }
        if ($tree[$cnt+1][0]>$tree[$cnt][0] && $cnt<$tmpcount) {                        
            $layer[$layer_label[$cnt]] = "";
            $js .= "function popUp" . $layer_label[$cnt] . "() {\n" . "shutdown();\n";
            for ($i=1; $i<=$tree[$cnt][0]; $i++) {
                $js .= "popUp(\\\"" . $layername[$i] . "\\\",true);\n";
            }
            $js .= "}\n";
            if (!isset($moveLayers[$tree[$cnt][4]])) {
                $moveLayers[$tree[$cnt][4]] = "setleft('" . $layer_label[$cnt] . "'," . $abscissa[$tree[$cnt][0]] . ");\n";
            } else {
                $moveLayers[$tree[$cnt][4]] .= "setleft('" . $layer_label[$cnt] . "'," . $abscissa[$tree[$cnt][0]] . ");\n";
                }
                if (!isset($moveLayers[$tree[$cnt][4]])) {
                    $moveLayers[$tree[$cnt][4]] = "settop('" . $layer_label[$cnt] . "'," . $ordinata[$tree[$cnt][0]] . ");\n";
                } else {
                    $moveLayers[$tree[$cnt][4]] .= "settop('" . $layer_label[$cnt] . "'," . $ordinata[$tree[$cnt][0]] . ");\n";
                }
                if (!isset($shutdown[$tree[$cnt][4]])) {
                    $shutdown[$tree[$cnt][4]] = "popUp('" . $layer_label[$cnt] . "',false);\n";
                } else {
                    $shutdown[$tree[$cnt][4]] .= "popUp('" . $layer_label[$cnt] . "',false);\n";
                }
            }
            if ($tree[$cnt+1][0]>$tree[$cnt][0] && $cnt<$tmpcount) {
                $currentarrow = $rightarrow;
            } else {
                $currentarrow = "";
            }
            $currentlink = $tree[$cnt][2];
            if ($tree[$cnt][3] != "") {
                $currenttarget = " target='" . $tree[$cnt][3] . "'";
            } else {
                $currenttarget = "";
            }
            if ($tree[$cnt][0] > 1) {
                if ( $tree[$cnt+1][0] > $tree[$cnt][0] && $cnt < $tmpcount ) {        
                    $onmouseover = " onmouseover='moveLayerY(\\\"" . $layer_label[$cnt] . "\\\", currentY) ; popUp" . $layer_label[$cnt] . "();";
                    $onmouseover = " onmouseover='moveLayerY(\\\"" . $layer_label[$cnt] . "\\\", currentY, event) ; popUp" . $layer_label[$cnt] . "();";
                } else {        
                    $onmouseover = " onmouseover='popUp" . $layername[$tree[$cnt][0]-1] . "();";
                }
                $layer[$layername[$tree[$cnt][0]-1]] .= "<img src='\".XOOPS_URL.\"/images/pointer.gif' width='8' height='8' alt='' />&nbsp;<a href='" . $currentlink . "'" . $onmouseover . "'" . $currenttarget . ">" .$tree[$cnt][1]. "</a>" . $currentarrow . "<br />\n";
            } elseif ($tree[$cnt][0] == 1) {
                if ($tree[$cnt+1][0]>$tree[$cnt][0] && $cnt<$tmpcount) {
                    $onmouseover = " onmouseover='moveLayerY(\\\"" . $layer_label[$cnt] . "\\\", currentY) ; popUp" . $layer_label[$cnt] . "();";
                    $onmouseover = " onmouseover='moveLayerY(\\\"" . $layer_label[$cnt] . "\\\", currentY,event) ; popUp" . $layer_label[$cnt] . "();";
                } else {
                   $onmouseover = " onmouseover='shutdown();";
                }
                if (!isset($firstleveltable[$tree[$cnt][4]])) {
                    $firstleveltable[$tree[$cnt][4]] = "<a href='" . $currentlink . "'" . $onmouseover . "'" . $currenttarget . ">" . $tree[$cnt][5] . "</a>" . $currentarrow . "<br />\n";
                } else {
                    $firstleveltable[$tree[$cnt][4]] .= "<a href='" . $currentlink . "'" . $onmouseover . "'" . $currenttarget . ">" . $tree[$cnt][5] . "</a>" . $currentarrow . "<br />\n";
                }
            }
        }        
        $cellpadding = 10;
        $width = $abscissa_step - $cellpadding;
        $menu_layers = "";
        for ( $cnt = 1; $cnt <= $tmpcount; $cnt++ ) {
            if (!($tree[$cnt+1][0]<=$tree[$cnt][0])) {
                $menu_layers .= "<div id='".$layer_label[$cnt]."' style='position: absolute; visibility: hidden; z-index:1000;'><table class='outer' width='150' cellspacing='1'><tr><th nowrap='nowrap'>".$tree[$cnt][1]."</th></tr><tr><td class='even' nowrap='nowrap'>".$layer[$layer_label[$cnt]]."<div style='margin-top: 5px; font-size: smaller; text-align: right;'><a href='#' onmouseover='shutdown();'>["._CLOSE."]</a></div></td></tr><tr><th style='font-size: smaller; text-align: left;'>".$tree[$cnt][5]."<br />".$tree[$cnt][6]."</th></tr></table></div>\n";
            }
        }
        $menu_layers .= "<script language='JavaScript'>\n<!--\nmoveLayers();\nloaded = 1;\n
        $content = "<"."?php\n";
        $content .= "\$xoops_admin_menu_js = \"".$js."\";\n";
        foreach ( $moveLayers as $k => $v ){
            $content .= "\$xoops_admin_menu_ml[$k] = \"".$v."\";\n";
        }
        foreach ( $shutdown as $k => $v ){
            $content .= "\$xoops_admin_menu_sd[$k] = \"".$v."\";\n";
    }
    foreach ( $firstleveltable as $k => $v ){
        $content .= "\$xoops_admin_menu_ft[$k] = \"".$v."\";\n";
    }
    $content .= "\$xoops_admin_menu_dv = \"".$menu_layers."\";\n";
    $content .= "\n?".">";
    return $content;
}
function xoops_module_write_admin_menu($content)
{
    if (!xoopsfwrite()) {
        return false;
    }
    $filename = XOOPS_CACHE_PATH.'/adminmenu.php';
    if ( !$file = fopen($filename, "w") ) {
        echo 'failed open file';
        return false;
    }
    if ( fwrite($file, $content) == -1 ) {
        echo 'failed write file';
        return false;
    }
    fclose($file);
    return true;
}
?>
