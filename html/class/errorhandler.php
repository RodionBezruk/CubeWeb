<?php
class XoopsErrorHandler
{
    var $_errors = array();
    var $_showErrors = false;
    var $_isFatal = false;
    function XoopsErrorHandler()
    {
        set_error_handler('XoopsErrorHandler_HandleError');
        register_shutdown_function('XoopsErrorHandler_Shutdown');
    }
    function &getInstance()
    {
        static $instance = null;
        if (empty($instance)) {
            $instance = new XoopsErrorHandler;
        }
        return $instance;
    }
    function activate($showErrors=false)
    {
        $this->_showErrors = $showErrors;
    }
    function handleError($error)
    {
        if (($error['errno'] & error_reporting()) != $error['errno']) {
            return;
        }
        $this->_errors[] = $error;
        if ($error['errno'] == E_USER_ERROR) {
            $this->_isFatal = true;
            exit();
        }
    }
    function renderErrors()
    {
        $output = '';
        if ($this->_isFatal) {
            $output .= 'This page cannot be displayed due to an internal error.<br/><br/>';
            $output .= 'If you are the administrator of this site, please visit the <a href="http:
        }
        if (!$this->_showErrors || empty($this->_errors)) {
            return $output;
        }
        foreach( $this->_errors as $error )
        {
            switch ( $error['errno'] )
            {
                case E_USER_NOTICE:
                    $output .= "Notice [Xoops]: ";
                    break;
                case E_USER_WARNING:
                    $output .= "Warning [Xoops]: ";
                    break;
                case E_USER_ERROR:
                    $output .= "Error [Xoops]: ";
                    break;
                case E_NOTICE:
                    $output .= "Notice [PHP]: ";
                    break;
                case E_WARNING:
                    $output .= "Warning [PHP]: ";
                    break;
                default:
                    $output .= "Unknown Condition [" . $error['errno'] . "]: ";
            }
            $output .= sprintf( "%s in file %s line %s<br />\n", $error['errstr'], $error['errfile'], $error['errline'] );
        }
        return $output;
    }
}
function XoopsErrorHandler_HandleError($errNo, $errStr, $errFile, $errLine)
{
    $new_error = array(
        'errno' => $errNo,
        'errstr' => $errStr,
        'errfile' => preg_replace("|^" . XOOPS_ROOT_PATH . "/|", '', $errFile),
        'errline' => $errLine
        );
    $error_handler =& XoopsErrorHandler::getInstance();
    $error_handler->handleError($new_error);
}
function XoopsErrorHandler_Shutdown()
{
    $error_handler =& XoopsErrorHandler::getInstance();
    echo $error_handler->renderErrors();
}
?>
