<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormHiddenToken extends XoopsFormHidden {
    function XoopsFormHiddenToken($name = null, $timeout = 360){
        if (empty($name)) {
            $token =& XoopsMultiTokenHandler::quickCreate(XOOPS_TOKEN_DEFAULT);
            $name = $token->getTokenName();
        } else {
            $token =& XoopsSingleTokenHandler::quickCreate(XOOPS_TOKEN_DEFAULT);
        }
        $this->XoopsFormHidden($name, $token->getTokenValue());
    }
}
?>
