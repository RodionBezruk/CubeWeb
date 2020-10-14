<?php
class XCube_TextFilter
{
    var $mDummy=null;  
    function getInstance(&$instance) {
       if (empty($instance)) {
            $instance = new XCube_TextFilter();
        }
    }
    function toShow($str) {
        return htmlspecialchars($str, ENT_QUOTES);
    }
    function toEdit($str) {
        return htmlspecialchars($str, ENT_QUOTES);
    }
}
?>
