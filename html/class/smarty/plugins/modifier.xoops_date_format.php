<?php
function smarty_modifier_xoops_date_format($time, $format="%b %e, %Y")
{
	if($time && is_numeric($time)) {
		return strftime ( $format, xoops_getUserTimestamp ( $time ) );
	}
	return;
}
?>
