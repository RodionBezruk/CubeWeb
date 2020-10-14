<?php
class XCube_Utils
{
	function XCube_Utils()
	{
	}
	function redirectHeader($url, $time, $messages = null)
	{
		$root =& XCube_Root::getSingleton();
		$root->mController->executeRedirect($url, $time, $messages);
	}
	function formatString()
	{
		$arr = func_get_args();
		if(count($arr)==0)
			return null;
		$message = $arr[0];
		$variables = array();
		if (is_array($arr[1])) {
			$variables = $arr[1];
		}
		else {
			$variables = $arr;
			array_shift($variables);
		}
		for ($i = 0; $i < count($variables); $i++) {
			$message = str_replace("{" . ($i) . "}", $variables[$i], $message);
			$message = str_replace("{" . ($i) . ":ucFirst}", ucfirst($variables[$i]), $message);
			$message = str_replace("{" . ($i) . ":toLower}", strtolower($variables[$i]), $message);
			$message = str_replace("{" . ($i) . ":toUpper}", strtoupper($variables[$i]), $message);
		}
		return $message;
	}
	function formatMessage()
	{
		$arr = func_get_args();
		if (count($arr) == 0) {
			return null;
		}
		else if (count($arr) == 1) {
			return XCube_Utils::formatString($arr[0]);
		}
		else if (count($arr) > 1) {
			$vals = $arr;
			array_shift($vals);
			return XCube_Utils::formatString($arr[0], $vals);
		}
	}
	function formatMessageByMap($subject,$arr)
	{
		$searches=array();
		$replaces=array();
		foreach($arr as $key=>$value) {
			$searches[]="{".$key."}";
			$replaces[]=$value;
		}
		return str_replace($searches,$replaces,$subject);
	}
}
?>
