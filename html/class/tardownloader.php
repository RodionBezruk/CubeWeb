<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
include_once XOOPS_ROOT_PATH.'/class/downloader.php';
include_once XOOPS_ROOT_PATH.'/class/class.tar.php';
class XoopsTarDownloader extends XoopsDownloader
{
	function XoopsTarDownloader($ext = '.tar.gz', $mimyType = 'application/x-gzip')
	{
		$this->archiver = new tar();
		$this->ext = trim($ext);
		$this->mimeType = trim($mimyType);
	}
	function addFile($filepath, $newfilename=null)
	{
		$this->archiver->addFile($filepath);
		if (isset($newfilename)) {
			for ($i = 0; $i < $this->archiver->numFiles; $i++) {
				if ($this->archiver->files[$i]['name'] == $filepath) {
					$this->archiver->files[$i]['name'] = trim($newfilename);
					break;
				}
			}
		}
	}
	function addBinaryFile($filepath, $newfilename=null)
	{
		$this->archiver->addFile($filepath, true);
		if (isset($newfilename)) {
			for ($i = 0; $i < $this->archiver->numFiles; $i++) {
				if ($this->archiver->files[$i]['name'] == $filepath) {
					$this->archiver->files[$i]['name'] = trim($newfilename);
					break;
				}
			}
		}
	}
	function addFileData(&$data, $filename, $time=0)
	{
		$dummyfile = XOOPS_CACHE_PATH.'/dummy_'.time().'.html';
		$fp = fopen($dummyfile, 'w');
		fwrite($fp, $data);
		fclose($fp);
		$this->archiver->addFile($dummyfile);
		unlink($dummyfile);
		for ($i = 0; $i < $this->archiver->numFiles; $i++) {
			if ($this->archiver->files[$i]['name'] == $dummyfile) {
				$this->archiver->files[$i]['name'] = $filename;
				if ($time != 0) {
					$this->archiver->files[$i]['time'] = $time;
				}
				break;
			}
		}
	}
	function addBinaryFileData(&$data, $filename, $time=0)
	{
		$dummyfile = XOOPS_CACHE_PATH.'/dummy_'.time().'.html';
		$fp = fopen($dummyfile, 'wb');
		fwrite($fp, $data);
		fclose($fp);
		$this->archiver->addFile($dummyfile, true);
		unlink($dummyfile);
		for ($i = 0; $i < $this->archiver->numFiles; $i++) {
			if ($this->archiver->files[$i]['name'] == $dummyfile) {
				$this->archiver->files[$i]['name'] = $filename;
				if ($time != 0) {
					$this->archiver->files[$i]['time'] = $time;
				}
				break;
			}
		}
	}
	function download($name, $gzip = true)
	{
		$this->_header($name.$this->ext);
		echo $this->archiver->toTarOutput($name.$this->ext, $gzip);
	}
}
?>
