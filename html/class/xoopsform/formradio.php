<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormRadio extends XoopsFormElement {
	var $_options = array();
	var $_value = null;
	function XoopsFormRadio($caption, $name, $value = null){
		$this->setCaption($caption);
		$this->setName($name);
		if (isset($value)) {
			$this->setValue($value);
		}
	}
	function getValue(){
		return $this->_value;
	}
	function setValue($value){
		$this->_value = $value;
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
		$renderTarget =& $renderSystem->createRenderTarget();
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_radio.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
