<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class XoopsMailerLocal extends XoopsMailer {
    function XoopsMailerLocal(){
        $this->multimailer = new XoopsMultiMailerLocal();
        $this->reset();
        $this->charSet = 'iso-2022-jp';
        $this->encoding = '7bit';
				$this->multimailer->CharSet = $this->charSet;
				$this->multimailer->SetLanguage('ja', XOOPS_ROOT_PATH . '/class/mail/phpmailer/language/');
				$this->multimailer->Encoding = "7bit";
    }
    function encodeFromName($text){
        return $this->STRtoJIS($text,_CHARSET);
    }
    function encodeSubject($text){
        if ($this->multimailer->needs_encode) {
            return $this->STRtoJIS($text,_CHARSET);
        } else {
            return $text;
        }
    }
    function encodeBody(&$text){
        if ($this->multimailer->needs_encode) {
            $text = $this->STRtoJIS($text,_CHARSET);
        }
    }
    function STRtoJIS($str, $from_charset){
        if (function_exists('mb_convert_encoding')) { 
            $str_JIS  = mb_convert_encoding(mb_convert_kana($str,"KV", $from_charset), "JIS", $from_charset);
        } else if ($from_charset=='EUC-JP') {
            $str_JIS = '';
            $mode = 0;
            $b = unpack("C*", $str);
            $n = count($b);
            for ($i = 1; $i <= $n; $i++) {
                if ($b[$i] == 0x8E) {
                    if ($mode != 2) {
                        $mode = 2;
                        $str_JIS .= pack("CCC", 0x1B, 0x28, 0x49);
                    }
                    $b[$i+1] -= 0x80;
                    $str_JIS .= pack("C", $b[$i+1]);
                    $i++;
                } elseif ($b[$i] > 0x8E) {
                    if ($mode != 1){
                        $mode = 1;
                        $str_JIS .= pack("CCC", 0x1B, 0x24, 0x42);
                    }
                    $b[$i] -= 0x80; $b[$i+1] -= 0x80;
                    $str_JIS .= pack("CC", $b[$i], $b[$i+1]);
                    $i++;
                } else {
                    if ($mode != 0) {
                        $mode = 0;
                        $str_JIS .= pack("CCC", 0x1B, 0x28, 0x42);
                    }
                    $str_JIS .= pack("C", $b[$i]);
                }
            }
            if ($mode != 0) $str_JIS .= pack("CCC", 0x1B, 0x28, 0x42);
        }
        return $str_JIS;
    }
}
class XoopsMultiMailerLocal extends XoopsMultiMailer {
    var $needs_encode;
    function XoopsMultiMailerLocal() {
        $this->XoopsMultiMailer();
        $this->needs_encode = true;
        if (function_exists('mb_convert_encoding')) {
            $mb_overload = ini_get('mbstring.func_overload');
            if (($this->Mailer == 'mail') && (intval($mb_overload) & 1)) { 
                $this->needs_encode = false;
                $this->mail_overload = true;
            }
        }
    }
    function AddrFormat($addr) {
        if(empty($addr[1])) {
            $formatted = $addr[0];
        } else {
            $formatted = $this->EncodeHeader($addr[1], 'text') . " <" . 
                         $addr[0] . ">";
        }
        return $formatted;
    }
    function EncodeHeader ($str, $position = 'text', $force=false) {
        if (!preg_match('/^4\.4\.[01]([^0-9]+|$)/',PHP_VERSION)) {
            if (function_exists('mb_convert_encoding')) { 
                if ($this->needs_encode || $force) {
                    $encoded = mb_convert_encoding($str, _CHARSET, mb_detect_encoding($str));
                    $encoded = mb_encode_mimeheader($encoded, "ISO-2022-JP", "B", "\n");
                } else {
                    $encoded = $str;
                }
            } else {
                $encoded = parent::EncodeHeader($str, $position);
            }
            return $encoded;
        } else {
            $encode_charset = strtoupper($this->CharSet);
            if (function_exists('mb_convert_encoding')) { 
                if ($this->needs_encode || $force) {
                	$str_encoding = mb_detect_encoding($str, 'ASCII,'.$encode_charset );
                    if ($str_encoding == 'ASCII') { 
                        return $str;
                    } else if ($str_encoding != $encode_charset) { 
                        $str = mb_convert_encoding($str, $encode_charset, $str_encoding);
                    }
                    $cut_start = 0;
                    $encoded ='';
                    $cut_length = floor((76-strlen('Subject: =?'.$encode_charset.'?B?'.'?='))/4)*3;
                    while($cut_start < strlen($str)) {
                        $partstr = mb_strcut ( $str, $cut_start, $cut_length, $encode_charset);
                        $partstr_length = strlen($partstr);
                        if (!$partstr_length) break;
                        if ($encode_charset == 'ISO-2022-JP') { 
                            if ((substr($partstr, 0, 3)===chr(27).'$B') 
                              && (substr($str, $cut_start, 3) !== chr(27).'$B')) {
                                $partstr_length -= 3;
                            }
                            if ((substr($partstr,-3)===chr(27).'(B') 
                              && (substr($str, $cut_start+$partstr_length-3, 3) !== chr(27).'(B')) {
                                $partstr_length -= 3;
                            }
                        }
                        if ($cut_start) $encoded .= "\r\n\t";
                        $encoded .= '=?' . $encode_charset . '?B?' . base64_encode($partstr) . '?=';
                        $cut_start += $partstr_length;
                    }
                } else {
                    $encoded = $str;
                }
            } else {
                $encoded = parent::EncodeHeader($str, $position);
            }
            return $encoded;
        }
    }
}
?>
