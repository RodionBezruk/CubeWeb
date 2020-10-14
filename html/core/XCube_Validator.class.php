<?php
class XCube_Validator
{
	function isValid(&$form, $vars)
	{
	}
}
class XCube_RequiredValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		return !$form->isNull();
	}
}
class XCube_MinlengthValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return strlen($form->toString()) >= $vars['minlength'];
		}
	}
}
class XCube_MaxlengthValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return strlen($form->toString()) <= $vars['maxlength'];
		}
	}
}
class XCube_MinValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return $form->toNumber() >= $vars['min'];
		}
	}
}
class XCube_MaxValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return $form->toNumber() <= $vars['max'];
		}
	}
}
class XCube_IntRangeValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return (intval($form->toNumber()) >= $vars['min'] && intval($form->toNumber()) <= $vars['max']);
		}
	}
}
class XCube_EmailValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return preg_match("/^[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+(\.[_a-z0-9\-+!#$%&'*\/=?^`{|}~]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $form->toString());
		}
	}
}
class XCube_MaskValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			return preg_match($vars['mask'], $form->toString());
		}
	}
}
class XCube_ExtensionValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			if (!is_a($form, "XCube_FileProperty")) {
				return true;
			}
			$extArr = explode(",", $vars['extension']);
			foreach ($extArr as $ext) {
				if (strtolower($form->mValue->getExtension()) == strtolower($ext)) {
					return true;
				}
			}
			return false;
		}
	}
}
class XCube_MaxfilesizeValidator extends XCube_Validator
{
	function isValid(&$form, $vars)
	{
		if ($form->isNull()) {
			return true;
		}
		else {
			if (!is_a($form, "XCube_FileProperty")) {
				return true;
			}
			return ($form->mValue->getFileSize() <= $vars['maxfilesize']);
		}
	}
}
?>
