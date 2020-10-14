<?php
define("XCUBE_FORMFILE_CHMOD", 0644);
class XCube_FormFile
{
	var $mName=null;
	var $mKey = null;
	var $mContentType=null;
	var $mFileName=null;
	var $mFileSize=0;
	var $_mTmpFileName=null;
	var $mUploadFileFlag=false;
	function XCube_FormFile($name = null, $key = null)
	{
		$this->mName = $name;
		$this->mKey = $key;
	}
	function fetch()
	{
		if($this->mName && isset($_FILES[$this->mName])) {
			if ($this->mKey != null) {
				$this->setFileName($_FILES[$this->mName]['name'][$this->mKey]);
				$this->setContentType($_FILES[$this->mName]['type'][$this->mKey]);
				$this->setFileSize($_FILES[$this->mName]['size'][$this->mKey]);
				$this->_mTmpFileName = $_FILES[$this->mName]['tmp_name'][$this->mKey];
			}
			else {
				$this->setFileName($_FILES[$this->mName]['name']);
				$this->setContentType($_FILES[$this->mName]['type']);
				$this->setFileSize($_FILES[$this->mName]['size']);
				$this->_mTmpFileName = $_FILES[$this->mName]['tmp_name'];
			}
			if($this->getFileSize()>0)
				$this->mUploadFileFlag=true;
		}
	}
	function hasUploadFile()
	{
		return $this->mUploadFileFlag;
	}
	function getContentType()
	{
		return $this->mContentType;
	}
	function getFileData()
	{
	}
	function getFileName()
	{
		return $this->mFileName;
	}
	function getFileSize()
	{
		return $this->mFileSize;
	}
	function getExtension()
	{
		$ret = null;
		$filename=$this->getFileName();
		if (preg_match("/\.([a-z\.]+)$/", $filename, $match)) {
			$ret=$match[1];
		}
		return $ret;
	}
	function setExtension($ext)
	{
		$filename=$this->getFileName();
		if(preg_match("/(.+)\.\w+$/",$filename,$match))
			$this->setFileName($match[1].".${ext}");
	}
	function setContentType($contenttype)
	{
		$this->mContentType=$contenttype;
	}
	function setFileName($filename)
	{
		$this->mFileName = $filename;
	}
	function setFileSize($filesize)
	{
		$this->mFileSize = $filesize;
	}
	function setBodyName($bodyname)
	{
		$this->setFileName($bodyname.".".$this->getExtension());
	}
	function getBodyName()
	{
		if(preg_match("/(.+)\.\w+$/",$this->getFileName(),$match)) {
			return $match[1];
		}
		return null;
	}
	function setRandomToBodyName($prefix,$salt='')
	{
		$filename = $prefix . $this->_getRandomString($salt) . "." . $this->getExtension();
		$this->setFileName($filename);
	}
	function setRandomToFilename($prefix,$salt='')
	{
		$filename = $prefix . $this->_getRandomString($salt);
		$this->setFileName($filename);
	}
	function _getRandomString($salt='')
	{
		if (empty($salt)) {
			$root=&XCube_Root::getSingleton();
			$salt = $root->getSiteConfig('Cube', 'Salt');
		}
		srand( microtime() *1000000);
		return md5($salt . rand());
	}
	function saveAs($file)
	{
		$destFile = "";
		if(preg_match("#\/$#",$file)) {
			$destFile = $file . $this->getFileName();
		}
		elseif(is_dir($file)) {
			$destFile = $file . "/" . $this->getFileName();
		}
		else {
			$destFile = $file;
		}
		$ret = move_uploaded_file($this->_mTmpFileName, $destFile);
		@chmod($destFile, XCUBE_FORMFILE_CHMOD);
		return $ret;
	}
	function saveAsRandBody($dir,$prefix='',$salt='')
	{
		$this->setRandomToBodyName($prefix,$salt);
		return $this->saveAs($dir);
	}
	function saveAsRand($dir,$prefix='',$salt='')
	{
		$this->setRandomToFileName($prefix,$salt);
		return $this->saveAs($dir);
	}
}
class XCube_FormImageFile extends XCube_FormFile
{
	function fetch()
	{
		parent::fetch();
		if ($this->hasUploadFile()) {
			if (!$this->_checkFormat()) {
				$this->mUploadFileFlag = false;
			}
		}
	}
	function getWidth()
	{
		list($width,$height,$type,$attr)=getimagesize($this->_mTmpFileName);
		return $width;
	}
	function getHeight()
	{
		list($width,$height,$type,$attr)=getimagesize($this->_mTmpFileName);
		return $height;
	}
	function _checkFormat()
	{
		if(!$this->hasUploadFile())
			return false;
		list($width,$height,$type,$attr)=getimagesize($this->_mTmpFileName);
		switch($type) {
			case IMAGETYPE_GIF:
				$this->setExtension("gif");
				break;
			case IMAGETYPE_JPEG:
				$this->setExtension("jpg");
				break;
			case IMAGETYPE_PNG:
				$this->setExtension("png");
				break;
			default:
				return false;
		}
		return true;
	}
}
?>
