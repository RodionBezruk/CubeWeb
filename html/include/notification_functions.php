<?php
function notificationEnabled ($style, $module_id=null)
{
	if (isset($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
		$status = $GLOBALS['xoopsModuleConfig']['notification_enabled'];
	} else {
		if (!isset($module_id)) {
			return false;
		}
		$module_handler =& xoops_gethandler('module');
		$module =& $module_handler->get($module_id);
		if (!empty($module) && $module->getVar('hasnotification') == 1) {
			$config_handler =& xoops_gethandler('config');
			$config = $config_handler->getConfigsByCat(0,$module_id);
			$status = $config['notification_enabled'];
		} else {
			return false;
		}
	}
	include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
	if (($style == 'block') && ($status == XOOPS_NOTIFICATION_ENABLEBLOCK || $status == XOOPS_NOTIFICATION_ENABLEBOTH)) {
		return true;
	}
	if (($style == 'inline') && ($status == XOOPS_NOTIFICATION_ENABLEINLINE || $status == XOOPS_NOTIFICATION_ENABLEBOTH)) {
		return true;
	}
	return false;
}
function &notificationCategoryInfo ($category_name = null, $module_id = null)
{
	if (!isset($module_id)) {
		global $xoopsModule;
		$module_id = !empty($xoopsModule) ? $xoopsModule->getVar('mid') : 0;
		$module =& $xoopsModule;
	} else {
		$module_handler =& xoops_gethandler('module');
		$module =& $module_handler->get($module_id);
	}
	if (!is_object($module)) {
		$ret = false;
		return $ret;
	}
	$not_config =& $module->getInfo('notification');
	if ($category_name == null) {
		return $not_config['category'];
	}
	foreach ($not_config['category'] as $category) {
		if ($category['name'] == $category_name) {
			return $category;
		}
	}
	$ret = false;
	return $ret;
}
function &notificationCommentCategoryInfo($module_id=null)
{
	$all_categories =& notificationCategoryInfo ('', $module_id);
	if (empty($all_categories)) {
		return false;
	}
	foreach ($all_categories as $category) {
		$all_events =& notificationEvents ($category['name'], false, $module_id);
		if (empty($all_events)) {
			continue;
		}
		foreach ($all_events as $event) {
			if ($event['name'] == 'comment') {
				return $category;
			}
		}
	}
	$ret = false;
	return $ret;
}
function &notificationEvents ($category_name, $enabled_only, $module_id=null)
{
	if (!isset($module_id)) {
		global $xoopsModule;
		$module_id = !empty($xoopsModule) ? $xoopsModule->getVar('mid') : 0;
		$module =& $xoopsModule;
	} else {
		$module_handler =& xoops_gethandler('module');
		$module =& $module_handler->get($module_id);
	}
	if (!is_object($module)) {
		$ret = false;
		return $ret;
	}
	$not_config =& $module->getInfo('notification');
	$config_handler =& xoops_gethandler('config');
	$mod_config = $config_handler->getConfigsByCat(0,$module_id);
	$category =& notificationCategoryInfo($category_name, $module_id);
	global $xoopsConfig;
	$event_array = array();
	$override_comment = false;
	$override_commentsubmit = false;
	$override_bookmark = false;
	foreach ($not_config['event'] as $event) {
		if ($event['category'] == $category_name) {
			$event['mail_template_dir'] = XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/mail_template/';
			if (!$enabled_only || notificationEventEnabled ($category, $event, $module)) {
				$event_array[] = $event;
			}
			if ($event['name'] == 'comment') {
				$override_comment = true;
			}
			if ($event['name'] == 'comment_submit') {
				$override_commentsubmit = true;
			}
			if ($event['name'] == 'bookmark') {
				$override_bookmark = true;
			}
		}
	}
	$root =& XCube_Root::getSingleton();
	$root->mLanguageManager->loadPageTypeMessageCatalog('notification');
	if ($module->getVar('hascomments')) {
		$com_config = $module->getInfo('comments');
		if (!empty($category['item_name']) && $category['item_name'] == $com_config['itemName']) {
			$mail_template_dir = XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/mail_template/';
			include_once XOOPS_ROOT_PATH . '/include/comment_constants.php';
			$config_handler =& xoops_gethandler('config');
			$com_config = $config_handler->getConfigsByCat(0,$module_id);
			if (!$enabled_only) {
				$insert_comment = true;
				$insert_submit = true;
			} else {
				$insert_comment = false;
				$insert_submit = false;
				switch($com_config['com_rule']) {
				case XOOPS_COMMENT_APPROVENONE:
					break;
				case XOOPS_COMMENT_APPROVEALL:
					if (!$override_comment) {
						$insert_comment = true;
					}
					break;
				case XOOPS_COMMENT_APPROVEUSER:
				case XOOPS_COMMENT_APPROVEADMIN:
					if (!$override_comment) {
						$insert_comment = true;
					}
					if (!$override_commentsubmit) {
						$insert_submit = true;
					}
					break;
				}
			}
			if ($insert_comment) {
				$event = array ('name'=>'comment', 'category'=>$category['name'], 'title'=>_NOT_COMMENT_NOTIFY, 'caption'=>_NOT_COMMENT_NOTIFYCAP, 'description'=>_NOT_COMMENT_NOTIFYDSC, 'mail_template_dir'=>$mail_template_dir, 'mail_template'=>'comment_notify', 'mail_subject'=>_NOT_COMMENT_NOTIFYSBJ);
				if (!$enabled_only || notificationEventEnabled($category, $event, $module)) {
					$event_array[] = $event;
				}
			}
			if ($insert_submit) {
				$event = array ('name'=>'comment_submit', 'category'=>$category['name'], 'title'=>_NOT_COMMENTSUBMIT_NOTIFY, 'caption'=>_NOT_COMMENTSUBMIT_NOTIFYCAP, 'description'=>_NOT_COMMENTSUBMIT_NOTIFYDSC, 'mail_template_dir'=>$mail_template_dir, 'mail_template'=>'commentsubmit_notify', 'mail_subject'=>_NOT_COMMENTSUBMIT_NOTIFYSBJ, 'admin_only'=>1);
				if (!$enabled_only || notificationEventEnabled($category, $event, $module)) {
					$event_array[] = $event;
				}
			}
		}
	}
	if (!empty($category['allow_bookmark'])) {
		if (!$override_bookmark) {
			$event = array ('name'=>'bookmark', 'category'=>$category['name'], 'title'=>_NOT_BOOKMARK_NOTIFY, 'caption'=>_NOT_BOOKMARK_NOTIFYCAP, 'description'=>_NOT_BOOKMARK_NOTIFYDSC);
			if (!$enabled_only || notificationEventEnabled($category, $event, $module)) {
				$event_array[] = $event;
			}
		}	
	}
	return $event_array;
}
function notificationEventEnabled (&$category, &$event, &$module)
{
	$config_handler =& xoops_gethandler('config');
	$mod_config = $config_handler->getConfigsByCat(0,$module->getVar('mid'));
	$option_name = notificationGenerateConfig ($category, $event, 'option_name');
	if (is_array($mod_config['notification_events']) && in_array($option_name, $mod_config['notification_events'])) {
		return true;
	}
	$notification_handler =& xoops_gethandler('notification');
	return false;
}
function &notificationEventInfo ($category_name, $event_name, $module_id=null)
{
	$all_events =& notificationEvents ($category_name, false, $module_id);
	if (is_array($all_events)) {
		foreach ($all_events as $event) {
			if ($event['name'] == $event_name) {
				return $event;
			}
		}
	}
	$ret = false;
	return $ret;
}
function &notificationSubscribableCategoryInfo ($module_id=null)
{
	$all_categories =& notificationCategoryInfo ('', $module_id);
	$script_url = explode('/', xoops_getenv('PHP_SELF'));
	$script_name = $script_url[count($script_url)-1];
	$sub_categories = array();
	foreach ($all_categories as $category) {
		$subscribe_from = $category['subscribe_from'];
		if (!is_array($subscribe_from)) {
			if ($subscribe_from == '*') {
				$subscribe_from = array($script_name);
			} else {
				$subscribe_from = array($subscribe_from);
			}
		}
		if (!in_array($script_name, $subscribe_from)) {
			continue;
		}	
		if (empty($category['item_name'])) {
			$category['item_name'] = '';
			$category['item_id'] = 0;
			$sub_categories[] = $category;
		} else {
			$item_name = $category['item_name'];
			$id = ($item_name != '' && isset($_GET[$item_name])) ? intval($_GET[$item_name]) : 0;
			if ($id > 0)  {
				$category['item_id'] = $id;
				$sub_categories[] = $category;
			}
		}
	}
	return $sub_categories;
}
function notificationGenerateConfig (&$category, &$event, $type)
{
	switch ($type) {
	case 'option_value':
	case 'name':
		return 'notify:' . $category['name'] . '-' . $event['name'];
		break;
	case 'option_name':
		return $category['name'] . '-' . $event['name'];
		break;
	default:
		return false;
		break;
	}
}
?>
