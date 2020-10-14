<?php
class XoopsMailerLocal extends XoopsMailer {
	function XoopsMailerLocal(){
		$this->XoopsMailer();
		$this->charSet = 'euc-kr';
		$this->encoding = 'base64';
	}
	function encodeFromName($text){
		return $this->UTF8toEUCKR($text);
	}
	function encodeSubject($text){
		return $this->UTF8toEUCKR($text);
	}
	function encodeBody(&$text){
		$text = $this->UTF8toEUCKR($text);
	}
	function UTF8toEUCKR($str){
	if (function_exists('iconv')) {
			$str = is_string($str) ? @iconv("UTF-8","EUC-KR",$str): $str;
			return $str;
	}
	elseif (function_exists('mb_convert_encoding')) {
			$str = is_string($str) ? @mb_convert_encoding($str, "EUC-KR", "UTF-8"): $str;
			return $str;
	}
	else
	{
		return $str;
	}
	}
}
?>
