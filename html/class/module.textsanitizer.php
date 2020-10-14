<?php
class MyTextSanitizer
{
    var $censorConf;
	var $mTextFilter = null;
	var $mMakeClickablePostFilter = null;
	var $mXoopsCodePostFilter = null;
    function MyTextSanitizer()
    {
		$this->mMakeClickablePostFilter =& new XCube_Delegate();
		$this->mMakeClickablePostFilter->register('MyTextSanitizer.MakeClickablePostFilter');
		$this->mXoopsCodePostFilter =& new XCube_Delegate();
		$this->mXoopsCodePostFilter->register('MyTextSanitizer.XoopsCodePostFilter');
        $root =& XCube_Root::getSingleton();
        $this->mTextFilter =& $root->getTextFilter();
    }
    function &getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new MyTextSanitizer();
        }
        return $instance;
    }
    function getSmileys()
    {
        return $this->mTextFilter->getSmileys();
    }
    function &smiley($text)
    {
        $text = $this->mTextFilter->smiley($text);
        return $text;
    }
    function &makeClickable($text)
    {
        $text = $this->mTextFilter->makeClickable($text);
        $this->mMakeClickablePostFilter->call(new XCube_Ref($text));
        return $text;
    }
    function &xoopsCodeDecode($text, $allowimage = 1)
    {
        $text = $this->mTextFilter->convertXCode($text, $allowimage);
        $this->mXoopsCodePostFilter->call(new XCube_Ref($text), $allowimage);
        return $text;
    }
    function _filterImgUrl($matches)
    {
        if ($this->checkUrlString($matches[2])) {
            return $matches[0];
        } else {
            return "";
        }
    }
    function checkUrlString($text)
    {
        if (preg_match("/[\\0-\\31]/", $text)) {
            return false;
        }
        return !preg_match("/^(javascript|vbscript|about):/i", $text);
    }
    function &nl2Br($text)
    {
        $ret = $this->mTextFilter->nl2Br($text);
        return $ret;
    }
    function &addSlashes($text)
    {
        if (!get_magic_quotes_gpc()) {
            $text = addslashes($text);
        }
        return $text;
    }
    function &stripSlashesGPC($text)
    {
        if (get_magic_quotes_gpc()) {
            $text = stripslashes($text);
        }
        return $text;
    }
    function &htmlSpecialChars($text, $forEdit=false)
    {
        if (!$forEdit) {
            $ret = $this->mTextFilter->toShow($text, true);
        } else {
            $ret = $this->mTextFilter->toEdit($text);
        }
		return $ret;
    }
    function &undoHtmlSpecialChars($text)
    {
        $ret = preg_replace(array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i"), array(">", "<", "\"", "'"), $text);
		return $ret;
    }
    function _ToShowTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1) {
        $text = $this->codePreConv($text, $xcode);
        if ($html != 1) $text = $this->htmlSpecialChars($text);
        $text = $this->makeClickable($text);
        if ($smiley != 0) $text = $this->smiley($text);
        if ($xcode != 0) $text = $this->xoopsCodeDecode($text, $image);
        if ($br != 0) $text = $this->nl2Br($text);
        $text = $this->codeConv($text, $xcode, $image);
        return $text;
    }
    function &displayTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
    {
        $text = $this->_ToShowTarea($text, $html, $smiley, $xcode, $image, $br);
        return $text;
    }
    function &previewTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
    {
        $text =& $this->stripSlashesGPC($text);
        $text = $this->_ToShowTarea($text, $html, $smiley, $xcode, $image, $br);
        return $text;
    }
    function &censorString($text)
    {
        if (!isset($this->censorConf)) {
            $config_handler =& xoops_gethandler('config');
            $this->censorConf =& $config_handler->getConfigsByCat(XOOPS_CONF_CENSOR);
        }
        if ($this->censorConf['censor_enable'] == 1) {
            $replacement = $this->censorConf['censor_replace'];
            foreach ($this->censorConf['censor_words'] as $bad) {
                if ( !empty($bad) ) {
                    $bad = quotemeta($bad);
                    $patterns[] = "/(\s)".$bad."/siU";
                    $replacements[] = "\\1".$replacement;
                    $patterns[] = "/^".$bad."/siU";
                    $replacements[] = $replacement;
                    $patterns[] = "/(\n)".$bad."/siU";
                    $replacements[] = "\\1".$replacement;
                    $patterns[] = "/]".$bad."/siU";
                    $replacements[] = "]".$replacement;
                    $text = preg_replace($patterns, $replacements, $text);
                }
            }
        }
        return $text;
    }
    function codePreConv($text, $xcode = 1) {
        if($xcode != 0){
            $text = $this->mTextFilter->preConvertXCode($text, $xcode);
        }
        return $text;
    }
    function codeConv($text, $xcode = 1, $image = 1){
        if($xcode != 0){
            $text = $this->mTextFilter->postConvertXCode($text, $xcode);
        }
        return $text;
    }
    function sanitizeForDisplay($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        $text = $this->_ToShowTarea($text, $allowhtml, $smiley, $bbcode, 1, 1);
        return $text;
    }
    function sanitizeForPreview($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        $text = $this->oopsStripSlashesGPC($text);
        $text = $this->_ToShowTarea($text, $allowhtml, $smiley, $bbcode, 1, 1);
        return $text;
    }
    function makeTboxData4Save($text)
    {
        return $this->addSlashes($text);
    }
    function makeTboxData4Show($text, $smiley=0)
    {
        $text = $this->mTextFilter->toShow($text, true);
        return $text;
    }
    function makeTboxData4Edit($text)
    {
        return $this->mTextFilter->toEdit($text);
    }
    function makeTboxData4Preview($text, $smiley=0)
    {
        $text = $this->stripSlashesGPC($text);
        $text = $this->mTextFilter->toShow($text, true);
        return $text;
    }
    function makeTboxData4PreviewInForm($text)
    {
        $text = $this->stripSlashesGPC($text);
        return $this->mTextFilter->toEdit($text);
    }
    function makeTareaData4Save($text)
    {
        return $this->addSlashes($text);
    }
    function &makeTareaData4Show($text, $html=1, $smiley=1, $xcode=1)
    {
        $ret = $this->displayTarea($text, $html, $smiley, $xcode);
        return $ret;
    }
    function makeTareaData4Edit($text)
    {
        return $this->mTextFilter->toEdit($text);
    }
    function &makeTareaData4Preview($text, $html=1, $smiley=1, $xcode=1)
    {
        $ret = $this->previewTarea($text, $html, $smiley, $xcode);
        return $ret;
    }
    function makeTareaData4PreviewInForm($text)
    {
        $text = $this->stripSlashesGPC($text);
        return $this->mTextFilter->toEdit($text);
    }
    function makeTareaData4InsideQuotes($text)
    {
        return $this->mTextFilter->toShow($text, true);
    }
    function &oopsStripSlashesGPC($text)
    {
        $ret = $this->stripSlashesGPC($text);
        return $ret;
    }
    function &oopsStripSlashesRT($text)
    {
        if (get_magic_quotes_runtime()) {
            $text =& stripslashes($text);
        }
        return $text;
    }
    function &oopsAddSlashes($text)
    {
        $ret = $this->addSlashes($text);
        return $ret;
    }
    function &oopsHtmlSpecialChars($text)
    {
        $ret = $this->mTextFilter->toShow($text, true);
        return $ret;
    }
    function &oopsNl2Br($text)
    {
        $ret = $this->nl2br($text);
        return $ret;
    }
}
?>
