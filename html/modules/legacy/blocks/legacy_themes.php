<?php
function b_legacy_themes_show($options)
{
    global $xoopsConfig;
	if (count($xoopsConfig['theme_set_allowed']) == 0) {
		return null;
	}
    $block = array();
	if (xoops_getenv('REQUEST_METHOD') == 'POST') {
		$block['isEnableChanger'] = 0;
		return $block;
	}
	$block['isEnableChanger'] = 1;
    $theme_options = array();
	$handler =& xoops_getmodulehandler('theme', 'legacy');
    foreach ($xoopsConfig['theme_set_allowed'] as $name) {
		$theme =& $handler->get($name);
		if ($theme != null) {
			$theme_option['name'] = $name;
			$theme_option['screenshot'] = $theme->getShow('screenshot');
			$theme_option['screenshotUrl'] = XOOPS_THEME_URL . "/" . $name . "/" . $theme->getShow('screenshot');
	        if ($name == $xoopsConfig['theme_set']) {
	            $theme_option['selected'] = 'selected="selected"';
                $block['theme_selected_screenshot'] = $theme->getShow('screenshot');
	        } else {
	            $theme_option['selected'] = '';
	        }
	        $theme_options[] = $theme_option;
		}
    }
    $block['count'] = count($xoopsConfig['theme_set_allowed']);
    $block['mode'] = $options[0];
    $block['width'] = $options[1];
    $block['theme_options'] = $theme_options;
    return $block;
}
function b_legacy_themes_edit($options)
{
    $chk = "";
    $form = _MB_LEGACY_LANG_THSHOW."&nbsp;";
    if ( $options[0] == 1 ) {
        $chk = ' checked="checked"';
    }
    $form .= '<input type="radio" name="options[0]" value="1"'.$chk.' />&nbsp;'._YES;
    $chk = "";
    if ( $options[0] == 0 ) {
        $chk = ' checked="checked"';
    }
    $form .= '&nbsp;<input type="radio" name="options[0]" value="0"'.$chk.' />'._NO;
    $form .= '<br />'._MB_LEGACY_LANG_THWIDTH.'&nbsp;';
    $form .= '<input type="text" name="options[1]" value="'.$options[1].'" />';
    return $form;
}
?>
