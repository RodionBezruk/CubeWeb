<?php
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
class XoopsComment extends XoopsObject
{
    function XoopsComment()
    {
        $this->XoopsObject();
        $this->initVar('com_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_pid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_modid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('com_icon', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('com_title', XOBJ_DTYPE_TXTBOX, null, true, 255, true);
        $this->initVar('com_text', XOBJ_DTYPE_TXTAREA, null, true, null, true);
        $this->initVar('com_created', XOBJ_DTYPE_INT, time(), false);
        $this->initVar('com_modified', XOBJ_DTYPE_INT, time(), false);
        $this->initVar('com_uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('com_ip', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('com_sig', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_itemid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_rootid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_status', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('com_exparams', XOBJ_DTYPE_OTHER, null, false, 255);
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('dosmiley', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('doxcode', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('doimage', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('dobr', XOBJ_DTYPE_INT, 1, false);
    }
	function isRoot()
    {
        return ($this->getVar('com_id') == $this->getVar('com_rootid'));
    }
    function &createChild()
    {
		$ret=new XoopsComment();
		$ret->setNew();
		$ret->setVar('com_pid',$this->getVar('com_id'));
		$ret->setVar('com_rootid',$this->getVar('com_rootid'));
		$ret->setVar('com_modid',$this->getVar('com_modid'));
		$ret->setVar('com_itemid',$this->getVar('com_itemid'));
		$ret->setVar('com_exparams',$this->getVar('com_exparams'));
		$title = $this->get('com_title');
		if (preg_match("/^Re:(.+)$/", $title, $matches)) {
			$ret->set('com_title', "Re[2]: " . $matches[1]);
		}
		elseif (preg_match("/^Re\[(\d+)\]:(.+)$/", $title, $matches)) {
			$ret->set('com_title', "Re[" . ($matches[1] + 1) . "]: " . $matches[2]);
		}
		return $ret;
	}
}
class XoopsCommentHandler extends XoopsObjectHandler
{
    function &create($isNew = true)
    {
        $comment =& new XoopsComment();
        if ($isNew) {
            $comment->setNew();
        }
        return $comment;
    }
    function &get($id)
    {
        $ret = false;
        $id = intval($id);
        if ($id > 0) {
            $sql = 'SELECT * FROM '.$this->db->prefix('xoopscomments').' WHERE com_id='.$id;
            if ($result = $this->db->query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                    $comment = new XoopsComment();
                    $comment->assignVars($this->db->fetchArray($result));
                        $ret =& $comment;
                }
            }
        }
        return $ret;
    }
    function insert(&$comment)
    {
        if (strtolower(get_class($comment)) != 'xoopscomment') {
            return false;
        }
        if (!$comment->isDirty()) {
            return true;
        }
        if (!$comment->cleanVars()) {
            return false;
        }
        foreach ($comment->cleanVars as $k => $v) {
            ${$k} = $v;
        }
        if ($comment->isNew()) {
            $com_id = $this->db->genId('xoopscomments_com_id_seq');
            $sql = sprintf("INSERT INTO %s (com_id, com_pid, com_modid, com_icon, com_title, com_text, com_created, com_modified, com_uid, com_ip, com_sig, com_itemid, com_rootid, com_status, com_exparams, dohtml, dosmiley, doxcode, doimage, dobr) VALUES (%u, %u, %u, %s, %s, %s, %u, %u, %u, %s, %u, %u, %u, %u, %s, %u, %u, %u, %u, %u)", $this->db->prefix('xoopscomments'), $com_id, $com_pid, $com_modid, $this->db->quoteString($com_icon), $this->db->quoteString($com_title), $this->db->quoteString($com_text), $com_created, $com_modified, $com_uid, $this->db->quoteString($com_ip), $com_sig, $com_itemid, $com_rootid, $com_status, $this->db->quoteString($com_exparams), $dohtml, $dosmiley, $doxcode, $doimage, $dobr);
        } else {
            $sql = sprintf("UPDATE %s SET com_pid = %u, com_icon = %s, com_title = %s, com_text = %s, com_created = %u, com_modified = %u, com_uid = %u, com_ip = %s, com_sig = %u, com_itemid = %u, com_rootid = %u, com_status = %u, com_exparams = %s, dohtml = %u, dosmiley = %u, doxcode = %u, doimage = %u, dobr = %u WHERE com_id = %u", $this->db->prefix('xoopscomments'), $com_pid, $this->db->quoteString($com_icon), $this->db->quoteString($com_title), $this->db->quoteString($com_text), $com_created, $com_modified, $com_uid, $this->db->quoteString($com_ip), $com_sig, $com_itemid, $com_rootid, $com_status, $this->db->quoteString($com_exparams), $dohtml, $dosmiley, $doxcode, $doimage, $dobr, $com_id);
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        if (empty($com_id)) {
            $com_id = $this->db->getInsertId();
        }
        $comment->assignVar('com_id', $com_id);
        return true;
    }
    function delete(&$comment)
    {
        if (strtolower(get_class($comment)) != 'xoopscomment') {
            return false;
        }
        $sql = sprintf("DELETE FROM %s WHERE com_id = %u", $this->db->prefix('xoopscomments'), $comment->getVar('com_id'));
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('xoopscomments');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $sort = ($criteria->getSort() != '') ? $criteria->getSort() : 'com_id';
            $sql .= ' ORDER BY '.$sort.' '.$criteria->getOrder();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $comment = new XoopsComment();
            $comment->assignVars($myrow);
            if (!$id_as_key) {
                $ret[] =& $comment;
            } else {
                $ret[$myrow['com_id']] =& $comment;
            }
            unset($comment);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('xoopscomments');
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
        $sql = 'DELETE FROM '.$this->db->prefix('xoopscomments');
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        return true;
    }
    function &getList($criteria = null)
    {
        $comments =& $this->getObjects($criteria, true);
        $ret = array();
        foreach (array_keys($comments) as $i) {
            $ret[$i] = $comments[$i]->getVar('com_title');
        }
        return $ret;
    }
    function &getByItemId($module_id, $item_id, $order = null, $status = null, $limit = null, $start = 0)
    {
        $criteria = new CriteriaCompo(new Criteria('com_modid', intval($module_id)));
        $criteria->add(new Criteria('com_itemid', intval($item_id)));
        if (isset($status)) {
            $criteria->add(new Criteria('com_status', intval($status)));
        }
        if (isset($order)) {
            $criteria->setOrder($order);
        }
        if (isset($limit)) {
            $criteria->setLimit($limit);
			$criteria->setStart($start);
        }
        return $this->getObjects($criteria);
    }
    function &getCountByItemId($module_id, $item_id, $status = null)
    {
        $criteria = new CriteriaCompo(new Criteria('com_modid', intval($module_id)));
        $criteria->add(new Criteria('com_itemid', intval($item_id)));
        if (isset($status)) {
            $criteria->add(new Criteria('com_status', intval($status)));
        }
        return $this->getCount($criteria);
    }
    function &getTopComments($module_id, $item_id, $order, $status = null)
    {
        $criteria = new CriteriaCompo(new Criteria('com_modid', intval($module_id)));
        $criteria->add(new Criteria('com_itemid', intval($item_id)));
        $criteria->add(new Criteria('com_pid', 0));
        if (isset($status)) {
            $criteria->add(new Criteria('com_status', intval($status)));
        }
        $criteria->setOrder($order);
        $ret =& $this->getObjects($criteria);
        return $ret;
    }
    function &getThread($comment_rootid, $comment_id, $status = null)
    {
        $criteria = new CriteriaCompo(new Criteria('com_rootid', intval($comment_rootid)));
        $criteria->add(new Criteria('com_id', intval($comment_id), '>='));
        if (isset($status)) {
            $criteria->add(new Criteria('com_status', intval($status)));
        }
        return $this->getObjects($criteria);
    }
    function updateByField(&$comment, $field_name, $field_value)
    {
        $comment->unsetNew();
        $comment->setVar($field_name, $field_value);
        return $this->insert($comment);
    }
    function deleteByModule($module_id)
    {
        return $this->deleteAll(new Criteria('com_modid', intval($module_id)));
    }
	function getChildObjects(&$comment)
	{
		$ret=array();
		$table=$this->db->prefix("xoopscomments");
		$sql="SELECT * FROM ${table} WHERE com_pid=" . $comment->getVar("com_id") .
		      " AND com_id<>".$comment->getVar("com_id");
		$result=$this->db->query($sql);
		while($row=$this->db->fetchArray($result)) {
			$comment=new XoopsComment();
			$comment->assignVars($row);
			$ret[]=&$comment;
			unset($comment);
		}
		return $ret;
	}
	function deleteWithChild(&$comment)
	{
		foreach($this->getChildObjects($comment) as $child) {
			$this->deleteWithChild($child);
		}
		$this->delete($comment);
		return true;	
	}
}
?>
