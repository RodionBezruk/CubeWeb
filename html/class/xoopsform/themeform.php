<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/form.php";
class XoopsThemeForm extends XoopsForm
{
	function insertBreak($extra = '', $class= '')
	{
    	$class = ($class != '') ? " class='$class'" : '';
    	$extra = ($extra != '') ? $extra : '&nbsp';
	    $this->addElement(new XoopsFormBreak($extra, $class)) ;
	}
	function render()
	{
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_themeform.html");
		$renderTarget->setAttribute("form", $this);
		$renderSystem->render($renderTarget);
		$ret = $renderTarget->getResult();
		$ret .= $this->renderValidationJS( true );
		return $ret;
	}
}
?>
