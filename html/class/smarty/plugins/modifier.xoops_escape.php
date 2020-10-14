<?php
function smarty_modifier_xoops_escape($string, $esc_type = 'show')
{
    $root =& XCube_Root::getSingleton();
    $textFilter =& $root->getTextFilter();
    switch ($esc_type) {
        case 'show':
            return $textFilter->toShow($string);
        case 'edit':
            return $textFilter->toEdit($string);
        case 'plain':
        case 'link':
            return htmlspecialchars($string, ENT_QUOTES);
        default:
            return $string;
    }
}
?>
