<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormElementTray extends XoopsFormElement {
	var $_elements = array();
	var $_required = array();
	var $_delimeter;
	function XoopsFormElementTray($caption, $delimeter="&nbsp;", $name=""){
	    $this->setName($name);
		$this->setCaption($caption);
		$this->_delimeter = $delimeter;
	}
	function isContainer()
	{
		return true;
	}
	function addElement(&$formElement, $required=false){
		$this->_elements[] =& $formElement;
		if ($required) {
			if (!$formElement->isContainer()) {
				$this->_required[] =& $formElement;
			} else {
				$required_elements =& $formElement->getElements(true);
				$count = count($required_elements);
				for ($i = 0 ; $i < $count; $i++) {
					$this->_required[] =& $required_elements[$i];
				}
			}
		}
	}
	function &getRequired()
	{
		return $this->_required;
	}
	function &getElements($recurse = false){
		if (!$recurse) {
			return $this->_elements;
		} else {
			$ret = array();
			$count = count($this->_elements);
			for ($i = 0; $i < $count; $i++) {
				if (!$this->_elements[$i]->isContainer()) {
					$ret[] =& $this->_elements[$i];
				} else {
					$elements =& $this->_elements[$i]->getElements(true);
					$count2 = count($elements);
					for ($j = 0; $j < $count2; $j++) {
						$ret[] =& $elements[$j];
					}
					unset($elements);
				}
			}
			return $ret;
		}
	}
	function getDelimeter(){
		return $this->_delimeter;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_elementtray.html");
		$renderTarget->setAttribute("tray", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
