<?php
class XoopsDownloader
{
	var $mimetype;
	var $ext;
	var $archiver;
	function XoopsDownloader()
	{
	}
	function _header($filename)
	{
		if (function_exists('mb_http_output')) {
			mb_http_output('pass');
		}
		header('Content-Type: '.$this->mimetype);
		if (preg_match("/MSIE ([0-9]\.[0-9]{1,2})/", $_SERVER['HTTP_USER_AGENT'])) {
			header('Content-Disposition: inline; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Pragma: no-cache');
		}
	}
	function addFile($filepath, $newfilename=null)
	{
	}
	function addBinaryFile($filepath, $newfilename=null)
	{
	}
	function addFileData(&$data, $filename, $time=0)
	{
	}
	function addBinaryFileData(&$data, $filename, $time=0)
	{
	}
	function download($name, $gzip = true)
	{
	}
}
?>
