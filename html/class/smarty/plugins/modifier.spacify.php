<?php
function smarty_modifier_spacify($string, $spacify_char = ' ')
{
    return implode($spacify_char,
                   preg_split('
}
?>
