<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsFormTextDateSelect extends XoopsFormText
{
    function XoopsFormTextDateSelect($caption, $name, $size = 15, $value= 0)
    {
        $value = !is_numeric($value) ? time() : intval($value);
        $this->XoopsFormText($caption, $name, $size, 25, $value);
    }
    function render()
    {
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_textdateselect.html");
		$renderTarget->setAttribute("element", $this);
		$renderTarget->setAttribute("date", date("Y-m-d", $this->getValue()));
        $jstime = formatTimestamp($this->getValue(), '"F j, Y H:i:s"');
        include_once XOOPS_ROOT_PATH.'/include/calendarjs.php';	
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
    }
}
?>
