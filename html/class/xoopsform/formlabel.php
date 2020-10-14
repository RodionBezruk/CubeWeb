<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormLabel extends XoopsFormElement {
	var $_value;
	function XoopsFormLabel($caption="", $value=""){
		$this->setCaption($caption);
		$this->_value = $value;
	}
	function getValue(){
		return $this->_value;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_label.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
