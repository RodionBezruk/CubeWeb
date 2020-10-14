<?php
class XoopsMailerLocal extends XoopsMailer {
	function XoopsMailerLocal(){
		$this->XoopsMailer();
		$this->charSet = 'euc-kr';
		$this->encoding = 'base64';
	}
}
?>
