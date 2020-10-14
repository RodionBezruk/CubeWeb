<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
class Legacy_SessionCallback extends XCube_ActionFilter
{
	function preBlockFilter()
	{
		$this->mRoot->mDelegateManager->add('XCube_Session.SetupSessionHandler', 'Legacy_SessionCallback::setupSessionHandler');
		$this->mRoot->mDelegateManager->add('XCube_Session.GetSessionCookiePath', 'Legacy_SessionCallback::getSessionCookiePath');
	}
	function setupSessionHandler()
	{
		$sessionHandler =& xoops_gethandler('session');
		session_set_save_handler(
			array(&$sessionHandler, 'open'),
			array(&$sessionHandler, 'close'),
			array(&$sessionHandler, 'read'),
			array(&$sessionHandler, 'write'),
			array(&$sessionHandler, 'destroy'),
			array(&$sessionHandler, 'gc'));
	}
	function getSessionCookiePath(&$cookiePath) {
		$parse_array = parse_url(XOOPS_URL);
		$cookiePath = @$parse_array['path'].'/';
	}
}
