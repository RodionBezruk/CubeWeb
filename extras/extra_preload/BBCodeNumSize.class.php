<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class BBCodeNumSize extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		$this->mRoot->mTextFilter->mMakeXCodeConvertTable->add(array(&$this, 'bbcode'), XCUBE_DELEGATE_PRIORITY_1);
	}
	function bbcode(&$patterns, &$replacements)
	{
		$patterns[] = "/\[size=(['\"]?)([a-z0-9-]*)\\1](.*)\[\/size\]/sU";
		$replacements[0][] = $replacements[1][] = '<span style="font-size: \\2;">\\3</span>';
	}
}
?>
