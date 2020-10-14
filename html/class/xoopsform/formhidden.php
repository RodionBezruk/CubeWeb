<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormHidden extends XoopsFormElement {
	var $_value;
	function XoopsFormHidden($name, $value){
		$this->setName($name);
		$this->setHidden();
		$this->setValue($value);
		$this->setCaption("");
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
		$renderTarget->setTemplateName("legacy_xoopsform_hidden.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
