<?php
if (!function_exists('opentable')) {
	function OpenTable($width='100%')
	{
		echo '<table width="'.$width.'" cellspacing="0" class="outer"><tr><td class="even">';
	}
}
if (!function_exists('closetable')) {
	function CloseTable()
	{
		echo '</td></tr></table>';
	}
}
if (!function_exists('themecenterposts')) {
	function themecenterposts($title, $content)
	{
		echo '<table cellpadding="4" cellspacing="1" width="98%" class="outer"><tr><td class="head">'.$title.'</td></tr><tr><td><br />'.$content.'<br /></td></tr></table>';
	}
}
?>
