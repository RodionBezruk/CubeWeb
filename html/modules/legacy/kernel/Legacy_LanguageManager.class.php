<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_LanguageManager.class.php";
class Legacy_LanguageManager extends XCube_LanguageManager
{
	function prepare()
	{
		parent::prepare();
		$this->_setupDatabase();
		$this->loadGlobalMessageCatalog();
		$this->_setupMbstring();
	}
	function _setupDatabase()
	{
		$filename = XOOPS_MODULE_PATH . '/legacy/language/' . $this->mLanguageName . '/charset_' . XOOPS_DB_TYPE . '.php';
		if (file_exists($filename)) {
			require_once($filename);
		}
	}
	function _setupMbstring()
	{
		if (defined('_MBSTRING_LANGUAGE') && function_exists("mb_language")) {
			if (@mb_language(_MBSTRING_LANGUAGE) != false && @mb_internal_encoding(_CHARSET) != false) {
				define('MBSTRING', true);
			}
			else {
				mb_language("neutral");
				mb_internal_encoding("ISO-8859-1");
				if (!defined('MBSTRING')) {
					define('MBSTRING', false);
				}
			}
			if (function_exists('mb_regex_encoding')) {
				@mb_regex_encoding(_CHARSET);
			}
			ini_set( 'mbstring.http_input', 'pass');
			ini_set( 'mbstring.http_output', 'pass');
			ini_set( 'mbstring.substitute_character', 'none');
		}
		if (!defined( "MBSTRING")) {
			define( "MBSTRING", FALSE);
		}
	}
	function loadGlobalMessageCatalog()
	{
		if (!$this->_loadFile(XOOPS_ROOT_PATH . "/modules/legacy/language/" . $this->mLanguageName . "/global.php")) {
			$this->_loadFile(XOOPS_ROOT_PATH . "/modules/legacy/language/english/global.php");
		}
		if (!defined("XOOPS_USE_MULTIBYTES")) {
			define("XOOPS_USE_MULTIBYTES", 0);
		}
	}
	function loadPageTypeMessageCatalog($type)
	{
		if (strpos($type, '.') === false) {
			$filename = XOOPS_ROOT_PATH . "/language/" . $this->mLanguageName . "/" . $type . ".php";
			if (!$this->_loadFile($filename)) {
				$filename = XOOPS_ROOT_PATH . "/language/" . $this->getFallbackLanguage() . "/" . $type . ".php";
				$this->_loadFile($filename);
			}
		}
	}
	function loadModuleMessageCatalog($moduleName)
	{
		$this->_loadLanguage($moduleName, "main");
	}
	function loadModuleAdminMessageCatalog($dirname)
	{
		$this->_loadLanguage($dirname, "admin");
	}
	function loadBlockMessageCatalog($dirname)
	{
		$this->_loadLanguage($dirname, "blocks");
	}
	function loadModinfoMessageCatalog($dirname)
	{
		$this->_loadLanguage($dirname, "modinfo");
	}
	function _loadLanguage($dirname, $fileBodyName)
	{
		$fileName = XOOPS_MODULE_PATH . "/" . $dirname . "/language/" . $this->mLanguageName . "/" . $fileBodyName . ".php";
		if (!$this->_loadFile($fileName)) {
			$fileName = XOOPS_MODULE_PATH . "/" . $dirname . "/language/english/" . $fileBodyName . ".php";
			$this->_loadFile($fileName);
		}
	}
	function _loadFile($filename)
	{
		if (file_exists($filename)) {
			global $xoopsDB, $xoopsTpl, $xoopsRequestUri, $xoopsModule, $xoopsModuleConfig,
				   $xoopsModuleUpdate, $xoopsUser, $xoopsUserIsAdmin, $xoopsTheme,
				   $xoopsConfig, $xoopsOption, $xoopsCachedTemplate, $xoopsLogger, $xoopsDebugger;
			require_once $filename;
			return true;
		}
		return false;
	}
	function existFile($section, $filename)
	{
		if ($section != null) {
			$filePath = XOOPS_ROOT_PATH . "/languages/" . $this->mLanguageName . "/${section}/${filename}";
		}
		else {
			$filePath = XOOPS_ROOT_PATH . "/languages/" . $this->mLanguageName . "/${filename}";
		}
		return file_exists($filePath);
	}
	function getFilepath($section, $filename)
	{
		$filepath = null;
		if ($section != null) {
			$filepath = XOOPS_ROOT_PATH . "/languages/" . $this->mLanguageName . "/${section}/${filename}";
		}
		else {
			$filepath = XOOPS_ROOT_PATH . "/languages/" . $this->mLanguageName . "/${filename}";
		}
		if (file_exists($filepath)) {
			return $filepath;
		}
		else {
			if ($section != null) {
				return XOOPS_ROOT_PATH . "/languages/" . $this->getFallbackLanguage() . "/${section}/${filename}";
			}
			else {
				return XOOPS_ROOT_PATH . "/languages/" . $this->getFallbackLanguage() . "/${filename}";
			}
		}
	}
	function loadTextFile($section, $filename)
	{
		$filepath = $this->getFilepath($section, $filename);
		return file_get_contents($filepath);
	}
	function getFallbackLanguage()
	{
		return "english";
	}
	function encodeUTF8($text)
	{
		if (XOOPS_USE_MULTIBYTES == 1) {
			if (function_exists('mb_convert_encoding')) {
				return mb_convert_encoding($text, 'UTF-8', _CHARSET);
			}
		}
		return utf8_encode($text);
	}
	function decodeUTF8($text)
	{
		if (XOOPS_USE_MULTIBYTES == 1) {
			if (function_exists('mb_convert_encoding')) {
				return mb_convert_encoding($text, _CHARSET, 'UTF-8');
			}
		}
		return utf8_decode($text);
	}
}
?>
