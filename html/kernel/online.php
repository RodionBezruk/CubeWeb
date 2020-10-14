<?php
class XoopsOnlineHandler
{
    var $db;
    function XoopsOnlineHandler(&$db)
    {
        $this->db =& $db;
    }
    function write($uid, $uname, $time, $module, $ip)
	{
		$uid = intval($uid);
		$ip = $this->db->quoteString($ip);
		if ($uid > 0) {
			$sql = "SELECT COUNT(*) FROM ".$this->db->prefix('online')." WHERE online_uid=".$uid;
		} else {
			$sql = "SELECT COUNT(*) FROM ".$this->db->prefix('online')." WHERE online_uid=".$uid." AND online_ip=".$ip;
		}
		list($count) = $this->db->fetchRow($this->db->queryF($sql));
        if ( $count > 0 ) {
            $sql = "UPDATE ".$this->db->prefix('online')." SET online_updated=".$time.", online_module = ".$module." WHERE online_uid = ".$uid;
            if ($uid == 0) {
                $sql .= " AND online_ip=".$ip;
            }
        } else {
			$sql = sprintf("INSERT INTO %s (online_uid, online_uname, online_updated, online_ip, online_module) VALUES (%u, %s, %u, %s, %u)", $this->db->prefix('online'), $uid, $this->db->quoteString($uname), $time, $ip, $module);
        }
		if (!$this->db->queryF($sql)) {
            return false;
        }
		return true;
    }
    function destroy($uid)
    {
		$sql = sprintf("DELETE FROM %s WHERE online_uid = %u", $this->db->prefix('online'), $uid);
        if (!$result = $this->db->queryF($sql)) {
            return false;
        }
        return true;
    }
    function gc($expire)
    {
		$sql = sprintf("DELETE FROM %s WHERE online_updated < %u", $this->db->prefix('online'), time() - intval($expire));
        $this->db->queryF($sql);
    }
    function &getAll($criteria = null)
    {
        $ret = array();
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->db->prefix('online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result =& $this->db->query($sql, $limit, $start);
        if (!$result) {
			$ret = false;
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] =& $myrow;
            unset($myrow);
        }
        return $ret;
    }
    function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result =& $this->db->query($sql)) {
			$ret = false;
            return $ret;
        }
        list($ret) = $this->db->fetchRow($result);
        return $ret;
    }
}
?>
