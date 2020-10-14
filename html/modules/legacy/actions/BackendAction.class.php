<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_BackendAction extends Legacy_Action
{
	var $mItems = array();
	var $mGetRSSItems = null;
	function Legacy_BackendAction($flag)
	{
		parent::Legacy_Action($flag);
		$this->mGetRSSItems =& new XCube_Delegate();
		$this->mGetRSSItems->register('Legacy_BackendAction.GetRSSItems');
	}
	function getDefaultView(&$controll, &$xoopsUser)
	{
		$items = array();
		$this->mGetRSSItems->call(new XCube_Ref($items));
		$sortArr = array();
		foreach ($items as $item) {
			$i = intval($item['pubdate']);
			for (; isset($sortArr[$i]) ; $i++);
			$sortArr[$i] = $item;
		}
		krsort($sortArr);
	    $this->mItems = $sortArr;
		return LEGACY_FRAME_VIEW_INDEX;
	}
	function executeViewIndex(&$controller, &$xoopsUser, &$render)
	{
		$xoopsConfig = $controller->mRoot->mContext->mXoopsConfig;
		$renderSystem =& $controller->mRoot->getRenderSystem('Legacy_RenderSystem');
		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setTemplateName("legacy_rss.html");
		$renderTarget->setAttribute('channel_title', $xoopsConfig['sitename']);
		$renderTarget->setAttribute('channel_link', XOOPS_URL . '/');
		$renderTarget->setAttribute('channel_desc', $xoopsConfig['slogan']);
		$renderTarget->setAttribute('channel_lastbuild', formatTimestamp(time(), 'rss'));
		$renderTarget->setAttribute('channel_webmaster', $xoopsConfig['adminmail']);
		$renderTarget->setAttribute('channel_editor', $xoopsConfig['adminmail']);
		$renderTarget->setAttribute('channel_category', 'News');
		$renderTarget->setAttribute('channel_generator', 'XOOPS Cube');
		$renderTarget->setAttribute('image_url', XOOPS_URL . '/images/logo.gif');
		$dimention = getimagesize(XOOPS_ROOT_PATH . '/images/logo.gif');
		$width = 0;		
		if (empty($dimention[0])) {
			$width = 88;
		}
		else {
			$width = ($dimention[0] > 144) ? 144 : $dimention[0];
		}
		$height = 0;
		if (empty($dimention[1])) {
			$height = 31;
		} else {
			$height = ($dimention[1] > 400) ? 400 : $dimention[1];
		}
		$renderTarget->setAttribute('image_width', $width);
		$renderTarget->setAttribute('image_height', $height);
		$renderTarget->setAttribute('items', $this->mItems);
		$renderSystem->render($renderTarget);
		if (function_exists('mb_http_output')) {
			mb_http_output('pass');
		}
		header ('Content-Type:text/xml; charset=utf-8');
		print xoops_utf8_encode($renderTarget->getResult());
		exit(0);
	}
}
