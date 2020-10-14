<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormSelect extends XoopsFormElement {
	var $_options = array();
	var $_multiple = false;
	var $_size;
	var $_value = array();
	function XoopsFormSelect($caption, $name, $value=null, $size=1, $multiple=false){
		$this->setCaption($caption);
		$this->setName($name);
		$this->_multiple = $multiple;
		$this->_size = intval($size);
		if (isset($value)) {
			$this->setValue($value);
		}
	}
	function isMultiple(){
		return $this->_multiple;
	}
	function getSize(){
		return $this->_size;
	}
	function getValue(){
		return $this->_value;
	}
	function setValue($value){
		if (is_array($value)) {
			foreach ($value as $v) {
				$this->_value[] = $v;
			}
		} else {
			$this->_value[] = $value;
		}
	}
	function addOption($value, $name=""){
		if ( $name != "" ) {
			$this->_options[$value] = $name;
		} else {
			$this->_options[$value] = $value;
		}
	}
	function addOptionArray($options){
		if ( is_array($options) ) {
			foreach ( $options as $k=>$v ) {
				$this->addOption($k, $v);
			}
		}
	}
	function getOptions(){
		return $this->_options;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_select.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
