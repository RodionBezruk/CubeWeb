<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH."/modules/legacyRender/kernel/Legacy_RenderTarget.class.php";
require_once XOOPS_ROOT_PATH . "/class/template.php";
define("LEGACY_RENDERSYSTEM_BANNERSETUP_BEFORE", false);
class Legacy_XoopsTpl extends XoopsTpl
{
	var $_mContextReserve = array();
	function Legacy_XoopsTpl()
	{
		$this->_mContextReserve = array ('xoops_pagetitle' => 'legacy_pagetitle');
		parent::XoopsTpl();
	}
    function assign($tpl_var, $value = null)
    {
		if (is_array($tpl_var)){
			foreach ($tpl_var as $key => $val) {
				if ($key != '') {
					$this->assign($key, $val);
				}
			}
		}
		else {
			if ($tpl_var != '') {
				if (isset($this->_mContextReserve[$tpl_var])) {
					$root =& XCube_Root::getSingleton();
					$root->mContext->setAttribute($this->_mContextReserve[$tpl_var], htmlspecialchars_decode($value));
				}
				$this->_tpl_vars[$tpl_var] = $value;
			}
        }
	}
	function assign_by_ref($tpl_var, &$value)
	{
		if ($tpl_var != '') {
			if (isset($this->_mContextReserve[$tpl_var])) {
				$root =& XCube_Root::getSingleton();
				$root->mContext->setAttribute($this->_mContextReserve[$tpl_var], htmlspecialchars_decode($value));
			}
			$this->_tpl_vars[$tpl_var] =& $value;
		}
	}
	function &get_template_vars($name = null)
	{
		$root =& XCube_Root::getSingleton();
		if (!isset($name)) {
			foreach ($this->_mContextReserve as $t_key => $t_value) {
			    if (isset($this->_mContextReserve[$t_value])) {
				    $this->_tpl_vars[$t_key] = htmlspecialchars($root->mContext->getAttribute($this->_mContextReserve[$t_value]), ENT_QUOTES);
				}
			}
			$value =& parent::get_template_vars($name);
		}
		elseif (isset($this->_mContextReserve[$name])) {
			$value = htmlspecialchars($root->mContext->getAttribute($this->_mContextReserve[$name]), ENT_QUOTES);
		}
		else {
			$value =& parent::get_template_vars($name);
		}
		return $value;
	}
}
class Legacy_RenderSystem extends XCube_RenderSystem
{
	var $mXoopsTpl;
	var $mThemeRenderTarget;
	var $mMainRenderTarget;
	var $_mContentsData = null;
	var $mSetupXoopsTpl = null;
	var $_mIsActiveBanner = false;
	function Legacy_RenderSystem()
	{
		parent::XCube_RenderSystem();
		$this->mSetupXoopsTpl =& new XCube_Delegate();
		$this->mSetupXoopsTpl->register('Legacy_RenderSystem.SetupXoopsTpl');
	}
	function prepare(&$controller)
	{
		parent::prepare($controller);
		$root =& $this->mController->mRoot;
		$context =& $root->getContext();
		$textFilter =& $root->getTextFilter();
        if ( isset($GLOBALS['xoopsTpl']) ) {
            $this->mXoopsTpl =& $GLOBALS['xoopsTpl'];
        } else {
		    $this->mXoopsTpl =& new Legacy_XoopsTpl();
        }
		$this->mXoopsTpl->register_function("legacy_notifications_select", "LegacyRender_smartyfunction_notifications_select");
		$this->mSetupXoopsTpl->call(new XCube_Ref($this->mXoopsTpl));
		$GLOBALS['xoopsTpl'] =& $this->mXoopsTpl;
		$this->mXoopsTpl->xoops_setCaching(0);
		if ($controller->mDebugger->isDebugRenderSystem()) {
			$this->mXoopsTpl->xoops_setDebugging(true);
		}
   		$this->mXoopsTpl->assign(array('xoops_requesturi' => htmlspecialchars($GLOBALS['xoopsRequestUri'], ENT_QUOTES),	
							'xoops_js' => '
						));
		$this->mXoopsTpl->assign('xoops_sitename', $textFilter->toShow($context->getAttribute('legacy_sitename')));
		$this->mXoopsTpl->assign('xoops_pagetitle', $textFilter->toShow($context->getAttribute('legacy_pagetitle')));
		$this->mXoopsTpl->assign('xoops_slogan', $textFilter->toShow($context->getAttribute('legacy_slogan')));
        $moduleHandler =& xoops_gethandler('module');
        $legacyRender =& $moduleHandler->getByDirname('legacyRender');
		if (is_object($legacyRender)) {
			$configHandler =& xoops_gethandler('config');
			$configs =& $configHandler->getConfigsByCat(0, $legacyRender->get('mid'));
			$this->mXoopsTpl->assign('xoops_meta_keywords', $textFilter->toShow($configs['meta_keywords']));
			$this->mXoopsTpl->assign('xoops_meta_description', $textFilter->toShow($configs['meta_description']));
			$this->mXoopsTpl->assign('xoops_meta_robots', $textFilter->toShow($configs['meta_robots']));
			$this->mXoopsTpl->assign('xoops_meta_rating', $textFilter->toShow($configs['meta_rating']));
			$this->mXoopsTpl->assign('xoops_meta_author', $textFilter->toShow($configs['meta_author']));
			$this->mXoopsTpl->assign('xoops_meta_copyright', $textFilter->toShow($configs['meta_copyright']));
			$this->mXoopsTpl->assign('xoops_footer', $configs['footer']); 
			$this->_mIsActiveBanner = $configs['banners'];
			if (LEGACY_RENDERSYSTEM_BANNERSETUP_BEFORE == true) {
				if ($configs['banners'] == 1) {
					$this->mXoopsTpl->assign('xoops_banner',xoops_getbanner());
				}
				else {
					$this->mXoopsTpl->assign('xoops_banner','&nbsp;');
				}
			}
		}
		else {
			$this->mXoopsTpl->assign('xoops_banner','&nbsp;');
		}
		$arr = null;
		if (is_object($context->mXoopsUser)) {
			$arr = array(
				'xoops_isuser' => true,
				'xoops_userid' => $context->mXoopsUser->getShow('uid'),
				'xoops_uname' => $context->mXoopsUser->getShow('uname')
			);
		}
		else {
			$arr = array(
				'xoops_isuser' => false
			);
		}
		$this->mXoopsTpl->assign($arr);
	}
	function setAttribute($key,$value)
	{
		$this->mRenderTarget->setAttribute($key,$value);
	}
	function getAttribute($key)
	{
		$this->mRenderTarget->getAttribute($key);
	}
	function _commonPrepareRender()
	{
		$root =& $this->mController->mRoot;
		$context =& $root->getContext();
		$textFilter =& $root->getTextFilter();
		$themeName = $context->getThemeName();
   		$this->mXoopsTpl->assign('xoops_theme', $themeName);
   		$this->mXoopsTpl->assign('xoops_imageurl', XOOPS_THEME_URL . "/${themeName}/");
   		$this->mXoopsTpl->assign('xoops_themecss', xoops_getcss($themeName));
		$this->mXoopsTpl->assign('xoops_sitename', $textFilter->toShow($context->getAttribute('legacy_sitename')));
		$this->mXoopsTpl->assign('xoops_pagetitle', $textFilter->toShow($context->getAttribute('legacy_pagetitle')));
		$this->mXoopsTpl->assign('xoops_slogan', $textFilter->toShow($context->getAttribute('legacy_slogan')));
		if($context->mModule != null) {	
			$xoopsModule =& $context->mXoopsModule;
			$this->mXoopsTpl->assign(array('xoops_modulename' => $xoopsModule->getShow('name'),
			                               'xoops_dirname' => $xoopsModule->getShow('dirname')));
		}
		if (isset($GLOBALS['xoopsUserIsAdmin'])) {
			$this->mXoopsTpl->assign('xoops_isadmin', $GLOBALS['xoopsUserIsAdmin']);
		}
	}
	function renderBlock(&$target)
	{
		$this->_commonPrepareRender();
		$this->mXoopsTpl->xoops_setCaching(0);
		foreach($target->getAttributes() as $key=>$value) {
			$this->mXoopsTpl->assign($key,$value);
		}
		$result=&$this->mXoopsTpl->fetchBlock($target->getTemplateName(),$target->getAttribute("bid"));
		$target->setResult($result);
		foreach($target->getAttributes() as $key=>$value) {
			$this->mXoopsTpl->clear_assign($key);
		}
	}
	function _render(&$target)
	{
		foreach($target->getAttributes() as $key=>$value) {
			$this->mXoopsTpl->assign($key,$value);
		}
		$result=$this->mXoopsTpl->fetch("db:".$target->getTemplateName());
		$target->setResult($result);
		foreach ($target->getAttributes() as $key => $value) {
			$this->mXoopsTpl->clear_assign($key);
		}
	}
	function render(&$target)
	{
		switch ($target->getAttribute('legacy_buffertype')) {
			case XCUBE_RENDER_TARGET_TYPE_BLOCK:
				$this->renderBlock($target);
				break;
			case XCUBE_RENDER_TARGET_TYPE_MAIN:
				$this->renderMain($target);
				break;
			case XCUBE_RENDER_TARGET_TYPE_THEME:
				$this->renderTheme($target);
				break;
			case XCUBE_RENDER_TARGET_TYPE_BUFFER:
			default:
				break;
		}
	}
	function renderMain(&$target)
	{
		$this->_commonPrepareRender();
		$cachedTemplateId = isset($GLOBLAS['xoopsCachedTemplateId']) ? $GLOBLAS['xoopsCachedTemplateId'] : null;
		foreach($target->getAttributes() as $key=>$value) {
			$this->mXoopsTpl->assign($key,$value);
		}
		if ($target->getTemplateName()) {
		    if ($cachedTemplateId!==null) {
		        $contents=$this->mXoopsTpl->fetch('db:'.$target->getTemplateName(), $xoopsCachedTemplateId);
		    } else {
		        $contents=$this->mXoopsTpl->fetch('db:'.$target->getTemplateName());
		    }
		} else {
		    if ($cachedTemplateId!==null) {
		        $this->mXoopsTpl->assign('dummy_content', $target->getAttribute("stdout_buffer"));
		        $contents=$this->mXoopsTpl->fetch($GLOBALS['xoopsCachedTemplate'], $xoopsCachedTemplateId);
		    } else {
		        $contents=$target->getAttribute("stdout_buffer");
		    }
		}
		$target->setResult($contents);
	}
	function renderTheme(&$target)
	{
		$this->_commonPrepareRender();
		if (LEGACY_RENDERSYSTEM_BANNERSETUP_BEFORE == false) {
			if ($this->_mIsActiveBanner == 1) {
				$this->mXoopsTpl->assign('xoops_banner',xoops_getbanner());
			}
			else {
				$this->mXoopsTpl->assign('xoops_banner','&nbsp;');
			}
		}
		foreach($target->getAttributes() as $key => $value) {
			$this->mXoopsTpl->assign($key, $value);
		}
		$assignNameMap = array(
				XOOPS_SIDEBLOCK_LEFT=>array('showflag'=>'xoops_showlblock','block'=>'xoops_lblocks'),
				XOOPS_CENTERBLOCK_LEFT=>array('showflag'=>'xoops_showcblock','block'=>'xoops_clblocks'),
				XOOPS_CENTERBLOCK_RIGHT=>array('showflag'=>'xoops_showcblock','block'=>'xoops_crblocks'),
				XOOPS_CENTERBLOCK_CENTER=>array('showflag'=>'xoops_showcblock','block'=>'xoops_ccblocks'),
				XOOPS_SIDEBLOCK_RIGHT=>array('showflag'=>'xoops_showrblock','block'=>'xoops_rblocks')
			);
		foreach($assignNameMap as $key=>$val) {
			$this->mXoopsTpl->assign($val['showflag'],$this->_getBlockShowFlag($val['showflag']));
			if(isset($this->mController->mRoot->mContext->mAttributes['legacy_BlockContents'][$key])) {
				foreach($this->mController->mRoot->mContext->mAttributes['legacy_BlockContents'][$key] as $result) {
					$this->mXoopsTpl->append($val['block'], $result);
				}
			}
		}
		$result=null;
		if($target->getAttribute("isFileTheme")) {
			$result=$this->mXoopsTpl->fetch($target->getTemplateName()."/theme.html");
		}
		else {
			$result=$this->mXoopsTpl->fetch("db:".$target->getTemplateName());
		}
		$result .= $this->mXoopsTpl->fetchDebugConsole();
		$target->setResult($result);
	}
	function _getBlockShowFlag($area) {
		switch($area) {
			case 'xoops_showrblock' :
				if (isset($GLOBALS['show_rblock']) && empty($GLOBALS['show_rblock'])) return 0;
				return (!empty($this->mController->mRoot->mContext->mAttributes['legacy_BlockShowFlags'][XOOPS_SIDEBLOCK_RIGHT])) ? 1 : 0;
				break;
			case 'xoops_showlblock' :
				if (isset($GLOBALS['show_lblock']) && empty($GLOBALS['show_lblock'])) return 0;
				return (!empty($this->mController->mRoot->mContext->mAttributes['legacy_BlockShowFlags'][XOOPS_SIDEBLOCK_LEFT])) ? 1 : 0;
				break;
			case 'xoops_showcblock' :
				if (isset($GLOBALS['show_cblock']) && empty($GLOBALS['show_cblock'])) return 0;
				return (!empty($this->mController->mRoot->mContext->mAttributes['legacy_BlockShowFlags'][XOOPS_CENTERBLOCK_LEFT])||
				        !empty($this->mController->mRoot->mContext->mAttributes['legacy_BlockShowFlags'][XOOPS_CENTERBLOCK_RIGHT])||
				        !empty($this->mController->mRoot->mContext->mAttributes['legacy_BlockShowFlags'][XOOPS_CENTERBLOCK_CENTER])) ? 1 : 0;
				break;
			default :
				return 0;
		}
	}
	function sendHeader()
	{
		header('Content-Type:text/html; charset='._CHARSET);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}
	function showXoopsHeader($closeHead=true)
	{
		global $xoopsConfig;
		$myts =& MyTextSanitizer::getInstance();
		if ($xoopsConfig['gzip_compression'] == 1) {
			ob_start("ob_gzhandler");
		} else {
			ob_start();
		}
		$this->sendHeader();
		$this->_renderHeader($closeHead);
	}
	function _renderHeader($closehead=true)
	{
		global $xoopsConfig, $xoopsTheme, $xoopsConfigMetaFooter;
		echo "<!DOCTYPE html PUBLIC '-
		echo '<html xmlns="http:
		<head>
		<meta http-equiv="content-type" content="text/html; charset='._CHARSET.'" />
		<meta http-equiv="content-language" content="'._LANGCODE.'" />
		<meta name="robots" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_robots']).'" />
		<meta name="keywords" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_keywords']).'" />
		<meta name="description" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_desc']).'" />
		<meta name="rating" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_rating']).'" />
		<meta name="author" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_author']).'" />
		<meta name="copyright" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_copyright']).'" />
		<meta name="generator" content="XOOPS" />
		<title>'.htmlspecialchars($xoopsConfig['sitename']).'</title>
		<script type="text/javascript" src="'.XOOPS_URL.'/include/xoops.js"></script>
		';
		$themecss = getcss($xoopsConfig['theme_set']);
		echo '<link rel="stylesheet" type="text/css" media="all" href="'.XOOPS_URL.'/xoops.css" />';
		if ($themecss) {
			echo '<link rel="stylesheet" type="text/css" media="all" href="'.$themecss.'" />';
		}
		if ($closehead) {
			echo '</head><body>';
		}
	}
	function _renderFooter()
	{
		echo '</body></html>';
	    ob_end_flush();
	}
	function showXoopsFooter()
	{
		$this->_renderFooter();
	}
	function &createRenderTarget($type = LEGACY_RENDER_TARGET_TYPE_MAIN, $option = null)
	{
		$renderTarget = null;
		switch ($type) {
			case XCUBE_RENDER_TARGET_TYPE_MAIN:
				$renderTarget =& new Legacy_RenderTargetMain();
				break;
			case LEGACY_RENDER_TARGET_TYPE_BLOCK:
				$renderTarget =& new XCube_RenderTarget();
				$renderTarget->setAttribute('legacy_buffertype', LEGACY_RENDER_TARGET_TYPE_BLOCK);
				break;
			default:
				$renderTarget =& new XCube_RenderTarget();
				break;
		}
		return $renderTarget;
	}
	function &getThemeRenderTarget($isDialog = false)
	{
		$screenTarget = $isDialog ? new Legacy_DialogRenderTarget() : new Legacy_ThemeRenderTarget();
		return $screenTarget;
	}
}
function LegacyRender_smartyfunction_notifications_select($params, &$smarty)
{
	$root =& XCube_Root::getSingleton();
	$renderSystem =& $root->getRenderSystem('Legacy_RenderSystem');
	$renderTarget =& $renderSystem->createRenderTarget('main');
	$renderTarget->setTemplateName("legacy_notification_select_form.html");
	XCube_DelegateUtils::call('Legacyfunction.Notifications.Select', new XCube_Ref($renderTarget));
	$renderSystem->render($renderTarget);
	return $renderTarget->getResult();
}
?>
