<?php
function smarty_modifier_xoops_formattimestampGMT($time, $format='s')
{
	if($time && is_numeric($time)) {
		return formattimestampGMT($time, $format);
	}
	return;
}
?>
