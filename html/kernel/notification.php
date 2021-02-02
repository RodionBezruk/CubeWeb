<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
include_once XOOPS_ROOT_PATH . '/include/notification_functions.php';
class XoopsNotification extends XoopsObject
{
    function XoopsNotification()
    {
        $this->XoopsObject();
		$this->initVar('not_id', XOBJ_DTYPE_INT, NULL, false);
		$this->initVar('not_modid', XOBJ_DTYPE_INT, NULL, false);
		$this->initVar('not_category', XOBJ_DTYPE_TXTBOX, null, false, 30);
		$this->initVar('not_itemid', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('not_event', XOBJ_DTYPE_TXTBOX, null, false, 30);
		$this->initVar('not_uid', XOBJ_DTYPE_INT, 0, true);
		$this->initVar('not_mode', XOBJ_DTYPE_INT, 0, false);
    }
	function notifyUser($template_dir, $template, $subject, $tags)
	{
		$member_handler =& xoops_gethandler('member');
		$user =& $member_handler->getUser($this->getVar('not_uid'));
		if (!is_object($user)) {
			return true;
		}
		$method = $user->getVar('notify_method');
		$xoopsMailer =& getMailer();
		include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
		switch($method) {
		case XOOPS_NOTIFICATION_METHOD_PM:
			$xoopsMailer->usePM();
			$config_handler =& xoops_gethandler('config');
			$xoopsMailerConfig =& $config_handler->getConfigsByCat(XOOPS_CONF_MAILER);
			$xoopsMailer->setFromUser($member_handler->getUser($xoopsMailerConfig['fromuid']));
			foreach ($tags as $k=>$v) {
				$xoopsMailer->assign($k, $v);
			}
			break;
		case XOOPS_NOTIFICATION_METHOD_EMAIL:
			$xoopsMailer->useMail();
			foreach ($tags as $k=>$v) {
				$xoopsMailer->assign($k, preg_replace("/&amp;/i", '&', $v));
			}
			break;
		default:
			return true; 
			break;
		}
		$xoopsMailer->setTemplateDir($template_dir);
		$xoopsMailer->setTemplate($template);
		$xoopsMailer->setToUsers($user);
		$xoopsMailer->setSubject($subject);
		$success = $xoopsMailer->send();
		include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
		$notification_handler =& xoops_gethandler('notification');
		if ($this->getVar('not_mode') == XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE) {
			$notification_handler->delete($this);
			return $success;
		}
		if ($this->getVar('not_mode') == XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT) {
			$this->setVar('not_mode', XOOPS_NOTIFICATION_MODE_WAITFORLOGIN);
			$notification_handler->insert($this);
		}
		return $success;
	}
}
class XoopsNotificationHandler extends XoopsObjectHandler
{
	var $mTrigger = null;
	var $mTriggerPreAction = null;
	function XoopsNotificationHandler(&$db)
	{
		parent::XoopsObjectHandler($db);
		$this->mTrigger =& new XCube_Delegate();
		$this->mTrigger->register('XoopsNotificationHandler.Trigger');
		$this->mTriggerPreAction =& new XCube_Delegate();
		$this->mTriggerPreAction->register("XoopsNotificationHandler.TriggerPreAction");
	}
    function &create($isNew = true)
    {
        $notification =& new XoopsNotification();
        if ($isNew) {
            $notification->setNew();
        }
        return $notification;
    }
    function &get($id)
    {
        $id = intval($id);
        $ret = false;
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('xoopsnotifications').' WHERE not_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                        $notification =& new XoopsNotification();
                    $notification->assignVars($this->db->fetchArray($result));
                        $ret =& $notification;
                }
            }
        }
        return $ret;
    }
    function insert(&$notification)
    {
        if (strtolower(get_class($notification)) != 'xoopsnotification') {
            return false;
        }
        if (!$notification->isDirty()) {
            return true;
        }
        if (!$notification->cleanVars()) {
            return false;
        }
        foreach ($notification->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($notification->isNew()) {
            $not_id = $this->db->genId('xoopsnotifications_not_id_seq');
	    $sql = sprintf("INSERT INTO %s (not_id, not_modid, not_itemid, not_category, not_uid, not_event, not_mode) VALUES (%u, %u, %u, %s, %u, %s, %u)", $this->db->prefix('xoopsnotifications'), $not_id, $not_modid, $not_itemid, $this->db->quoteString($not_category), $not_uid, $this->db->quoteString($not_event), $not_mode);
        } else {
	    $sql = sprintf("UPDATE %s SET not_modid = %u, not_itemid = %u, not_category = %s, not_uid = %u, not_event = %s, not_mode = %u WHERE not_id = %u", $this->db->prefix('xoopsnotifications'), $not_modid, $not_itemid, $this->db->quoteString($not_category), $not_uid, $this->db->quoteString($not_event), $not_mode, $not_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($not_id)) {
            $not_id = $this->db->getInsertId();
        }
        $notification->assignVar('not_id', $not_id);
        return true;
    }
    function delete(&$notification)
    {
        if (strtolower(get_class($notification)) != 'xoopsnotification') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE not_id = %u", $this->db->prefix('xoopsnotifications'), $notification->getVar('not_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('xoopsnotifications');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $sort = ($criteria->getSort() != '') ? $criteria->getSort() : 'not_id';
            $sql .= ' ORDER BY '.$sort.' '.$criteria->getOrder();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $notification =& new XoopsNotification();
            $notification->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $notification;
            } else {
                $ret[$myrow['not_id']] =& $notification;
            }
            unset($notification);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('xoopsnotifications');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->db->query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        return $count;
    }
    function deleteAll($criteria = null)
    {
        $sql = 'DELETE FROM '.$this->db->prefix('xoopsnotifications');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
	function &getNotification ($module_id, $category, $item_id, $event, $user_id)
	{
        $ret = false;
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('not_modid', intval($module_id)));
		$criteria->add(new Criteria('not_category', $category));
		$criteria->add(new Criteria('not_itemid', intval($item_id)));
		$criteria->add(new Criteria('not_event', $event));
		$criteria->add(new Criteria('not_uid', intval($user_id)));
		$objects = $this->getObjects($criteria);
		if (count($objects) == 1) {
            $ret =& $objects[0];
		}
        return $ret;
	}
	function isSubscribed ($category, $item_id, $event, $module_id, $user_id)
	{
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('not_modid', intval($module_id)));
		$criteria->add(new Criteria('not_category', $category));
		$criteria->add(new Criteria('not_itemid', intval($item_id)));
		$criteria->add(new Criteria('not_event', $event));
		$criteria->add(new Criteria('not_uid', intval($user_id)));
		return $this->getCount($criteria);
	}
    function subscribe ($category, $item_id, $events, $mode=null, $module_id=null, $user_id=null)
    {
        if (!isset($user_id)) {
            global $xoopsUser;
            if (empty($xoopsUser)) {
                return false;  
            } else {
                $user_id = $xoopsUser->getVar('uid');
            }
        }
        if (!isset($module_id)) {
            global $xoopsModule;
            $module_id = $xoopsModule->getVar('mid');
        }
        if (!isset($mode)) {
            $user = new XoopsUser($user_id);
            $mode = $user->getVar('notify_mode');
        }
        if (!is_array($events)) $events = array($events);
        foreach ($events as $event) {
            if ($notification =& $this->getNotification($module_id, $category, $item_id, $event, $user_id)) {
                if ($notification->getVar('not_mode') != $mode) {
                    $this->updateByField($notification, 'not_mode', $mode);
            	}
            } else {
                $notification =& $this->create();
                $notification->setVar('not_modid', $module_id);
                $notification->setVar('not_category', $category);
                $notification->setVar('not_itemid', $item_id);
                $notification->setVar('not_uid', $user_id);
                $notification->setVar('not_event', $event);
                $notification->setVar('not_mode', $mode);
                $this->insert($notification);
            }
        }
    }
    function &getByUser ($user_id)
    {
        $criteria = new Criteria ('not_uid', $user_id);
        return $this->getObjects($criteria, true);
    }
	function &getSubscribedEvents ($category, $item_id, $module_id, $user_id)
    {
        $criteria = new CriteriaCompo();
        $criteria->add (new Criteria('not_modid', $module_id));
        $criteria->add (new Criteria('not_category', $category));
        if ($item_id) {
            $criteria->add (new Criteria('not_itemid', $item_id));
        }
        $criteria->add (new Criteria('not_uid', $user_id));
        $results = $this->getObjects($criteria, true);
        $ret = array();
        foreach (array_keys($results) as $i) {
            $ret[] = $results[$i]->getVar('not_event');
        }
        return $ret;
    }
    function &getByItemId($module_id, $item_id, $order = null, $status = null)
    {
        $criteria = new CriteriaCompo(new Criteria('com_modid', intval($module_id)));
        $criteria->add(new Criteria('com_itemid', intval($item_id)));
        if (isset($status)) {
            $criteria->add(new Criteria('com_status', intval($status)));
        }
        if (isset($order)) {
            $criteria->setOrder($order);
        }
        $ret =& $this->getObjects($criteria);
        return $ret;
    }
    function triggerEvents ($category, $item_id, $events, $extra_tags=array(), $user_list=array(), $module_id=null, $omit_user_id=null)
    {
        if (!is_array($events)) {
            $events = array($events);
        }
        foreach ($events as $event) {
            $this->triggerEvent($category, $item_id, $event, $extra_tags, $user_list, $module_id, $omit_user_id);
        }
    }
    function triggerEvent ($category, $item_id, $event, $extra_tags=array(), $user_list=array(), $module_id=null, $omit_user_id=null)
    {
        if (!isset($module_id)) {
            global $xoopsModule;
            $module =& $xoopsModule;
            $module_id = !empty($xoopsModule) ? $xoopsModule->getVar('mid') : 0;
        } else {
            $module_handler =& xoops_gethandler('module');
            $module =& $module_handler->get($module_id);
        }
        $not_config = $module->getInfo('notification');
		$event_correct = false;
		foreach ($not_config['event'] as $event_config) {
			if (($event_config['name'] == $event)&&($event_config['category'] == $category)) {
				$event_correct = true;
				break;
			}
		}
		if ($event_correct) {
			$force_return = false;
			$this->mTriggerPreAction->call(new XCube_Ref($category), new XCube_Ref($event), new XCube_Ref($item_id),
										   new XCube_Ref($extra_tags), new XCube_Ref($module), new XCube_Ref($user_list),
										   new XCube_Ref($omit_user_id), new XCube_Ref($not_config),
										   new XCube_Ref($force_return));
			$this->mTrigger->call($category, $event, $item_id, $extra_tags, new XCube_Ref($module), $user_list, $omit_user_id, $not_config, new XCube_Ref($force_return));
        	if ($force_return) {
				return;
			}
		}
		$config_handler =& xoops_gethandler('config');
		$mod_config =& $config_handler->getConfigsByCat(0,$module->getVar('mid'));
		if (empty($mod_config['notification_enabled'])) {
			return false;
		}
		$category_info =& notificationCategoryInfo ($category, $module_id);
		$event_info =& notificationEventInfo ($category, $event, $module_id);
		if (!in_array(notificationGenerateConfig($category_info,$event_info,'option_name'),$mod_config['notification_events']) && empty($event_info['invisible'])) {
			return false;
		}
        if (!isset($omit_user_id)) {
            global $xoopsUser;
            if (!empty($xoopsUser)) {
                $omit_user_id = $xoopsUser->getVar('uid');
            } else {
                $omit_user_id = 0;
            }
        }
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('not_modid', intval($module_id)));
        $criteria->add(new Criteria('not_category', $category));
        $criteria->add(new Criteria('not_itemid', intval($item_id)));
        $criteria->add(new Criteria('not_event', $event));
        $mode_criteria = new CriteriaCompo();
        $mode_criteria->add (new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDALWAYS), 'OR');
        $mode_criteria->add (new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE), 'OR');
        $mode_criteria->add (new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT), 'OR');
        $criteria->add($mode_criteria);
		if (!empty($user_list)) {
			$user_criteria = new CriteriaCompo();
			foreach ($user_list as $user) {
				$user_criteria->add (new Criteria('not_uid', $user), 'OR');
			}
			$criteria->add($user_criteria);
		}
        $notifications =& $this->getObjects($criteria);
        if (empty($notifications)) {
            return;
        }
        $tags = array();
        if (!empty($not_config)) {
            if (!empty($not_config['tags_file'])) {
                $tags_file = XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/' . $not_config['tags_file'];
                if (file_exists($tags_file)) {
                    include_once $tags_file;
                    if (!empty($not_config['tags_func'])) {
                        $tags_func = $not_config['tags_func'];
                        if (function_exists($tags_func)) {
                            $tags = $tags_func($category, intval($item_id), $event);
                        }
                    }
                }
            }
			if (!empty($not_config['lookup_file'])) {
				$lookup_file = XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/' . $not_config['lookup_file'];
				if (file_exists($lookup_file)) {
					include_once $lookup_file;
					if (!empty($not_config['lookup_func'])) {
						$lookup_func = $not_config['lookup_func'];
						if (function_exists($lookup_func)) {
							$item_info = $lookup_func($category, intval($item_id));
						}
					}
				}
			}
        }
		$tags['X_ITEM_NAME'] = !empty($item_info['name']) ? $item_info['name'] : '[' . _NOT_ITEMNAMENOTAVAILABLE . ']';
		$tags['X_ITEM_URL']  = !empty($item_info['url']) ? $item_info['url'] : '[' . _NOT_ITEMURLNOTAVAILABLE . ']';
		$tags['X_ITEM_TYPE'] = !empty($category_info['item_name']) ? $category_info['title'] : '[' . _NOT_ITEMTYPENOTAVAILABLE . ']';
        $tags['X_MODULE'] = $module->getVar('name', 'n');
        $tags['X_MODULE_URL'] = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/';
        $tags['X_NOTIFY_CATEGORY'] = $category;
        $tags['X_NOTIFY_EVENT'] = $event;
        $template_dir = $event_info['mail_template_dir'];
        $template = $event_info['mail_template'] . '.tpl';
        $subject = $event_info['mail_subject'];
		foreach ($notifications as $notification) {
			if (empty($omit_user_id) || $notification->getVar('not_uid') != $omit_user_id) {
				$tags['X_UNSUBSCRIBE_URL'] = XOOPS_URL . '/notifications.php';
        		$tags = array_merge ($tags, $extra_tags);
				$notification->notifyUser($template_dir, $template, $subject, $tags);
			}
		}
	}
    function unsubscribeByUser ($user_id)
    {
        $criteria = new Criteria('not_uid', intval($user_id));
        return $this->deleteAll($criteria);
    }
    function unsubscribe ($category, $item_id, $events, $module_id=null, $user_id=null)
    {
        if (!isset($user_id)) {
            global $xoopsUser;
            if (empty($xoopsUser)) {
                return false;  
            } else {
                $user_id = $xoopsUser->getVar('uid');
            }
        }
        if (!isset($module_id)) {
            global $xoopsModule;
            $module_id = $xoopsModule->getVar('mid');
        }
        $criteria = new CriteriaCompo();
        $criteria->add (new Criteria('not_modid', intval($module_id)));
        $criteria->add (new Criteria('not_category', $category));
        $criteria->add (new Criteria('not_itemid', intval($item_id)));
        $criteria->add (new Criteria('not_uid', intval($user_id)));
        if (!is_array($events)) {
            $events = array($events);
        }
        $event_criteria = new CriteriaCompo();
        foreach ($events as $event) {
            $event_criteria->add (new Criteria('not_event', $event), 'OR');
        }
        $criteria->add($event_criteria);
        return $this->deleteAll($criteria);
    }
    function unsubscribeByModule ($module_id)
    {
        $criteria = new Criteria('not_modid', intval($module_id));
        return $this->deleteAll($criteria);
    }
    function unsubscribeByItem ($module_id, $category, $item_id)
	{
        $criteria = new CriteriaCompo();
        $criteria->add (new Criteria('not_modid', intval($module_id)));
        $criteria->add (new Criteria('not_category', $category));
        $criteria->add (new Criteria('not_itemid', intval($item_id)));
        return $this->deleteAll($criteria);
    }
    function doLoginMaintenance ($user_id)
    {
        $criteria = new CriteriaCompo();
        $criteria->add (new Criteria('not_uid', intval($user_id)));
        $criteria->add (new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_WAITFORLOGIN));
        $notifications = $this->getObjects($criteria, true);
        foreach ($notifications as $n) {
            $n->setVar('not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT);
            $this->insert($n);
        }
    }
    function updateByField(&$notification, $field_name, $field_value)
    {
        $notification->unsetNew();
        $notification->setVar($field_name, $field_value);
        return $this->insert($notification);
    }
}
?>
