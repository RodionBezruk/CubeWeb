<?php
function smarty_modifier_xoops_formattimestamp($time, $format='s')
{
	if($time && is_numeric($time)) {
		return formatTimestamp($time,$format);
	}
	return;
}
?>
