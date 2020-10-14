<?php
class XoopsForm {
    var $_action;
    var $_method;
    var $_name;
    var $_title;
    var $_elements = array();
    var $_extra;
    var $_required = array();
    function XoopsForm($title, $name, $action, $method="post", $addtoken = false){
        $this->_title = $title;
        $this->_name = $name;
        $this->_action = $action;
        $this->_method = $method;
        if ($addtoken != false) {
            $this->addElement(new XoopsFormHiddenToken());
        }
    }
    function getTitle(){
        return $this->_title;
    }
    function getName(){
        return $this->_name;
    }
    function getAction(){
        return $this->_action;
    }
    function getMethod(){
        return $this->_method;
    }
    function addElement(&$formElement, $required=false){
        if ( is_string( $formElement ) ) {
            $this->_elements[] = $formElement;
        } elseif ( is_subclass_of($formElement, 'xoopsformelement') ) {
            $this->_elements[] =& $formElement;
            if ($required) {
                if (!$formElement->isContainer()) {
                    $this->_required[] =& $formElement;
                } else {
                    $required_elements =& $formElement->getRequired();
                    $count = count($required_elements);
                    for ($i = 0 ; $i < $count; $i++) {
                        $this->_required[] =& $required_elements[$i];
                    }
                }
            }
        }
    }
    function &getElements($recurse = false){
        if (!$recurse) {
            return $this->_elements;
        } else {
            $ret = array();
            $count = count($this->_elements);
            for ($i = 0; $i < $count; $i++) {
				if (!is_object($this->_elements[$i])) {
					$ret[] = $this->_elements[$i];
				}
                if (!$this->_elements[$i]->isContainer()) {
                    $ret[] =& $this->_elements[$i];
                } else {
                    $elements =& $this->_elements[$i]->getElements(true);
                    $count2 = count($elements);
                    for ($j = 0; $j < $count2; $j++) {
                        $ret[] =& $elements[$j];
                    }
                    unset($elements);
                }
            }
            return $ret;
        }
    }
    function getElementNames()
    {
        $ret = array();
        $elements =& $this->getElements(true);
        $count = count($elements);
        for ($i = 0; $i < $count; $i++) {
            $ret[] = $elements[$i]->getName();
        }
        return $ret;
    }
    function &getElementByName($name){
        $elements =& $this->getElements(true);
        $count = count($elements);
        for ($i = 0; $i < $count; $i++) {
            if ($name == $elements[$i]->getName()) {
                return $elements[$i];
            }
        }
        $ret = false;
        return $ret;
    }
    function setElementValue($name, $value){
        $ele =& $this->getElementByName($name);
        if (is_object($ele) && method_exists($ele, 'setValue')) {
            $ele->setValue($value);
        }
    }
    function setElementValues($values){
        if (is_array($values) && !empty($values)) {
            $elements =& $this->getElements(true);
            $count = count($elements);
            for ($i = 0; $i < $count; $i++) {
                $name = $elements[$i]->getName();
                if ($name && isset($values[$name]) && method_exists($elements[$i], 'setValue')) {
                    $elements[$i]->setValue($values[$name]);
                }
            }
        }
    }
    function &getElementValue($name){
        $ele =& $this->getElementByName($name);
        if (is_object($ele) && method_exists($ele, 'getValue')) {
            return $ele->getValue($value);
        }
        $ret = null;
        return $ret;
    }
    function &getElementValues(){
        $elements =& $this->getElements(true);
        $count = count($elements);
        $values = array();
        for ($i = 0; $i < $count; $i++) {
            $name = $elements[$i]->getName();
            if ($name && method_exists($elements[$i], 'getValue')) {
                $values[$name] =& $elements[$i]->getValue();
            }
        }
        return $values;
    }
    function setExtra($extra){
        $this->_extra = " ".$extra;
    }
    function &getExtra(){
        if (isset($this->_extra)) {
            $ret =& $this->_extra;
        } else {
	    	$ret = '';
        }
        return $ret;
    }
    function setRequired(&$formElement){
        $this->_required[] =& $formElement;
    }
    function &getRequired(){
        return $this->_required;
    }
    function insertBreak($extra = null){
    }
    function render(){
    }
    function display(){
        echo $this->render();
    }
    function renderValidationJS( $withtags = true ) {
		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);
		$renderTarget =& $renderSystem->createRenderTarget();
		$renderTarget->setAttribute('legacy_module', 'legacy');
		$renderTarget->setTemplateName("legacy_xoopsform_opt_validationjs.html");
		$renderTarget->setAttribute('form', $this);
		$renderTarget->setAttribute('withtags', $withtags);
        $required =& $this->getRequired();
        $reqcount = count($required);
		$renderTarget->setAttribute('required', $required);
		$renderTarget->setAttribute('required_count', $reqcount);
		$renderSystem->render($renderTarget);
		return $renderTarget->getResult();
        $js = "";
        if ( $withtags ) {
            $js .= "\n<!-- Start Form Vaidation JavaScript 
        }
        $myts =& MyTextSanitizer::getInstance();
        $formname = $this->getName();
        $required =& $this->getRequired();
        $reqcount = count($required);
        $js .= "function xoopsFormValidate_{$formname}() {
    myform = window.document.$formname;\n";
        for ($i = 0; $i < $reqcount; $i++) {
            $eltname    = $required[$i]->getName();
            $eltcaption = trim( $required[$i]->getCaption() );
            $eltmsg = empty($eltcaption) ? sprintf( _FORM_ENTER, $eltname ) : sprintf( _FORM_ENTER, $eltcaption );
            $eltmsg = str_replace('"', '\"', stripslashes($eltmsg));
            $js .= "if ( myform.{$eltname}.value == \"\" ) "
                . "{ window.alert(\"{$eltmsg}\"); myform.{$eltname}.focus(); return false; }\n";
        }
        $js .= "return true;\n}\n";
        if ( $withtags ) {
            $js .= "
        }
        return $js;
    }
    function assign(&$tpl){
        $i = 0;
        $elements = array();
        foreach ( $this->getElements() as $ele ) {
            $n = ($ele->getName() != "") ? $ele->getName() : $i;
            $elements[$n]['name']     = $ele->getName();
            $elements[$n]['caption']  = $ele->getCaption();
            $elements[$n]['body']     = $ele->render();
            $elements[$n]['hidden']   = $ele->isHidden();
            if ($ele->getDescription() != '') {
                $elements[$n]['description']  = $ele->getDescription();
            }
            $i++;
        }
        $js = $this->renderValidationJS();
        $tpl->assign($this->getName(), array('title' => $this->getTitle(), 'name' => $this->getName(), 'action' => $this->getAction(),  'method' => $this->getMethod(), 'extra' => 'onsubmit="return xoopsFormValidate_'.$this->getName().'();"'.$this->getExtra(), 'javascript' => $js, 'elements' => $elements));
    }
}
?>
