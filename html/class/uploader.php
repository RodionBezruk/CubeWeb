<?php
define("XCUBE_IMAGETYPE_ENUM_GIF",1);
define("XCUBE_IMAGETYPE_ENUM_JPG",2);
define("XCUBE_IMAGETYPE_ENUM_PNG",3);
define("XCUBE_IMAGETYPE_ENUM_BMP",6);
class XoopsMediaUploader
{
    var $allowUnknownTypes = false;
    var $mediaName;
    var $mediaType;
    var $mediaSize;
    var $mediaTmpName;
    var $mediaError;
    var $mediaRealType = '';
    var $uploadDir = '';
    var $allowedMimeTypes = array();
    var $allowedExtensions = array();
    var $maxFileSize = 0;
    var $maxWidth;
    var $maxHeight;
    var $targetFileName;
    var $prefix;
    var $errors = array();
    var $savedDestination;
    var $savedFileName;
    var $extensionToMime = array();
	var $_strictCheckExtensions = array();
    function XoopsMediaUploader($uploadDir, $allowedMimeTypes, $maxFileSize=0, $maxWidth=null, $maxHeight=null)
    {
        @$this->extensionToMime = include( XOOPS_ROOT_PATH . '/class/mimetypes.inc.php' );
        if ( !is_array( $this->extensionToMime ) ) {
            $this->extensionToMime = array();
            return false;
        }
        if (is_array($allowedMimeTypes)) {
            $this->allowedMimeTypes =& $allowedMimeTypes;
        }
        $this->uploadDir = $uploadDir;
        $this->maxFileSize = intval($maxFileSize);
        if(isset($maxWidth)) {
            $this->maxWidth = intval($maxWidth);
        }
        if(isset($maxHeight)) {
            $this->maxHeight = intval($maxHeight);
        }
		$this->_strictCheckExtensions = array("gif"=>XCUBE_IMAGETYPE_ENUM_GIF,
                                               "jpg"=>XCUBE_IMAGETYPE_ENUM_JPG,
                                               "jpeg"=>XCUBE_IMAGETYPE_ENUM_JPG,
                                               "png"=>XCUBE_IMAGETYPE_ENUM_PNG,
                                               "bmp"=>XCUBE_IMAGETYPE_ENUM_BMP); 
    }
    function setAllowedExtensions($extensions)
    {
        $this->allowedExtensions = is_array($extensions) ? $extensions : array();
    }
	function setStrictCheckExtensions($extensions)
	{
		$this->_strictCheckExtensions = $extensions;
	}
    function fetchMedia($media_name, $index = null)
    {
        if ( empty( $this->extensionToMime ) ) {
            $this->setErrors( 'Error loading mimetypes definition' );
            return false;
        }
        if (!isset($_FILES[$media_name])) {
            $this->setErrors('File not found');
            return false;
        } elseif (is_array($_FILES[$media_name]['name']) && isset($index)) {
            $index = intval($index);
            $this->mediaName = (get_magic_quotes_gpc()) ? stripslashes($_FILES[$media_name]['name'][$index]) : $_FILES[$media_name]['name'][$index];
            $this->mediaType = $_FILES[$media_name]['type'][$index];
            $this->mediaSize = $_FILES[$media_name]['size'][$index];
            $this->mediaTmpName = $_FILES[$media_name]['tmp_name'][$index];
            $this->mediaError = !empty($_FILES[$media_name]['error'][$index]) ? $_FILES[$media_name]['errir'][$index] : 0;
        } else {
            $media_name =& $_FILES[$media_name];
            $this->mediaName = (get_magic_quotes_gpc()) ? stripslashes($media_name['name']) : $media_name['name'];
            $this->mediaName = $media_name['name'];
            $this->mediaType = $media_name['type'];
            $this->mediaSize = $media_name['size'];
            $this->mediaTmpName = $media_name['tmp_name'];
            $this->mediaError = !empty($media_name['error']) ? $media_name['error'] : 0;
        }
        if ( ($ext = strrpos( $this->mediaName, '.' )) !== false ) {
            $this->ext = strtolower ( substr( $this->mediaName, $ext + 1 ) );
            if ( isset( $this->extensionToMime[$this->ext] ) ) {
                $this->mediaRealType = $this->extensionToMime[$this->ext];
            }
        } else {
            $this->setErrors('Invalid Extension');
            return false;
        }
        $this->errors = array();
        if (intval($this->mediaSize) < 0) {
            $this->setErrors('Invalid File Size');
            return false;
        }
        if ($this->mediaName == '') {
            $this->setErrors('Filename Is Empty');
            return false;
        }
        if ($this->mediaTmpName == 'none' || !is_uploaded_file($this->mediaTmpName)) {
            $this->setErrors('No file uploaded');
            return false;
        }
        if ($this->mediaError > 0) {
            $this->setErrors('Error occurred: Error #'.$this->mediaError);
            return false;
        }
        return true;
    }
    function setTargetFileName($value){
        $this->targetFileName = strval(trim($value));
    }
    function setPrefix($value){
        $this->prefix = strval(trim($value));
    }
    function getMediaName()
    {
        return $this->mediaName;
    }
    function getMediaType()
    {
        return $this->mediaType;
    }
    function getMediaSize()
    {
        return $this->mediaSize;
    }
    function getMediaTmpName()
    {
        return $this->mediaTmpName;
    }
    function getSavedFileName(){
        return $this->savedFileName;
    }
    function getSavedDestination(){
        return $this->savedDestination;
    }
    function upload($chmod = 0644)
    {
        if ($this->uploadDir == '') {
            $this->setErrors('Upload directory not set');
            return false;
        }
        if (!is_dir($this->uploadDir)) {
            $this->setErrors('Failed opening directory: '.$this->uploadDir);
        }
        if (!is_writeable($this->uploadDir)) {
            $this->setErrors('Failed opening directory with write permission: '.$this->uploadDir);
        }
        if (!$this->checkMaxFileSize()) {
            $this->setErrors('File size too large: '.$this->mediaSize);
        }
        if (!$this->checkMaxWidth()) {
            $this->setErrors(sprintf('File width must be smaller than %u', $this->maxWidth));
        }
        if (!$this->checkMaxHeight()) {
            $this->setErrors(sprintf('File height must be smaller than %u', $this->maxHeight));
        }
        if (!$this->checkMimeType()) {
            $this->setErrors("Invalid file type");
        }
        if (count($this->errors) > 0) {
            return false;
        }
        if (!$this->_copyFile($chmod)) {
            $this->setErrors('Failed uploading file: '.$this->mediaName);
            return false;
        }
        return true;
    }
    function _copyFile($chmod)
    {
        if (isset($this->targetFileName)) {
            $this->savedFileName = $this->targetFileName;
        } elseif (isset($this->prefix)) {
            $this->savedFileName = uniqid($this->prefix).'.'.strtolower($this->ext);
        } else {
            $this->savedFileName = strtolower($this->mediaName);
        }
        $this->savedDestination = $this->uploadDir.'/'.$this->savedFileName;
        if (!move_uploaded_file($this->mediaTmpName, $this->savedDestination)) {
            return false;
        }
        @chmod($this->savedDestination, $chmod);
        return true;
    }
    function checkMaxFileSize()
    {
        if ($this->mediaSize > $this->maxFileSize) {
            return false;
        }
        return true;
    }
    function checkMaxWidth()
    {
        if (!isset($this->maxWidth)) {
            return true;
        }
        if (false !== $dimension = getimagesize($this->mediaTmpName)) {
            if ($dimension[0] > $this->maxWidth) {
                return false;
            }
        } else {
            trigger_error(sprintf('Failed fetching image size of %s, skipping max width check..', $this->mediaTmpName), E_USER_WARNING);
        }
        return true;
    }
    function checkMaxHeight()
    {
        if (!isset($this->maxHeight)) {
            return true;
        }
        if (false !== $dimension = getimagesize($this->mediaTmpName)) {
            if ($dimension[1] > $this->maxHeight) {
                return false;
            }
        } else {
            trigger_error(sprintf('Failed fetching image size of %s, skipping max height check..', $this->mediaTmpName), E_USER_WARNING);
        }
        return true;
    }
    function checkMimeType()
    {
        if (!empty($this->allowedExtensions)) {
            if (!in_array($this->ext, $this->allowedExtensions)) {
                $this->setErrors( 'File extension not allowed' );
                return false;
            }
            if (!empty($this->allowedMimeTypes)&& !in_array($this->mediaType, $this->allowedMimeTypes)) {
                $this->setErrors('Unexpected MIME Type');
                return false;
            }
        } else {
            if (empty( $this->mediaRealType ) && !$this->allowUnknownTypes) {
                return false;
            }
            if (!empty($this->allowedMimeTypes)&& !in_array($this->mediaRealType, $this->allowedMimeTypes)) {
                $this->setErrors('Unexpected MIME Type');
                return false;
            }
        }
		if(isset($this->_strictCheckExtensions[$this->ext])) {
			return $this->_checkStrict();
		}
		else {
	        return true;
		}
    }
	function _checkStrict()
	{
		$parseValue = getimagesize($this->mediaTmpName);
		if($parseValue===false)
			return false;
		return $parseValue[2]==$this->_strictCheckExtensions[$this->ext];
	}
    function checkExpectedMimeType()
    {
        if ( empty( $this->mediaRealType ) && !$this->allowUnknownTypes ) {
            return false;
        }
        return ( empty($this->allowedMimeTypes) || in_array($this->mediaRealType, $this->allowedMimeTypes) );
    }
    function setErrors($error)
    {
        $this->errors[] = trim($error);
    }
    function &getErrors($ashtml = true)
    {
        if (!$ashtml) {
            return $this->errors;
        } else {
            $ret = '';
            if (count($this->errors) > 0) {
                $ret = '<h4>Errors Returned While Uploading</h4>';
                foreach ($this->errors as $error) {
                    $ret .= $error.'<br />';
                }
            }
            return $ret;
        }
    }
}
?>
