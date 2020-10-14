<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_ROOT_PATH . "/core/XCube_ActionForm.class.php";
class User_UserAdminEditForm extends XCube_ActionForm
{
	var $_mIsNew;
	function getTokenName()
	{
		return "module.user.UserAdminEditForm.Token" . $this->get('uid');
	}
	function prepare()
	{
		$this->mFormProperties['uid'] =& new XCube_IntProperty('uid');
		$this->mFormProperties['name'] =& new XCube_StringProperty('name');
		$this->mFormProperties['uname'] =& new XCube_StringProperty('uname');
		$this->mFormProperties['email'] =& new XCube_StringProperty('email');
		$this->mFormProperties['url'] =& new XCube_StringProperty('url');
		$this->mFormProperties['user_icq'] =& new XCube_StringProperty('user_icq');
		$this->mFormProperties['user_from'] =& new XCube_StringProperty('user_from');
		$this->mFormProperties['user_sig'] =& new XCube_TextProperty('user_sig');
		$this->mFormProperties['user_viewemail'] =& new XCube_IntProperty('user_viewemail');
		$this->mFormProperties['user_aim'] =& new XCube_StringProperty('user_aim');
		$this->mFormProperties['user_yim'] =& new XCube_StringProperty('user_yim');
		$this->mFormProperties['user_msnm'] =& new XCube_StringProperty('user_msnm');
		$this->mFormProperties['pass'] =& new XCube_StringProperty('pass');
		$this->mFormProperties['vpass'] =& new XCube_StringProperty('vpass');
		$this->mFormProperties['posts'] =& new XCube_IntProperty('posts');
		$this->mFormProperties['attachsig'] =& new XCube_IntProperty('attachsig');
		$this->mFormProperties['rank'] =& new XCube_IntProperty('rank');
		$this->mFormProperties['level'] =& new XCube_IntProperty('level');
		$this->mFormProperties['timezone_offset'] =& new XCube_FloatProperty('timezone_offset');
		$this->mFormProperties['umode'] =& new XCube_StringProperty('umode');
		$this->mFormProperties['uorder'] =& new XCube_IntProperty('uorder');
		$this->mFormProperties['notify_method'] =& new XCube_IntProperty('notify_method');
		$this->mFormProperties['notify_mode'] =& new XCube_IntProperty('notify_mode');
		$this->mFormProperties['user_occ'] =& new XCube_StringProperty('user_occ');
		$this->mFormProperties['bio'] =& new XCube_TextProperty('bio');
		$this->mFormProperties['user_intrest'] =& new XCube_StringProperty('user_intrest');
		$this->mFormProperties['user_mailok'] =& new XCube_IntProperty('user_mailok');
		$this->mFormProperties['groups'] =& new XCube_IntArrayProperty('groups');
		$this->mFieldProperties['uid'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['uid']->setDependsByArray(array('required'));
		$this->mFieldProperties['uid']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_UID);
		$this->mFieldProperties['name'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['name']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['name']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_NAME, '60');
		$this->mFieldProperties['name']->addVar('maxlength', 60);
		$this->mFieldProperties['uname'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['uname']->setDependsByArray(array('required','maxlength'));
		$this->mFieldProperties['uname']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_UNAME, '25');
		$this->mFieldProperties['uname']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_UNAME, '25');
		$this->mFieldProperties['uname']->addVar('maxlength', 25);
		$this->mFieldProperties['email'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['email']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_EMAIL, '60');
		$this->mFieldProperties['email']->setDependsByArray(array('required', 'maxlength', 'email'));
		$this->mFieldProperties['email']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_EMAIL, '60');
		$this->mFieldProperties['email']->addVar('maxlength', 60);
		$this->mFieldProperties['email']->addMessage('email', _MD_USER_ERROR_EMAIL, _MD_USER_LANG_EMAIL);
		$this->mFieldProperties['url'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['url']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['url']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_URL, '100');
		$this->mFieldProperties['url']->addVar('maxlength', 100);
		$this->mFieldProperties['user_icq'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_icq']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_icq']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_ICQ, '15');
		$this->mFieldProperties['user_icq']->addVar('maxlength', 15);
		$this->mFieldProperties['user_from'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_from']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_from']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_FROM, '100');
		$this->mFieldProperties['user_from']->addVar('maxlength', 100);
		$this->mFieldProperties['user_aim'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_aim']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_aim']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_AIM, '18');
		$this->mFieldProperties['user_aim']->addVar('maxlength', 18);
		$this->mFieldProperties['user_yim'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_yim']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_yim']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_YIM, '25');
		$this->mFieldProperties['user_yim']->addVar('maxlength', 25);
		$this->mFieldProperties['user_msnm'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_msnm']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_msnm']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_MSNM, '100');
		$this->mFieldProperties['user_msnm']->addVar('maxlength', 100);
		$this->mFieldProperties['pass'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['pass']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['pass']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_PASS, '32');
		$this->mFieldProperties['pass']->addVar('maxlength', 32);
		$this->mFieldProperties['vpass'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['vpass']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['vpass']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_PASS, '32');
		$this->mFieldProperties['vpass']->addVar('maxlength', 32);
		$this->mFieldProperties['posts'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['posts']->setDependsByArray(array('required'));
		$this->mFieldProperties['posts']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_POSTS);
		$this->mFieldProperties['rank'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['rank']->setDependsByArray(array('required'));
		$this->mFieldProperties['rank']->addMessage('required', _MD_USER_ERROR_REQUIRED, _AD_USER_LANG_RANK);
		$this->mFieldProperties['level'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['level']->setDependsByArray(array('required'));
		$this->mFieldProperties['level']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_LEVEL);
		$this->mFieldProperties['timezone_offset'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['timezone_offset']->setDependsByArray(array('required'));
		$this->mFieldProperties['timezone_offset']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_TIMEZONE_OFFSET);
		$this->mFieldProperties['umode'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['umode']->setDependsByArray(array('required'));
		$this->mFieldProperties['umode']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_UMODE);
		$this->mFieldProperties['uorder'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['uorder']->setDependsByArray(array('required','intRange'));
		$this->mFieldProperties['uorder']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_UORDER);
		$this->mFieldProperties['uorder']->addMessage('intRange', _MD_USER_ERROR_INJURY, _MD_USER_LANG_UORDER);
		$this->mFieldProperties['uorder']->addVar('min', 0);
		$this->mFieldProperties['uorder']->addVar('max', 1);
		$this->mFieldProperties['notify_method'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['notify_method']->setDependsByArray(array('required','intRange'));
		$this->mFieldProperties['notify_method']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_NOTIFY_METHOD);
		$this->mFieldProperties['notify_method']->addMessage('intRange', _MD_USER_ERROR_INJURY, _MD_USER_LANG_NOTIFY_METHOD);
		$this->mFieldProperties['notify_method']->addVar('min', 0);
		$this->mFieldProperties['notify_method']->addVar('max', 2);
		$this->mFieldProperties['notify_mode'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['notify_mode']->setDependsByArray(array('required','intRange'));
		$this->mFieldProperties['notify_mode']->addMessage('required', _MD_USER_ERROR_REQUIRED, _MD_USER_LANG_NOTIFY_MODE);
		$this->mFieldProperties['notify_mode']->addMessage('intRange', _MD_USER_ERROR_INJURY, _MD_USER_LANG_NOTIFY_MODE);
		$this->mFieldProperties['notify_mode']->addVar('min', 0);
		$this->mFieldProperties['notify_mode']->addVar('max', 2);
		$this->mFieldProperties['user_occ'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_occ']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_occ']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_OCC, '100');
		$this->mFieldProperties['user_occ']->addVar('maxlength', 100);
		$this->mFieldProperties['user_intrest'] =& new XCube_FieldProperty($this);
		$this->mFieldProperties['user_intrest']->setDependsByArray(array('maxlength'));
		$this->mFieldProperties['user_intrest']->addMessage('maxlength', _MD_USER_ERROR_MAXLENGTH, _MD_USER_LANG_USER_INTREST, '150');
		$this->mFieldProperties['user_intrest']->addVar('maxlength', 150);
	}
	function validateUname()
	{
		if ($this->get('uname') != null) {
			$handler =& xoops_gethandler('user');
			$criteria =& new CriteriaCompo(new Criteria('uname', $this->get('uname')));
			if ($this->get('uid')) {
				$criteria->add(new Criteria('uid', $this->get('uid'), '<>'));
			}
			if ($handler->getCount($criteria) > 0) {
				$this->addErrorMessage(_AD_USER_ERROR_UNAME_NO_UNIQUE);
			}
		}
	}
	function validateEmail()
	{
		if (strlen($this->get('email')) > 0) {
			$userHandler=&xoops_gethandler('user');
			$criteria =& new CriteriaCompo(new Criteria('email', $this->get('email')));
			if ($this->get('uid') > 0) {
				$criteria->add(new Criteria('uid', $this->get('uid'), '<>'));
			}
			if ($userHandler->getCount($criteria) > 0) {
				$this->addErrorMessage(_MD_USER_ERROR_EMAILTAKEN);
			}
		}
	}
	function validateUrl()
	{
		$t_url = $this->get('url');
		if (strlen($t_url) > 0) {
			if (!preg_match('/^https?(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $t_url)) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_MD_USER_ERROR_INJURY, _MD_USER_LANG_URL));
			}
		}
	}
	function validateGroups()
	{
		$groupHandler =& xoops_gethandler('group');
		foreach ($this->get('groups') as $gid) {
			$group =& $groupHandler->get($gid);
			if (!is_object($group)) {
				$this->addErrorMessage(_AD_USER_ERROR_GROUP_VALUE);
			}
		}
	}
	function validatePass()
	{
		if (strlen($this->get('pass'))) {
			if ($this->get('pass') != $this->get('vpass')) {
				$this->addErrorMessage(_MD_USER_ERROR_PASSWORD);
				$this->set('pass', '');
				$this->set('vpass', '');
			}
		}
		elseif($this->_mIsNew) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_MD_USER_ERROR_REQUIRED, _MD_USER_LANG_PASS));
		}
	}
	function validateRank()
	{
		$t_rank = $this->get('rank');
		if ($t_rank > 0) {
			$handler =& xoops_getmodulehandler('ranks', 'user');
			$rank =& $handler->get($t_rank);
			if (!is_object($rank)) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_MD_USER_ERROR_INJURY, _AD_USER_LANG_RANK));
			}
			elseif ($rank->get('rank_special') != 1) {
				$this->addErrorMessage(XCube_Utils::formatMessage(_MD_USER_ERROR_INJURY, _AD_USER_LANG_RANK));
			}
		}
	}
	function validateUmode()
	{
		if (!in_array($this->get('umode'), array('nest', 'flat', 'thread'))) {
			$this->addErrorMessage(_AD_USER_ERROR_UMODE);
		}
	}
	function load(&$obj)
	{
		$this->set('uid', $obj->get('uid'));
		$this->set('name', $obj->get('name'));
		$this->set('uname', $obj->get('uname'));
		$this->set('email', $obj->get('email'));
		$this->set('url', $obj->get('url'));
		$this->set('user_icq', $obj->get('user_icq'));
		$this->set('user_from', $obj->get('user_from'));
		$this->set('user_sig', $obj->get('user_sig'));
		$this->set('user_viewemail', $obj->get('user_viewemail'));
		$this->set('user_aim', $obj->get('user_aim'));
		$this->set('user_yim', $obj->get('user_yim'));
		$this->set('user_msnm', $obj->get('user_msnm'));
		$this->set('posts', $obj->get('posts'));
		$this->set('attachsig', $obj->get('attachsig'));
		$this->set('rank', $obj->get('rank'));
		$this->set('level', $obj->get('level'));
		$this->set('timezone_offset', $obj->get('timezone_offset'));
		$this->set('umode', $obj->get('umode'));
		$this->set('uorder', $obj->get('uorder'));
		$this->set('notify_method', $obj->get('notify_method'));
		$this->set('notify_mode', $obj->get('notify_mode'));
		$this->set('user_occ', $obj->get('user_occ'));
		$this->set('bio', $obj->get('bio'));
		$this->set('user_intrest', $obj->get('user_intrest'));
		$this->set('user_mailok', $obj->get('user_mailok'));
		$this->_mIsNew = $obj->isNew();
		$groups = $obj->getGroups();
		if ($this->_mIsNew) {
			$this->set('groups', 0, XOOPS_GROUP_USERS);
		}
		else {
			$i = 0;
			foreach ($groups as $gid) {
				$this->set('groups', $i++, $gid);
			}
		}
	}
	function update(&$obj)
	{
		$obj->set('uid', $this->get('uid'));
		$obj->set('name', $this->get('name'));
		$obj->set('uname', $this->get('uname'));
		$obj->set('email', $this->get('email'));
		$obj->set('url', $this->get('url'));
		$obj->set('user_icq', $this->get('user_icq'));
		$obj->set('user_from', $this->get('user_from'));
		$obj->set('user_sig', $this->get('user_sig'));
		$obj->set('user_viewemail', $this->get('user_viewemail'));
		$obj->set('user_aim', $this->get('user_aim'));
		$obj->set('user_yim', $this->get('user_yim'));
		$obj->set('user_msnm', $this->get('user_msnm'));
		if (strlen($this->get('pass'))) {
			$obj->set('pass', md5($this->get('pass')));
		}
		$obj->set('posts', $this->get('posts'));
		$obj->set('attachsig', $this->get('attachsig'));
		$obj->set('rank', $this->get('rank'));
		$obj->set('level', $this->get('level'));
		$obj->set('timezone_offset', $this->get('timezone_offset'));
		$obj->set('umode', $this->get('umode'));
		$obj->set('uorder', $this->get('uorder'));
		$obj->set('notify_method', $this->get('notify_method'));
		$obj->set('notify_mode', $this->get('notify_mode'));
		$obj->set('user_occ', $this->get('user_occ'));
		$obj->set('bio', $this->get('bio'));
		$obj->set('user_intrest', $this->get('user_intrest'));
		$obj->set('user_mailok', $this->get('user_mailok'));
		$obj->Groups = array();
		$groups = $this->get('groups');
		foreach ($groups as $gid) {
			$obj->Groups[] = $gid;
		}
	}
}
?>
