<?php
class cache_manager {
    var $s_files = array();
    var $f_files = array();
    function write($file, $source){
        if (false != $fp = fopen(XOOPS_CACHE_PATH.'/'.$file, 'w')) {
            fwrite($fp, $source);
            fclose($fp);
            $this->s_files[] = $file;
        }else{
            $this->f_files[] = $file;
        }
    }
    function report(){
        $reports = array();
        foreach($this->s_files as $val){
            $reports[]= _OKIMG.sprintf(_INSTALL_L123, "<b>$val</b>");
        }
        foreach($this->f_files as $val){
            $reports[] = _NGIMG.sprintf(_INSTALL_L124, "<b>$val</b>");
        }
        return $reports;
    }
}
?>
