<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_TextFilter extends XCube_TextFilter
{
    var $mMakeXCodeConvertTable = null;
    var $mMakeXCodeCheckImgPatterns = null;
    var $mMakeClickableConvertTable = null;
    var $mMakePreXCodeConvertTable = null;
    var $mMakePostXCodeConvertTable = null;
    var $mXCodePre = null;
    var $mMakeClickablePre = null;
    var $mClickablePatterns = array();
    var $mClickableReplacements = array();
    var $mXCodePatterns = array();
    var $mXCodeReplacements = array();
    var $mXCodeCheckImgPatterns = array();
    var $mPreXCodePatterns = array();
    var $mPreXCodeReplacements = array();
    var $mPostXCodePatterns = array();
    var $mPostXCodeReplacements = array();
    var $mSmileys = array();
    var $mSmileysConvTable = array();
    function Legacy_TextFilter()
	{
        $this->mMakeClickableConvertTable =& new XCube_Delegate;
        $this->mMakeClickableConvertTable->register('Legacy_TextFilter.MakeClickableConvertTable');
        $this->mMakeClickableConvertTable->add('Legacy_TextFilter::makeClickableConvertTable', XCUBE_DELEGATE_PRIORITY_2);
        $this->mMakeXCodeConvertTable =& new XCube_Delegate;
        $this->mMakeXCodeConvertTable->register('Legacy_TextFilter.MakeXCodeConvertTable');
        $this->mMakeXCodeConvertTable->add('Legacy_TextFilter::makeXCodeConvertTable', XCUBE_DELEGATE_PRIORITY_2);
        $this->mMakeXCodeCheckImgPatterns =& new XCube_Delegate;
        $this->mMakeXCodeCheckImgPatterns->register('Legacy_TextFilter.MakeXCodeCheckImgPatterns');
        $this->mMakeXCodeCheckImgPatterns->add('Legacy_TextFilter::makeXCodeCheckImgPatterns', XCUBE_DELEGATE_PRIORITY_2);
        $this->mMakePreXCodeConvertTable =& new XCube_Delegate;
        $this->mMakePreXCodeConvertTable->register('Legacy_TextFilter.MakePreXCodeConvertTable');
        $this->mMakePreXCodeConvertTable->add('Legacy_TextFilter::makePreXCodeConvertTable', XCUBE_DELEGATE_PRIORITY_2);
        $this->mMakePostXCodeConvertTable =& new XCube_Delegate;
        $this->mMakePostXCodeConvertTable->register('Legacy_TextFilter.MakePostXCodeConvertTable');
        $this->mMakePostXCodeConvertTable->add('Legacy_TextFilter::makePostXCodeConvertTable', XCUBE_DELEGATE_PRIORITY_2);
        $this->mMakeClickablePre =& new XCube_Delegate();
        $this->mMakeClickablePre->register('MyTextSanitizer.MakeClickablePre');
        $this->mXCodePre =& new XCube_Delegate();
        $this->mXCodePre->register('MyTextSanitizer.XoopsCodePre');
    }
    function getInstance(&$instance) {
        if (empty($instance)) {
            $instance = new Legacy_TextFilter();
        }
    }
    function toShow($text, $x2comat=false) {
        if ($x2comat) {
            return preg_replace(array("/&amp;(#[0-9]+|#x[0-9a-f]+|[a-z]+[0-9]*);/i", "/&nbsp;/i"), array('&\\1;', '&amp;nbsp;'), htmlspecialchars($text, ENT_QUOTES));
        } else {
            return preg_replace("/&amp;(#[0-9]+|#x[0-9a-f]+|[a-z]+[0-9]*);/i", '&\\1;', htmlspecialchars($text, ENT_QUOTES));
        }
    }
    function toEdit($text) {
        return preg_replace("/&amp;(#0?[0-9]{4,6};)/i", '&$1', htmlspecialchars($text, ENT_QUOTES));
    }
    function toShowTarea($text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1, $x2comat=false) {
        $text = $this->preConvertXCode($text, $xcode);
        if ($html != 1) $text = $this->toShow($text, $x2comat);
        $text = $this->makeClickable($text);
        if ($smiley != 0) $text = $this->smiley($text);
        if ($xcode != 0) $text = $this->convertXCode($text, $image);
        if ($br != 0) $text = $this->nl2Br($text);
        $text = $this->postConvertXCode($text, $xcode, $image);
        return $text;
    }
    function getSmileys() {
        if (count($this->mSmileys) == 0) {
            $this->mSmileysConvTable[0] = $this->mSmileysConvTable[1] = array();
            $db =& Database::getInstance();
            if ($getsmiles = $db->query("SELECT * FROM ".$db->prefix("smiles"))){
                while ($smile = $db->fetchArray($getsmiles)) {
                    $this->mSmileys[] = $smile;
                    $this->mSmileysConvTable[0][] = $smile['code'];
                    $this->mSmileysConvTable[1][] = '<img src="'.XOOPS_UPLOAD_URL.'/'.htmlspecialchars($smile['smile_url']).'" alt="" />';
                }
            }
        }
        return $this->mSmileys;
    }
    function smiley($text) {
        if (count($this->mSmileys) == 0) $this->getSmileys();
        if (count($this->mSmileys) != 0) {
            $text = str_replace($this->mSmileysConvTable[0], $this->mSmileysConvTable[1], $text);
        }
        return $text;
    }
    function makeClickable($text) {
        if (empty($this->mClickablePatterns)) {
            $this->mMakeClickableConvertTable->call(new XCube_Ref($this->mClickablePatterns), new XCube_Ref($this->mClickableReplacements));
            $this->mMakeClickablePre->call(new XCube_Ref($this->mClickablePatterns), new XCube_Ref($this->mClickableReplacements));
        }
        $text = preg_replace($this->mClickablePatterns, $this->mClickableReplacements, $text);
        return $text;
    }
    function makeClickableConvertTable(&$patterns, &$replacements) {
        $patterns[] = "/(^|[^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([^, \r\n\"\(\)'<>]+)/i";
        $replacements[] = "\\1<a href=\"\\2:
        $patterns[] = "/(^|[^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([^, \r\n\"\(\)'<>]+)/i";
        $replacements[] = "\\1<a href=\"http:
        $patterns[] = "/(^|[^]_a-z0-9-=\"'\/])ftp\.([a-z0-9\-]+)\.([^, \r\n\"\(\)'<>]+)/i";
        $replacements[] = "\\1<a href=\"ftp:
        $patterns[] = "/(^|[^]_a-z0-9-=\"'\/:\.])([a-z0-9\-_\.]+?)@([a-z0-9!#\$%&'\*\+\-\/=\?^_\`{\|}~\.]+)/i";
        $replacements[] = "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>";
    }
    function convertXCode($text, $allowimage = 1) {
        if (empty($this->mXCodePatterns)) {
            $this->mMakeXCodeConvertTable->call(new XCube_Ref($this->mXCodePatterns), new XCube_Ref($this->mXCodeReplacements));
            $this->mXCodePre->call(new XCube_Ref($this->mXCodePatterns), new XCube_Ref($this->mXCodeReplacements[0]), 0);
            $dummy = array();
            $this->mXCodePre->call(new XCube_Ref($dummy), new XCube_Ref($this->mXCodeReplacements[1]), 1);
        }
        if (empty($this->mXCodeCheckImgPatterns)) {
            $this->mMakeXCodeCheckImgPatterns->call(new XCube_Ref($this->mXCodeCheckImgPatterns));
        }
        $text = preg_replace_callback($this->mXCodeCheckImgPatterns, array($this, '_filterImgUrl'), $text);
        $replacementsIdx = ($allowimage == 0) ? 0 : 1;
        $text = preg_replace($this->mXCodePatterns, $this->mXCodeReplacements[$replacementsIdx], $text);
        return $text;
    }
    function makeXCodeCheckImgPatterns(&$patterns) {
        $patterns[] = "/\[img( align=\w+)]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";
    }
    function makeXCodeConvertTable(&$patterns, &$replacements) {
        $patterns[] = "/\[siteurl\=(['\"]?)([^\"'<>]*)\\1\](.*)\[\/siteurl\]/sU";
        $replacements[0][] = $replacements[1][] = '<a href="'.XOOPS_URL.'/\\2" target="_blank">\\3</a>';
        $patterns[] = "/\[url\=(['\"]?)(http[s]?:\/\/[^\"'<>]*)\\1\](.*)\[\/url\]/sU";
        $replacements[0][] = $replacements[1][] = '<a href="\\2" target="_blank">\\3</a>';
        $patterns[] = "/\[url\=(['\"]?)(ftp?:\/\/[^\"'<>]*)\\1\](.*)\[\/url\]/sU";
        $replacements[0][] = $replacements[1][] = '<a href="\\2" target="_blank">\\3</a>';
        $patterns[] = "/\[url\=(['\"]?)([^\"'<>]*)\\1\](.*)\[\/url\]/sU";
        $replacements[0][] = $replacements[1][] = '<a href="http:
        $patterns[] = "/\[color\=(['\"]?)([a-zA-Z0-9]*)\\1\](.*)\[\/color\]/sU";
        $replacements[0][] = $replacements[1][] = '<span style="color: #\\2;">\\3</span>';
        $patterns[] = "/\[size\=(['\"]?)([a-z-]*)\\1\](.*)\[\/size\]/sU";
        $replacements[0][] = $replacements[1][] = '<span style="font-size: \\2;">\\3</span>';
        $patterns[] = "/\[font\=(['\"]?)([^;<>\*\(\)\"']*)\\1\](.*)\[\/font\]/sU";
        $replacements[0][] = $replacements[1][] = '<span style="font-family: \\2;">\\3</span>';
        $patterns[] = "/\[email\]([^;<>\*\(\)\"']*)\[\/email\]/sU";
        $replacements[0][] = $replacements[1][] = '<a href="mailto:\\1">\\1</a>';
        $patterns[] = "/\[b\](.*)\[\/b\]/sU";
        $replacements[0][] = $replacements[1][] = '<b>\\1</b>';
        $patterns[] = "/\[i\](.*)\[\/i\]/sU";
        $replacements[0][] = $replacements[1][] = '<i>\\1</i>';
        $patterns[] = "/\[u\](.*)\[\/u\]/sU";
        $replacements[0][] = $replacements[1][] = '<u>\\1</u>';
        $patterns[] = "/\[d\](.*)\[\/d\]/sU";
        $replacements[0][] = $replacements[1][] = '<del>\\1</del>';
        $patterns[] = "/\[img align\=(['\"]?)(left|center|right)\\1\]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";
        $replacements[0][] = '<a href="\\3" target="_blank">\\3</a>';
        $replacements[1][] = '<img src="\\3" align="\\2" alt="" />';
        $patterns[] = "/\[img\]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";
        $replacements[0][] = '<a href="\\1" target="_blank">\\1</a>';
        $replacements[1][] = '<img src="\\1" alt="" />';
        $patterns[] = "/\[img align\=(['\"]?)(left|center|right)\\1 id\=(['\"]?)([0-9]*)\\3\]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";
        $replacements[0][] = '<a href="'.XOOPS_URL.'/image.php?id=\\4" target="_blank">\\5</a>';
        $replacements[1][] = '<img src="'.XOOPS_URL.'/image.php?id=\\4" align="\\2" alt="\\5" />';
        $patterns[] = "/\[img id\=(['\"]?)([0-9]*)\\1\]([^\"\(\)\?\&'<>]*)\[\/img\]/sU";
        $replacements[0][] = '<a href="'.XOOPS_URL.'/image.php?id=\\2" target="_blank">\\3</a>';
        $replacements[1][] = '<img src="'.XOOPS_URL.'/image.php?id=\\2" alt="\\3" />';
        $patterns[] = "/\[quote\]/sU";
        $replacements[0][] = $replacements[1][] = _QUOTEC.'<div class="xoopsQuote"><blockquote>';
        $patterns[] = "/\[\/quote\]/sU";
        $replacements[0][] = $replacements[1][] = '</blockquote></div>';
        $patterns[] = "/javascript:/si";
        $replacements[0][] = $replacements[1][] = "java script:";
        $patterns[] = "/about:/si";
        $replacements[0][] = $replacements[1][] = "about :";
    }
    function _filterImgUrl($matches)
    {
        if ($this->_checkUrlString($matches[2])) {
            return $matches[0];
        } else {
            return "";
        }
    }
    function _checkUrlString($text)
    {
        if (preg_match("/[\\0-\\31]/", $text)) {
            return false;
        }
        return !preg_match("/^(javascript|vbscript|about):/i", $text);
    }
    function nl2Br($text)
    {
        return preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$text);
    }
    function preConvertXCode($text, $xcode = 1) {
        if($xcode != 0){
            if (empty($this->mPreXCodePatterns)) {
                $this->mMakePreXCodeConvertTable->call(new XCube_Ref($this->mPreXCodePatterns), new XCube_Ref($this->mPreXCodeReplacements));
            }
            $text =  preg_replace($this->mPreXCodePatterns, $this->mPreXCodeReplacements, $text);
        }
        return $text;
    }
    function makePreXCodeConvertTable(&$patterns, &$replacements) {
        $patterns[] = "/\[code\](.*)\[\/code\]/esU";
        $replacements[] = "'[code]'.base64_encode('$1').'[/code]'";
    }
    function postConvertXCode($text, $xcode=1, $image=1){
        if($xcode != 0){
            if (empty($this->mPostXCodePatterns)) {
                $this->mMakePostXCodeConvertTable->call(new XCube_Ref($this->mPostXCodePatterns), new XCube_Ref($this->mPostXCodeReplacements));
            }
            $replacementsIdx = ($image == 0) ? 0 : 1;
            $text =  preg_replace($this->mPostXCodePatterns, $this->mPostXCodeReplacements[$replacementsIdx], $text);
        }
        return $text;
    }
    function makePostXCodeConvertTable(&$patterns, &$replacements) {
        $patterns[] = "/\[code\](.*)\[\/code\]/esU";
        $replacements[0][] = "'<div class=\"xoopsCode\"><pre><code>'.Legacy_TextFilter::codeSanitizer('$1', 0).'</code></pre></div>'";
        $replacements[1][] = "'<div class=\"xoopsCode\"><pre><code>'.Legacy_TextFilter::codeSanitizer('$1', 1).'</code></pre></div>'"; 
    }
    function codeSanitizer($text, $image = 1){
        return $this->convertXCode(htmlspecialchars(str_replace('\"', '"', base64_decode($text)),ENT_QUOTES), $image);
    }
}
?>
