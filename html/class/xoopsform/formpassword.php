<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormPassword extends XoopsFormElement {
	var $_size;
	var $_maxlength;
	var $_value;
	function XoopsFormPassword($caption, $name, $size, $maxlength, $value=""){
		$this->setCaption($caption);
		$this->setName($name);
		$this->_size = intval($size);
		$this->_maxlength = intval($maxlength);
		$this->setValue($value);
	}
	function getSize(){
		return $this->_size;
	}
	function getMaxlength(){
		return $this->_maxlength;
	}
	function getValue(){
		return $this->_value;
	}
	function setValue($value){
		$this->_value = $value;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_password.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
