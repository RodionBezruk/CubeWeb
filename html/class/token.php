<?php
define ( 'XOOPS_TOKEN_TIMEOUT', 0 );
define ( 'XOOPS_TOKEN_PREFIX', "XOOPS_TOKEN_" );
if(!defined('XOOPS_SALT'))
    define('XOOPS_SALT',substr(md5(XOOPS_DB_PREFIX.XOOPS_DB_USER.XOOPS_ROOT_PATH),5,8));
define ( 'XOOPS_TOKEN_SESSION_STRING', "X2_TOKEN");
define ( 'XOOPS_TOKEN_MULTI_SESSION_STRING', "X2_MULTI_TOKEN");
define('XOOPS_TOKEN_DEFAULT', 'XOOPS_TOKEN_DEFAULT');
class XoopsToken
{
    var $_name_;
    var $_token_;
    var $_lifetime_;
    var $_unlimited_;
    var $_number_=0;
    function XoopsToken($name, $timeout = XOOPS_TOKEN_TIMEOUT)
    {
        $this->_name_ = $name;
        if($timeout) {
            $this->_lifetime_ = time() + $timeout;
            $this->_unlimited_ = false;
        }
        else {
            $this->_lifetime_ = 0;
            $this->_unlimited_ = true;
        }
        $this->_token_ = $this->_generateToken();
    }
    function _generateToken()
    {
        srand(microtime()*100000);
        return md5(XOOPS_SALT.$this->_name_.uniqid(rand(),true));
    }
    function getTokenName()
    {
        return XOOPS_TOKEN_PREFIX.$this->_name_."_".$this->_number_;
    }
    function getTokenValue()
    {
        return $this->_token_;
    }
    function setSerialNumber($serial_number)
    {
        $this->_number_ = $serial_number;
    }
    function getSerialNumber()
    {
        return $this->_number_;
    }
    function getHtml()
    {
        return @sprintf('<input type="hidden" name="%s" value="%s" />',$this->getTokenName(),$this->getTokenValue());
    }
    function getUrl()
    {
        return $this->getTokenName()."=".$this->getTokenValue();
    }
    function validate($token=null)
    {
        return ($this->_token_==$token && ( $this->_unlimited_ || time()<=$this->_lifetime_));
    }
}
class XoopsTokenHandler
{
    var $_prefix ="";
    function &create($name,$timeout = XOOPS_TOKEN_TIMEOUT)
    {
        $token =& new XoopsToken($name,$timeout);
        $this->register($token);
        return $token;
    }
    function &fetch($name)
    {
        $ret = null;
        if(isset($_SESSION[XOOPS_TOKEN_SESSION_STRING][$this->_prefix.$name])) {
            $ret =& $_SESSION[XOOPS_TOKEN_SESSION_STRING][$this->_prefix.$name];
        }
        return $ret;
    }
    function register(&$token)
    {
        $_SESSION[XOOPS_TOKEN_SESSION_STRING][$this->_prefix.$token->_name_] = $token;
    }
    function unregister(&$token)
    {
        unset($_SESSION[XOOPS_TOKEN_SESSION_STRING][$this->_prefix.$token->_name_]);
    }
    function isRegistered($name)
    {
        return isset($_SESSION[XOOPS_TOKEN_SESSION_STRING][$this->_prefix.$name]);
    }
    function validate(&$token,$clearIfValid)
    {
        $req_token = isset($_REQUEST[ $token->getTokenName() ]) ?
                trim($_REQUEST[ $token->getTokenName() ]) : null;
        if($req_token) {
            if($token->validate($req_token)) {
                if($clearIfValid)
                    $this->unregister($token);
                return true;
            }
        }
        return false;
    }
}
class XoopsSingleTokenHandler extends XoopsTokenHandler
{
    function autoValidate($name,$clearIfValid=true)
    {
        if($token =& $this->fetch($name)) {
            return $this->validate($token,$clearIfValid);
        }
        return false;
    }
    function &quickCreate($name,$timeout = XOOPS_TOKEN_TIMEOUT)
    {
        $handler =& new XoopsSingleTokenHandler();
        $ret =& $handler->create($name,$timeout);
        return $ret;
    }
    function quickValidate($name,$clearIfValid=true)
    {
        $handler = new XoopsSingleTokenHandler();
        return $handler->autoValidate($name,$clearIfValid);
    }
}
class XoopsMultiTokenHandler extends XoopsTokenHandler
{
    function &create($name,$timeout=XOOPS_TOKEN_TIMEOUT)
    {
        $token =& new XoopsToken($name,$timeout);
        $token->setSerialNumber($this->getUniqueSerial($name));
        $this->register($token);
        return $token;
    }
    function &fetch($name,$serial_number)
    {
        $ret = null;
        if(isset($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$this->_prefix.$name][$serial_number])) {
            $ret =& $_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$this->_prefix.$name][$serial_number];
        }
        return $ret;
    }
    function register(&$token)
    {
        $_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$this->_prefix.$token->_name_][$token->getSerialNumber()] = $token;
    }
    function unregister(&$token)
    {
        unset($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$this->_prefix.$token->_name_][$token->getSerialNumber()]);
    }
    function isRegistered($name,$serial_number)
    {
        return isset($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$this->_prefix.$name][$serial_number]);
    }
    function autoValidate($name,$clearIfValid=true)
    {
        $serial_number = $this->getRequestNumber($name);
        if($serial_number!==null) {
            if($token =& $this->fetch($name,$serial_number)) {
                return $this->validate($token,$clearIfValid);
            }
        }
        return false;
    }
    function &quickCreate($name,$timeout = XOOPS_TOKEN_TIMEOUT)
    {
        $handler =& new XoopsMultiTokenHandler();
        $ret =& $handler->create($name,$timeout);
        return $ret;
    }
    function quickValidate($name,$clearIfValid=true)
    {
        $handler = new XoopsMultiTokenHandler();
        return $handler->autoValidate($name,$clearIfValid);
    }
    function getRequestNumber($name)
    {
        $str = XOOPS_TOKEN_PREFIX.$name."_";
        foreach($_REQUEST as $key=>$val) {
            if(preg_match("/".$str."(\d+)/",$key,$match))
                return intval($match[1]);
        }
        return null;
    }
    function getUniqueSerial($name)
    {
        if(isset($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$name])) {
            if(is_array($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$name])) {
                for($i=0;isset($_SESSION[XOOPS_TOKEN_MULTI_SESSION_STRING][$name][$i]);$i++);
                return $i;
            }
        }
        return 0;
    }
}
?>
