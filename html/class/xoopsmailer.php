<?php
if (!defined("XOOPS_ROOT_PATH")) exit();
if (isset($GLOBALS['xoopsConfig']['language']) && file_exists(XOOPS_ROOT_PATH.'/language/'.$GLOBALS['xoopsConfig']['language'].'/mail.php')) {
	include_once XOOPS_ROOT_PATH.'/language/'.$GLOBALS['xoopsConfig']['language'].'/mail.php';
} else {
	include_once XOOPS_ROOT_PATH.'/language/english/mail.php';
}
include_once(XOOPS_ROOT_PATH."/class/mail/xoopsmultimailer.php");
class XoopsMailer
{
	var $multimailer;
	var $fromEmail;
	var $fromName;
	var $fromUser;
	var $toUsers;
	var $toEmails;
	var $headers;
	var $subject;
	var $body;
	var $errors;
	var $success;
	var $isMail;
	var $isPM;
	var $assignedTags;
	var $template;
	var $templatedir;
	var $charSet = 'iso-8859-1';
	var $encoding = '8bit';
	function XoopsMailer()
	{
		$this->multimailer = new XoopsMultiMailer();
		$this->reset();
	}
	function reset()
	{
		$this->fromEmail = "";
		$this->fromName = "";
		$this->fromUser = null; 
		$this->priority = '';
		$this->toUsers = array();
		$this->toEmails = array();
		$this->headers = array();
		$this->subject = "";
		$this->body = "";
		$this->errors = array();
		$this->success = array();
		$this->isMail = false;
		$this->isPM = false;
		$this->assignedTags = array();
		$this->template = "";
		$this->templatedir = "";
		$this->LE ="\n";
	}
	function setTemplateDir($value)
	{
		if ( substr($value, -1, 1) != "/" ) {
			$value .= "/";
		}
		$this->templatedir = $value;
	}
	function setTemplate($value)
	{
		$this->template = $value;
	}
	function setFromEmail($value)
	{
		$this->fromEmail = trim($value);
	}
	function setFromName($value)
	{
		$this->fromName = trim($value);
	}
	function setFromUser(&$user)
	{
        if ( strtolower(get_class($user)) == "xoopsuser" ) {
			$this->fromUser =& $user;
		}
	}
	function setPriority($value)
	{
		$this->priority = trim($value);
	}
	function setSubject($value)
	{
		$this->subject = trim($value);
	}
	function setBody($value)
	{
		$this->body = trim($value);
	}
	function useMail()
	{
		$this->isMail = true;
	}
	function usePM()
	{
		$this->isPM = true;
	}
	function send($debug = false)
	{
		global $xoopsConfig;
		if ( $this->body == "" && $this->template == "" ) {
			if ($debug) {
				$this->errors[] = _MAIL_MSGBODY;
			}
			return false;
		} elseif ( $this->template != "" ) {
			$path = ( $this->templatedir != "" ) ? $this->templatedir."".$this->template : (XOOPS_ROOT_PATH."/language/".$xoopsConfig['language']."/mail_template/".$this->template);
			if ( !($fd = @fopen($path, 'r')) ) {
				if ($debug) {
					$this->errors[] = _MAIL_FAILOPTPL;
				}
            			return false;
        		}
			$this->setBody(fread($fd, filesize($path)));
		}
		if ( $this->isMail  || !empty($this->toEmails) ) {
			if (!empty($this->priority)) {
				$this->headers[] = "X-Priority: " . $this->priority;
			}
			$this->headers[] = "X-Mailer: XOOPS Cube";
			$this->headers[] = "Return-Path: ".$this->fromEmail;
			$headers = join($this->LE, $this->headers);
		}
		global $xoopsConfig;
		$this->assign ('X_ADMINMAIL', $xoopsConfig['adminmail']);
		$this->assign ('X_SITENAME', $xoopsConfig['sitename']);
		$this->assign ('X_SITEURL', XOOPS_URL);
		foreach ( $this->assignedTags as $k => $v ) {
			$this->body = str_replace("{".$k."}", $v, $this->body);
			$this->subject = str_replace("{".$k."}", $v, $this->subject);
		}
		$this->body = str_replace("\r\n", "\n", $this->body);
		$this->body = str_replace("\r", "\n", $this->body);
		$this->body = str_replace("\n", $this->LE, $this->body);
		foreach ( $this->toEmails as $mailaddr ) {
			if ( !$this->sendMail($mailaddr, $this->subject, $this->body, $headers) ) {
				if ($debug) {
					$this->errors[] = sprintf(_MAIL_SENDMAILNG, $mailaddr);
				}
			} else {
				if ($debug) {
					$this->success[] = sprintf(_MAIL_MAILGOOD, $mailaddr);
				}
			}
		}
		foreach ( $this->toUsers as $user ) {
			$subject = str_replace("{X_UNAME}", $user->getVar("uname"), $this->subject );
			$text = str_replace("{X_UID}", $user->getVar("uid"), $this->body );
			$text = str_replace("{X_UEMAIL}", $user->getVar("email"), $text );
			$text = str_replace("{X_UNAME}", $user->getVar("uname"), $text );
			$text = str_replace("{X_UACTLINK}", XOOPS_URL."/user.php?op=actv&id=".$user->getVar("uid")."&actkey=".$user->getVar('actkey'), $text );
			if ( $this->isMail ) {
				if ( !$this->sendMail($user->getVar("email"), $subject, $text, $headers) ) {
					if ($debug) {
						$this->errors[] = sprintf(_MAIL_SENDMAILNG, $user->getVar("uname"));
					}
				} else {
					if ($debug) {
						$this->success[] = sprintf(_MAIL_MAILGOOD, $user->getVar("uname"));
					}
				}
			}
			if ( $this->isPM ) {
				if ( !$this->sendPM($user->getVar("uid"), $subject, $text) ) {
					if ($debug) {
						$this->errors[] = sprintf(_MAIL_SENDPMNG, $user->getVar("uname"));
					}
				} else {
					if ($debug) {
						$this->success[] = sprintf(_MAIL_PMGOOD, $user->getVar("uname"));
					}
				}
			}
		}
		if ( count($this->errors) > 0 ) {
			return false;
		}
		return true;
	}
	function sendPM($uid, $subject, $body)
	{
		global $xoopsUser;
		$pm_handler =& xoops_gethandler('privmessage');
		$pm =& $pm_handler->create();
		$pm->setVar("subject", $subject);
		$pm->setVar('from_userid', !empty($this->fromUser) ? $this->fromUser->getVar('uid') : $xoopsUser->getVar('uid'));
		$pm->setVar("msg_text", $body);
		$pm->setVar("to_userid", $uid);
		if (!$pm_handler->insert($pm)) {
			return false;
		}
		return true;
	}
	function sendMail($email, $subject, $body, $headers)
	{
		$subject = $this->encodeSubject($subject);
		$this->encodeBody($body);
		$this->multimailer->ClearAllRecipients();
		$this->multimailer->AddAddress($email);
		$this->multimailer->Subject = $subject;
		$this->multimailer->Body = $body;
		$this->multimailer->CharSet = $this->charSet;
		$this->multimailer->Encoding = $this->encoding;
		if (!empty($this->fromName)) {
			$this->multimailer->FromName = $this->encodeFromName($this->fromName);
		}
		if (!empty($this->fromEmail)) {
			$this->multimailer->From = $this->fromEmail;
		}
		$this->multimailer->ClearCustomHeaders();
		foreach ($this->headers as $header) {
			$this->multimailer->AddCustomHeader($header);
		}
		if (!$this->multimailer->Send()) {
		    $this->errors[] = $this->multimailer->ErrorInfo;
			return FALSE;
		}
		return TRUE;
	}
	function getErrors($ashtml = true)
	{
		if ( !$ashtml ) {
			return $this->errors;
		} else {
			if ( !empty($this->errors) ) {
				$ret = "<h4>"._ERRORS."</h4>";
				foreach ( $this->errors as $error ) {
					$ret .= $error."<br />";
				}
			} else {
				$ret = "";
			}
			return $ret;
		}
	}
	function getSuccess($ashtml = true)
	{
		if ( !$ashtml ) {
			return $this->success;
		} else {
			$ret = "";
			if ( !empty($this->success) ) {
				foreach ( $this->success as $suc ) {
					$ret .= $suc."<br />";
				}
			}
			return $ret;
		}
	}
	function assign($tag, $value=null)
	{
		if ( is_array($tag) ) {
			foreach ( $tag as $k => $v ) {
				$this->assign($k, $v);
			}
		} else {
			if ( !empty($tag) && isset($value) ) {
				$tag = strtoupper(trim($tag));
					$this->assignedTags[$tag] = $value;
			}
		}
	}
	function addHeaders($value)
	{
		$this->headers[] = trim($value).$this->LE;
	}
	function setToEmails($email)
	{
		if ( !is_array($email) ) {
			if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i",$email) ) {
				array_push($this->toEmails, $email);
			}
		} else {
			foreach ( $email as $e) {
				$this->setToEmails($e);
			}
		}
	}
	function setToUsers(&$user)
	{
		if ( !is_array($user) ) {
			if ( in_array(strtolower(get_class($user)) , array("xoopsuser", "userusersobject"))) {
				array_push($this->toUsers, $user);
			}
		} else {
			foreach ( $user as $u) {
				$this->setToUsers($u);
			}
		}
	}
	function setToGroups($group)
	{
		if ( !is_array($group) ) {
			if ( strtolower(get_class($group)) == "xoopsgroup" ) {
				$member_handler =& xoops_gethandler('member');
				$groups=&$member_handler->getUsersByGroup($group->getVar('groupid'),true);
				$this->setToUsers($groups, true);
			}
		} else {
			foreach ($group as $g) {
				$this->setToGroups($g);
			}
		}
	}
	function encodeFromName($text)
	{
		return $text;
	}
	function encodeSubject($text)
	{
		return $text;
	}
	function encodeBody(&$text)
	{
	}
}
?>
