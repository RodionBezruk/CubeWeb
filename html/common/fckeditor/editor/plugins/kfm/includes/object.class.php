<?php
function kfm_error($message,$level=3){
	global $kfm_errors;
	$kfm_errors[]=array('message'=>$message,'level'=>$level);
	return false;
}
function kfm_isError($level=3){
	global $kfm_errors;
	foreach($kfm_errors as $error) if($error->level<=$level)return true;
	return false;
}
function kfm_getErrors($level=3){
	global $kfm_errors;
	return $kfm_errors;
}
function kfm_addMessage($message){
	global $kfm_messages;
	$kfm_messages[]=array('message'=>$message);
}
function kfm_getMessages(){
	global $kfm_messages;
	return $kfm_messages;
}
class kfmObject{
	var $error_array = array();
	function __construct(){
		$this->kfmObject();
	}
	function kfmObject(){
	}
	function error($message, $level=3){
		global $kfm_errors;
		$info=array('function'=>'','class'=>'','file'=>'');
		$trace=debug_backtrace();
		$previous_level=array_shift($trace);
		foreach($trace as $errorlevel){
			if(!isset($errorlevel['class'])){
				$info=$previous_level;
				break;
			}
			$previous_level=$errorlevel;
		}
		$error=array(
			'message'=>$message, 
			'level'=>$level,
			'function'=>$info['function'],
			'class'=>$info['class'],
			'file'=>$info['file']);
		$this->error_array[] = $message;
		$kfm_errors[]=$error;
		return false;
	}
	function hasErrors(){
		if(count($this->error_array)) return true;
		return false;
	}
	function getErrors(){
		return 'error: '.implode("_", $this->error_array);
	}
	function addErrors($object){
		array_merge_recursive($this->error_array, $object->error_array);
	}
	function checkAddr($addr){
		return (
			strpos($addr,'..')===false&&
			strpos($addr,'.')!==0&&
			strpos($addr,'/.')===false&&
			!in_array(preg_replace('/.*\./','',$addr),$GLOBALS['kfm_banned_extensions'])
		);
	}
}
?>
