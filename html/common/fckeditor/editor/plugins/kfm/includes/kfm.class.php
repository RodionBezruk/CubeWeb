<?php
class kfmBase extends kfmObject{
	var $doctype='<!DOCTYPE html PUBLIC "-
	var $settings=array();
	function setting($name,$value='novaluegiven'){
		if($value=='novaluegiven'){
			if(!isset($this->settings[$name]))return $this->error('Setting '.$name.' does not exists');
			return $this->settings[$name];
		}
		$this->settings[$name]=$value;
	}
	function defaultSetting($name, $value){
		if(!isset($this->settings[$name]))$this->settings[$name]=$value;
	}
	function getParameter($parameter, $default=false){
	}
	function setParameter($parameter, $value){
	}
}
$kfm=new kfmBase();
