<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once(XOOPS_ROOT_PATH.'/class/mail/phpmailer/class.phpmailer.php');
class XoopsMultiMailer extends PHPMailer {
  var $From     = "";
  var $FromName   = "";
  var $Mailer   = "mail";
  var $Sendmail = "/usr/sbin/sendmail";
  var $Host   = "";
  var $SMTPAuth = FALSE;
  var $Username = "";
  var $Password = "";
  function XoopsMultiMailer(){
    global $xoopsConfig;
  	$this->ClearAllRecipients();
    $config_handler =& xoops_gethandler('config');
    $xoopsMailerConfig =& $config_handler->getConfigsByCat(XOOPS_CONF_MAILER);
    $this->From = $xoopsMailerConfig['from'];
    if ($this->From == '') {
        $this->From = $xoopsConfig['adminmail'];
    }
    $this->Sender = $xoopsConfig['adminmail'];
    if ($xoopsMailerConfig["mailmethod"] == "smtpauth") {
        $this->Mailer = "smtp";
      $this->SMTPAuth = TRUE;
      $this->Host = implode(';',$xoopsMailerConfig['smtphost']);
      $this->Username = $xoopsMailerConfig['smtpuser'];
      $this->Password = $xoopsMailerConfig['smtppass'];
    } else {
      $this->Mailer = $xoopsMailerConfig['mailmethod'];
      $this->SMTPAuth = FALSE;
      $this->Sendmail = $xoopsMailerConfig['sendmailpath'];
      $this->Host = implode(';',$xoopsMailerConfig['smtphost']);
    }
  }
    function AddrFormat($addr) {
        if(empty($addr[1]))
            $formatted = $addr[0];
        else
            $formatted = sprintf('%s <%s>', '=?'.$this->CharSet.'?B?'.base64_encode($addr[1]).'?=', $addr[0]);
        return $formatted;
    }
    function Send() {
        if (empty($this->Sender) 
            || preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i",$this->Sender)) {
            return parent::Send();
        }
        return false;
    }
    function SetLanguage($lang_type, $lang_path = 'language/') {
        $ext = substr($lang_path, -1, 1);
        if ($ext != '/' && file_exists($lang_path)) {
            include($lang_path);
            $this->language = $PHPMAILER_LANG;
            return true;
        }
        return parent::SetLanguage($lang_type, $lang_path);
    }
}
?>
