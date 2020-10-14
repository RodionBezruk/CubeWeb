<?php
class XoopsMailerLocal extends XoopsMailer {
	function XoopsMailerLocal(){
		$this->XoopsMailer();
		$this->charSet = 'iso-8859-1';
	}
}
?>
