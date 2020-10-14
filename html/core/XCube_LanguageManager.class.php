<?php
class XCube_LanguageManager
{
	var $mLanguageName;
    var $mLocaleName;
	function XCube_LanguageManager()
	{
		$this->mLanguageName = $this->getFallbackLanguage();
        $this->mLocaleName = $this->getFallbackLocale();
	}
	function prepare()
	{
	}
	function setLocale($locale)
	{
		$this->mLanguageName = $locale;
	}
	function getLocale()
	{
		return $this->mLanguageName;
	}
	function setLanguage($language)
	{
		$this->mLanguageName = $language;
	}
	function getLanguage()
	{
		return $this->mLanguageName;
	}
	function loadGlobalMessageCatalog()
	{
	}
	function loadModuleMessageCatalog($moduleName)
	{
	}
	function loadThemeMessageCatalog($themeName)
	{
	}
	function existFile($section, $filename)
	{
	}
	function getFilepath($section, $filename)
	{
	}
	function loadTextFile($section, $filename)
	{
	}
	function translate($word)
	{
		return $word;
	}
	function getFallbackLanguage()
	{
		return "eng";
	}
	function getFallbackLocale()
	{
		return "EG";
	}
	function encodeUTF8($str)
	{
		return $str;
	}
	function decodeUTF8($str)
	{
		return $str;
	}
}
?>
