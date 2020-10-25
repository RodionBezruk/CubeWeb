<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
require_once XOOPS_MODULE_PATH . "/legacy/class/AbstractDeleteAction.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/forms/CommentAdminDeleteForm.class.php";
require_once XOOPS_MODULE_PATH . "/legacy/admin/actions/CommentEditAction.class.php";
class Legacy_CommentDeleteAction extends Legacy_AbstractDeleteAction
{
	function _getId()
	{
		return isset($_REQUEST['com_id']) ? xoops_getrequest('com_id') : 0;
	}
	function &_getHandler()
	{
		$handler =& xoops_getmodulehandler('comment');
		$handler->mDeleteSuccess->add(array(&$this, "doDelete"));
		return $handler;
	}
	function _setupActionForm()
	{
		$this->mActionForm =& new Legacy_CommentAdminDeleteForm();
		$this->mActionForm->prepare();
	}
	function executeViewInput(&$controller, &$xoopsUser, &$render)
	{
		$this->mObject->loadUser();
		$this->mObject->loadModule();
		$this->mObject->loadStatus();
		$handler =& xoops_getmodulehandler('comment');
		$criteria =& new Criteria('com_pid', $this->mObject->get('com_id'));
		$children =& $handler->getObjects($criteria);
		if (count($children) > 0) {
			foreach (array_keys($children) as $key) {
				$children[$key]->loadModule();
				$children[$key]->loadUser();
			}
		}
		$render->setTemplateName("comment_delete.html");
		$render->setAttribute('actionForm', $this->mActionForm);
		$render->setAttribute('object', $this->mObject);
		$render->setAttribute('children', $children);
	}
	function executeViewSuccess(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeForward("./index.php?action=CommentList");
	}
	function executeViewError(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeRedirect("./index.php?action=CommentList", 1, _MD_LEGACY_ERROR_DBUPDATE_FAILED);
	}
	function executeViewCancel(&$controller, &$xoopsUser, &$render)
	{
		$controller->executeForward("./index.php?action=CommentList");
	}
	function _doExecute()
	{
		if ($this->mActionForm->get('delete_mode') == "delete_one") {
		return $this->doDeleteOne($this->mObject);
		}
		else {
		return $this->mObjectHandler->delete($this->mObject);
		}
	}
	function doDeleteOne($comment)
	{
		$comment_handler = xoops_gethandler('comment');
		$comment =& $comment_handler->get($comment->getVar('com_id'));
		if( !$comment_handler->delete($comment) ) {
		return false;
		}
		if ($comment->get('com_status') != 1 && $comment->get('com_uid') > 0) {
			$memberhandler =& xoops_gethandler('member');
			$user =& $memberhandler->getUser($comment->get('com_uid'));
			if (is_object($user)) {
				$count = $user->get('posts');
				if($count > 0) {
					$memberhandler->updateUserByField($user, 'posts', $count - 1);
				}
			}
		}
		$thread_comments =& $comment_handler->getThread($comment->getVar('com_rootid'), $comment->getVar('com_id'));
		include_once XOOPS_ROOT_PATH.'/class/tree.php';
		$xot = new XoopsObjectTree($thread_comments, 'com_id', 'com_pid', 'com_rootid');
		$child_comments =& $xot->getFirstChild($comment->getVar('com_id'));
		$new_pid = $comment->getVar('com_pid');
		$errs = array();
		foreach (array_keys($child_comments) as $i) {
		$child_comments[$i]->setVar('com_pid', $new_pid);
		if (false != $comment->isRoot()) {
		$new_rootid = $child_comments[$i]->getVar('com_id');
		$child_comments[$i]->setVar('com_rootid', $child_comments[$i]->getVar('com_id'));
		if (!$comment_handler->insert($child_comments[$i])) {
		           $errs[] = 'Could not change comment parent ID from <b>'.$comment->getVar('com_id').'</b> to <b>'.$new_pid.'</b>. (ID: '.$new_rootid.')';
		} else {
		$c_child_comments =& $xot->getAllChild($new_rootid);
		$cc_count = count($c_child_comments);
		foreach (array_keys($c_child_comments) as $j) {
                    	$c_child_comments[$j]->setVar('com_rootid', $new_rootid);
                    	if (!$comment_handler->insert($c_child_comments[$j])) {
                        $errs[] = 'Could not change comment root ID from <b>'.$comment->getVar('com_id').'</b> to <b>'.$new_rootid.'</b>.';
                    	}
                	}
            	}
        		} else {
            	if (!$comment_handler->insert($child_comments[$i])) {
                	$errs[] = 'Could not change comment parent ID from <b>'.$comment->getVar('com_id').'</b> to <b>'.$new_pid.'</b>.';
            	}
       		}
		}
		if (count($errs) > 0) {
		return false;
		}
		$comment_config = Legacy_CommentEditAction::loadCallbackFile($comment);
		if ($comment_config ) {
		$function = $comment_config['callback']['update'];		
		if (function_exists($function)) {
			$criteria = new CriteriaCompo(new Criteria('com_modid', $comment->getVar('com_modid')));
                		$criteria->add(new Criteria('com_itemid', $comment->getVar('com_itemid')));
                		$criteria->add(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));
                		$comment_count = $comment_handler->getCount($criteria);
			call_user_func_array($function, array($comment->getVar('com_itemid'), $comment_count, $comment->getVar('com_id')));
		}
		}
		return true;
	}
	function doDelete($comment)
	{
		if ($comment->get('com_status') != 1 && $comment->get('com_uid') > 0) {
			$handler =& xoops_gethandler('member');
			$user =& $handler->getUser($comment->get('com_uid'));
			if (is_object($user)) {
				$count = $user->get('posts');
				if($count > 0) {
					$handler->updateUserByField($user, 'posts', $count - 1);
				}
			}
		}
		$comment_config = Legacy_CommentEditAction::loadCallbackFile($comment);
		if ($comment_config == false) {
			return;
		}
		$function = $comment_config['callback']['update'];
		if (function_exists($function)) {
			$comment_handler = xoops_gethandler('comment');
    			$criteria = new CriteriaCompo(new Criteria('com_modid', $comment->getVar('com_modid')));
			$criteria->add(new Criteria('com_itemid', $comment->getVar('com_itemid')));
			$criteria->add(new Criteria('com_status', XOOPS_COMMENT_ACTIVE));
			$comment_count = $comment_handler->getCount($criteria);
			call_user_func_array($function, array($comment->getVar('com_itemid'), $comment_count, $comment->getVar('com_id')));
		}
	}
}
?>
