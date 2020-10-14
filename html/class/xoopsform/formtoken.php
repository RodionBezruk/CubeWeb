<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormToken extends XoopsFormHidden {
    function XoopsFormToken($token)
    {
        if(is_object($token)) {
            parent::XoopsFormHidden($token->getTokenName(), $token->getTokenValue());
        }
        else {
            parent::XoopsFormHidden('','');
        }
    }
}
?>
