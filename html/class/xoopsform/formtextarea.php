<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormTextArea extends XoopsFormElement {
	var $_cols;
	var $_rows;
	var $_value;
	function XoopsFormTextArea($caption, $name, $value="", $rows=5, $cols=50){
		$this->setCaption($caption);
		$this->setName($name);
		$this->_rows = intval($rows);
		$this->_cols = intval($cols);
		$this->setValue($value);
	}
	function getRows(){
		return $this->_rows;
	}
	function getCols(){
		return $this->_cols;
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
		$renderTarget =& $renderSystem->createRenderTarget();
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_textarea.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
