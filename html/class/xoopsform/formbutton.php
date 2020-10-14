<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormButton extends XoopsFormElement {
	var $_value;
	var $_type;
	function XoopsFormButton($caption, $name, $value="", $type="button"){
		$this->setCaption($caption);
		$this->setName($name);
		$this->_type = $type;
		$this->setValue($value);
	}
	function getValue(){
		return $this->_value;
	}
	function setValue($value){
		$this->_value = $value;
	}
	function getType(){
		return $this->_type;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_button.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
