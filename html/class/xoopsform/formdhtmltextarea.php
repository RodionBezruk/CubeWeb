<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/xoopsform/formtextarea.php";
class XoopsFormDhtmlTextArea extends XoopsFormTextArea
{
    var $_hiddenText;
    function XoopsFormDhtmlTextArea($caption, $name, $value, $rows=5, $cols=50, $hiddentext="xoopsHiddenText")
    {
        $this->XoopsFormTextArea($caption, $name, $value, $rows, $cols);
        $this->_xoopsHiddenText = $hiddentext;
    }
    function render()
    {
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_dhtmltextarea.html");
		$renderTarget->setAttribute("element", $this);
		$renderSystem->render($renderTarget);
		$ret = $renderTarget->getResult();
        $ret .= $this->_renderSmileys();
		return $ret;
    }
    function _renderSmileys()
    {
		$handler =& xoops_getmodulehandler('smiles', 'legacy');
		$smilesArr =& $handler->getObjects(new Criteria('display', 1));
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_opt_smileys.html");
		$renderTarget->setAttribute("element", $this);
		$renderTarget->setAttribute("smilesArr", $smilesArr);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
    }
}
?>
