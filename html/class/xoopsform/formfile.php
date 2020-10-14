<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormFile extends XoopsFormElement {
	var $_maxFileSize;
	function XoopsFormFile($caption, $name, $maxfilesize){
		$this->setCaption($caption);
		$this->setName($name);
		$this->_maxFileSize = intval($maxfilesize);
	}
	function getMaxFileSize(){
		return $this->_maxFileSize;
	}
	function render(){
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_file.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
	}
}
?>
