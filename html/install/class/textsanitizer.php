<?php
set_magic_quotes_runtime(0);
class TextSanitizer
{
    function TextSanitizer()
    {
    }
    function &getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new TextSanitizer();
        }
        return $instance;
    }
    function &makeClickable(&$text)
    {
        $patterns = array("/([^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([^, \r\n\"\(\)'<>]+)/i", "/([^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([^, \r\n\"\(\)'<>]+)/i", "/([^]_a-z0-9-=\"'\/])([a-z0-9\-_.]+?)@([^, \r\n\"\(\)'<>]+)/i");
        $replacements = array("\\1<a href=\"\\2:
        $ret = preg_replace($patterns, $replacements, $text);
        return $ret;
    }
    function &nl2Br($text)
    {
        $ret = preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$text);
        return $ret;
    }
    function &addSlashes($text, $force=false)
    {
        if ($force) {
            $ret = addslashes($text);
            return $ret;
        }
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
    function &htmlSpecialChars($text)
    {
        $text = preg_replace("/&amp;/i", '&', htmlspecialchars($text, ENT_QUOTES));
        return $text;
    }
    function &undoHtmlSpecialChars(&$text)
    {
        $ret = preg_replace(array("/&gt;/i", "/&lt;/i", "/&quot;/i", "/&#039;/i"), array(">", "<", "\"", "'"), $text);
        return $ret;
    }
    function &displayText($text, $html=false)
    {
        if (! $html) {
            $text =& $this->htmlSpecialChars($text);
        }
        $text =& $this->makeClickable($text);
        $text =& $this->nl2Br($text);
        return $text;
    }
    function &previewText($text, $html=false)
    {
        $text =& $this->stripSlashesGPC($text);
        return $this->displayText($text, $html);
    }
    function sanitizeForDisplay($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        if ( $allowhtml == 0 ) {
            $text = $this->htmlSpecialChars($text);
        } else {
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->nl2Br($text);
        return $text;
    }
    function sanitizeForPreview($text, $allowhtml = 0, $smiley = 1, $bbcode = 1)
    {
        $text = $this->oopsStripSlashesGPC($text);
        if ( $allowhtml == 0 ) {
            $text = $this->htmlSpecialChars($text);
        } else {
            $text = $this->makeClickable($text);
        }
        if ( $smiley == 1 ) {
            $text = $this->smiley($text);
        }
        if ( $bbcode == 1 ) {
            $text = $this->xoopsCodeDecode($text);
        }
        $text = $this->nl2Br($text);
        return $text;
    }
    function makeTboxData4Save($text)
    {
        return $this->addSlashes($text);
    }
    function makeTboxData4Show($text, $smiley=0)
    {
        $text = $this->htmlSpecialChars($text);
        return $text;
    }
    function makeTboxData4Edit($text)
    {
        return $this->htmlSpecialChars($text);
    }
    function makeTboxData4Preview($text, $smiley=0)
    {
        $text = $this->stripSlashesGPC($text);
        $text = $this->htmlSpecialChars($text);
        return $text;
    }
    function makeTboxData4PreviewInForm($text)
    {
        $text = $this->stripSlashesGPC($text);
        return $this->htmlSpecialChars($text);
    }
    function makeTareaData4Save($text)
    {
        return $this->addSlashes($text);
    }
    function &makeTareaData4Show(&$text, $html=1, $smiley=1, $xcode=1)
    {
        return $this->displayTarea($text, $html, $smiley, $xcode);
    }
    function makeTareaData4Edit($text)
    {
        return htmlSpecialChars($text, ENT_QUOTES);
    }
    function &makeTareaData4Preview(&$text, $html=1, $smiley=1, $xcode=1)
    {
        return $this->previewTarea($text, $html, $smiley, $xcode);
    }
    function makeTareaData4PreviewInForm($text)
    {
        $text = $this->stripSlashesGPC($text);
        return htmlSpecialChars($text, ENT_QUOTES);
    }
    function makeTareaData4InsideQuotes($text)
    {
        return $this->htmlSpecialChars($text);
    }
    function &oopsStripSlashesGPC($text)
    {
        return $this->stripSlashesGPC($text);
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
        return $this->addSlashes($text);
    }
    function &oopsHtmlSpecialChars($text)
    {
        return $this->htmlSpecialChars($text);
    }
    function &oopsNl2Br($text)
    {
        return $this->nl2br($text);
    }
}
?>
