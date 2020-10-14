<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_IPbanningFilter extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		if ($this->mRoot->mContext->getXoopsConfig('enable_badips')) {
			if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
				foreach ($this->mRoot->mContext->mXoopsConfig['bad_ips'] as $bi) {
					$bi = str_replace('.', '\.', $bi);
					if (!empty($bi) && preg_match("/".$bi."/", $_SERVER['REMOTE_ADDR'])) {
						die();
					}
				}
			}
		}
	}
}
?>
