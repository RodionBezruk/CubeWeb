<?php
class mainfile_manager {
    var $path = '../mainfile.php';
    var $distfile = '../mainfile.dist.php';
    var $rewrite = array();
    var $report =  array();
    var $error = false;
    function mainfile_manager(){
    }
    function setRewrite($def, $val){
        $this->rewrite[$def] = $val;
    }
    function copyDistFile(){
        if ( ! copy($this->distfile, $this->path) ) {
            $this->report[] = _NGIMG.sprintf(_INSTALL_L126, '<b>'.$this->path.'</b>');
            $this->error = true;
            return false;
        }
        $this->report[] = _OKIMG.sprintf(_INSTALL_L125, '<b>'.$this->path.'</b>', '<b>'.$this->distfile.'</b>');
        return true;
    }
    function doRewrite(){
        if ( ! $file = fopen($this->path,"r") ) {
            $this->error = true;
            return false;
        }
        clearstatcache();
        $content = fread($file, filesize($this->path) );
        fclose($file);
        foreach($this->rewrite as $key => $val){
            if(is_int($val) &&
             preg_match("/(define\()([\"'])(".$key.")\\2,\s*([0-9]+)\s*\)/",$content)){
                $content = preg_replace("/(define\()([\"'])(".$key.")\\2,\s*([0-9]+)\s*\)/"
                , "define('".$key."', ".$val.")"
                , $content);
                $this->report[] = _OKIMG.sprintf(_INSTALL_L121, "<b>$key</b>", $val);
            }
            elseif(preg_match("/(define\()([\"'])(".$key.")\\2,\s*([\"'])(.*?)\\4\s*\)/",$content)){
                $content = preg_replace("/(define\()([\"'])(".$key.")\\2,\s*([\"'])(.*?)\\4\s*\)/"
                , "define('".$key."', '".addslashes($val)."')"
                , $content);
                $this->report[] = _OKIMG.sprintf(_INSTALL_L121, '<b>'.$key.'</b>', $val);
            }else{
                $this->error = true;
                $this->report[] = _NGIMG.sprintf(_INSTALL_L122, '<b>'.$val.'</b>');
            }
        }
        if ( !$file = fopen($this->path,"w") ) {
            $this->error = true;
            return false;
        }
        if ( fwrite($file,$content) == -1 ) {
            fclose($file);
            $this->error = true;
            return false;
        }
        fclose($file);
        return true;
    }
    function report(){
        return $this->report;
    }
    function error(){
        return $this->error;
    }
}
?>
