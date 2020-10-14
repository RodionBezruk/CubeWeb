<?php
class XoopsFormElement {
	var $_name;
	var $_caption;
	var $_accesskey = '';
	var $_class = '';
	var $_hidden = false;
	var $_extra = "";
	var $_required = false;
	var $_description = "";
	var $_id = null;
	function XoopsFormElement(){
		exit("This class cannot be instantiated!");
	}
	function isContainer()
	{
		return false;
	}
	function setName($name) {
		$this->_name = trim($name);
	}
	function getName($encode=true) {
		if (false != $encode) {
			return str_replace("&amp;", "&", str_replace("'","&#039;",htmlspecialchars($this->_name)));
		}
		return $this->_name;
	}
	function setId($id) {
		$this->_id = $id;
	}
	function getId() {
		return $this->_id != null ? $this->_id : $this->getName();
	}
	function setAccessKey($key) {
		$this->_accesskey = trim($key);
	}
	function getAccessKey() {
		return $this->_accesskey;
	}
	function getAccessString( $str ) {
		$access = $this->getAccessKey();
		if ( !empty($access) && ( false !== ($pos = strpos($str, $access)) ) ) {
			return substr($str, 0, $pos) . '<span style="text-decoration:underline">' . substr($str, $pos, 1) . '</span>' . substr($str, $pos+1);
		}
		return $str;
	}
	function setClass($class) {
		$class = trim($class);
		if ( empty($class) ) {
			$this->_class = '';
		} else {
			$this->_class .= (empty($this->_class) ? '' : ' ') . $class;
		}
	}
	function getClass() {
		return $this->_class;
	}
	function setCaption($caption) {
		$this->_caption = trim($caption);
	}
	function getCaption() {
		return $this->_caption;
	}
	function setDescription($description) {
		$this->_description = trim($description);
	}
	function getDescription() {
		return $this->_description;
	}
	function setHidden() {
		$this->_hidden = true;
	}
	function isHidden() {
		return $this->_hidden;
	}
	function isBreak() {
	    return false;
	}
	function setExtra($extra, $replace = false){
		if ( $replace) {
			$this->_extra = " " .trim($extra);
		} else {
			$this->_extra .= " " . trim($extra);
		}
		return $this->_extra;
	}
	function getExtra(){
		if (isset($this->_extra)) {
			return $this->_extra;
		}
	}
	function render(){
	}
	function getMessageForJS()
	{
		$eltcaption = trim( $this->getCaption() );
        $eltmsg = empty($eltcaption) ? sprintf( _FORM_ENTER, $eltname ) : sprintf( _FORM_ENTER, $eltcaption );
        $eltmsg = str_replace('"', '\"', stripslashes($eltmsg));
		return $eltmsg;
	}
}
?>
