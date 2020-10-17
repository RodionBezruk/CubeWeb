<?php
define ("FCK_HTMLAREA_DEFID_PREFIX", "fck_form_");
define ("FCK_HTMLAREA_DEFAULT_WIDTH", "100%");
define ("FCK_HTMLAREA_DEFAULT_HIGHT", "400px");
define ("FCK_HTMLAREA_DEFAULT_BASEPATH", "/common/fckeditor/");
define ("FCK_HTMLAREA_DEFAULT_TOOLBAR", "Default");
define ("FCK_HTMLAREA_DEFAULT_SKIN", "default");
function smarty_function_fck_htmlarea($params, &$smarty)
{
       $root = &XCube_Root :: getSingleton();
       $textFilter = &$root -> getTextFilter();
       if( ! empty( $params['name'] ) ) {
               $name = trim($params['name']);
               $value    = isset($params['value']) ? $params['value'] : null;
               $id       = isset($params['id']) ? trim($params['id']) :
FCK_HTMLAREA_DEFID_PREFIX . $name;
               $width    = isset($params['width'])    ?
htmlspecialchars(trim($params['width']), ENT_QUOTES)    :
FCK_HTMLAREA_DEFAULT_WIDTH;
               $height   = isset($params['height'])   ?
htmlspecialchars(trim($params['height']), ENT_QUOTES)   :
FCK_HTMLAREA_DEFAULT_HIGHT;
               $basepath = isset($params['basepath']) ?
htmlspecialchars(trim($params['basepath']), ENT_QUOTES) :
FCK_HTMLAREA_DEFAULT_BASEPATH;
               $toolbar  = isset($params['toolbar'])  ?
htmlspecialchars(trim($params['toolbar']), ENT_QUOTES)  :
FCK_HTMLAREA_DEFAULT_TOOLBAR;
               $skin     = isset($params['skin'])     ?
htmlspecialchars(trim($params['skin']), ENT_QUOTES)     :
FCK_HTMLAREA_DEFAULT_SKIN;
               include_once XOOPS_ROOT_PATH.'/common/fckeditor/fckeditor.php';
               ob_start();
                       $oFCKeditor = new FCKeditor($name) ;
                       $oFCKeditor->BasePath = XOOPS_URL. $basepath;
                       $oFCKeditor->Value    = $value ;
                       $oFCKeditor->Height   = $height ;
                       if (isset($toolbar)) $oFCKeditor->ToolbarSet = $toolbar ;
                       if (isset($skin)) $oFCKeditor->Config['SkinPath'] =  XOOPS_URL.
$basepath. 'editor/skins/'. $skin. '/' ;
                       $oFCKeditor->Create() ;
                       $editor = ob_get_contents();
               ob_end_clean();
               print $editor;
       }
}
?>
