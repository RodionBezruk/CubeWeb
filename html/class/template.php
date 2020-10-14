<?php
if (!defined('SMARTY_DIR')) {
	exit();
}
require_once SMARTY_DIR.'Smarty.class.php';
class XoopsTpl extends Smarty
{
	var $_canUpdateFromFile = false;
	function XoopsTpl()
	{
		global $xoopsConfig;
		$this->Smarty();
		$this->compile_id = null;
		if ($xoopsConfig['theme_fromfile'] == 1) {
			$this->_canUpdateFromFile = true;
			$this->compile_check = true;
		} else {
			$this->_canUpdateFromFile = false;
			$this->compile_check = false;
		}
		$this->left_delimiter =  '<{';
		$this->right_delimiter =  '}>';
		$this->template_dir = XOOPS_THEME_PATH;
		$this->cache_dir = XOOPS_CACHE_PATH;
		$this->compile_dir = XOOPS_COMPILE_PATH;
		$this->plugins_dir = array(XOOPS_ROOT_PATH.'/class/smarty/plugins');
		$this->use_sub_dirs = false;
		$this->assign(array('xoops_url' => XOOPS_URL,
							'xoops_rootpath' => XOOPS_ROOT_PATH,
							'xoops_langcode' => _LANGCODE,
							'xoops_charset' => _CHARSET,
							'xoops_version' => XOOPS_VERSION,
							'xoops_upload_url' => XOOPS_UPLOAD_URL
							));
		XCube_DelegateUtils::call('XoopsTpl.New',  new XCube_Ref($this));
	}
	function xoops_setTemplateDir($dirname)
	{
		$this->template_dir = $dirname;
	}
	function xoops_getTemplateDir()
	{
		return $this->template_dir;
	}
	function xoops_setDebugging($flag=false)
	{
		$this->debugging = is_bool($flag) ? $flag : false;
	}
	function xoops_setCaching($num=0)
	{
		$this->caching = (int)$num;
	}
	function xoops_setCacheTime($num=0)
	{
		$num = (int)$num;
		if ($num <= 0) {
			$this->caching = 0;
		} else {
			$this->cache_lifetime = $num;
		}
	}
	function xoops_setCompileDir($dirname)
	{
		$this->compile_dir = $dirname;
	}
	function xoops_setCacheDir($dirname)
	{
		$this->cache_dir = $dirname;
	}
	function xoops_fetchFromData(&$data)
	{
		$dummyfile = XOOPS_CACHE_PATH.'/dummy_'.time();
		$fp = fopen($dummyfile, 'w');
		fwrite($fp, $data);
		fclose($fp);
		$fetched = $this->fetch('file:'.$dummyfile);
		unlink($dummyfile);
		$this->clear_compiled_tpl('file:'.$dummyfile);
		return $fetched;
	}
	function xoops_canUpdateFromFile()
	{
		return $this->_canUpdateFromFile;
	}
	function &fetchBlock($template,$bid)
	{
		$ret = $this->fetch('db:'.$template,$bid);
        return $ret;
	}
	function isBlockCached($template,$bid)
	{
		return $this->is_cached('db:'.$template, 'blk_'.$bid);
	}
	function isModuleCached($templateName,$dirname)
	{
		if(!$templateName)
			$templateName='system_dummy.html';
        return $this->is_cached('db:'.$templateName, $this->getModuleCachedTemplateId($dirname));
	}
	function fetchModule($templateName,$dirname)
	{
		if(!$templateName)
			$templateName='system_dummy.html';
        return $this->fetch('db:'.$templateName, $this->getModuleCachedTemplateId($dirname));
	}
	function getModuleCachedTemplateId($dirname)
	{
		return 'mod_'.$dirname.'|'.md5(str_replace(XOOPS_URL, '', $GLOBALS['xoopsRequestUri']));
	}
	function fetchDebugConsole()
	{
		if ($this->debugging) {
			$_params = array();
			require_once(SMARTY_CORE_DIR . 'core.get_microtime.php');
			$this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = (smarty_core_get_microtime($_params, $this) - $_debug_start_time);
			require_once(SMARTY_CORE_DIR . 'core.display_debug_console.php');
			return smarty_core_display_debug_console($_params, $this);
		}
	}
}
function xoops_template_touch($tpl_id, $clear_old = true)
{
	$result = null;
    XCube_DelegateUtils::call('Legacy.XoopsTpl.TemplateTouch', $tpl_id, $clear_old, new XCube_Ref($result));
	if ($result === null) {
		$tpl = new XoopsTpl();
		$tpl->force_compile = true;
		$tplfile_handler =& xoops_gethandler('tplfile');
		$tplfile =& $tplfile_handler->get($tpl_id);
		if ( is_object($tplfile) ) {
			$file = $tplfile->getVar('tpl_file');
			if ($clear_old) {
				$tpl->clear_cache('db:'.$file);
				$tpl->clear_compiled_tpl('db:'.$file);
			}
			return true;
		}
		return false;
	} else {
		return $result;
	}
}
function xoops_template_create ($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
{
	if ( $resource_type == 'db' ) {
		$file_handler =& xoops_gethandler('tplfile');
		$tpl =& $file_handler->find('default', null, null, null, $resource_name, true);
		if (count($tpl) > 0 && is_object($tpl[0])) {
			$template_source = $tpl[0]->getSource();
			$template_timestamp = $tpl[0]->getLastModified();
			return true;
		}
	} else {
	}
	return false;
}
function xoops_template_clear_module_cache($mid)
{
	$block_arr =& XoopsBlock::getByModule($mid);
	$count = count($block_arr);
	if ($count > 0) {
		$xoopsTpl = new XoopsTpl();	
		$xoopsTpl->xoops_setCaching(2);
		for ($i = 0; $i < $count; $i++) {
			if ($block_arr[$i]->getVar('template') != '') {
				$xoopsTpl->clear_cache('db:'.$block_arr[$i]->getVar('template'), 'blk_'.$block_arr[$i]->getVar('bid'));
			}
		}
	}
}
?>
