<?php
class XoopsSessionHandler
{
    var $db;
    function XoopsSessionHandler(&$db)
    {
        $this->db =& $db;
    }
    function open($save_path, $session_name)
	{
        return true;
    }
    function close()
	{
        return true;
    }
    function read($sess_id)
	{
        $sql = sprintf('SELECT sess_data FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($sess_id));
        if (false != $result = $this->db->query($sql)) {
            if (list($sess_data) = $this->db->fetchRow($result)) {
                return $sess_data;
            }
        }
        return '';
    }
    function write($sess_id, $sess_data)
	{
		$sess_id = $this->db->quoteString($sess_id);
		list($count) = $this->db->fetchRow($this->db->query("SELECT COUNT(*) FROM ".$this->db->prefix('session')." WHERE sess_id=".$sess_id));
        if ( $count > 0 ) {
			$sql = sprintf('UPDATE %s SET sess_updated = %u, sess_data = %s WHERE sess_id = %s', $this->db->prefix('session'), time(), $this->db->quoteString($sess_data), $sess_id);
        } else {
			$sql = sprintf('INSERT INTO %s (sess_id, sess_updated, sess_ip, sess_data) VALUES (%s, %u, %s, %s)', $this->db->prefix('session'), $sess_id, time(), $this->db->quoteString($_SERVER['REMOTE_ADDR']), $this->db->quoteString($sess_data));
        }
		if (!$this->db->queryF($sql)) {
            return false;
        }
		return true;
    }
    function destroy($sess_id)
    {
		$sql = sprintf('DELETE FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($sess_id));
        if ( !$result = $this->db->queryF($sql) ) {
            return false;
        }
        return true;
    }
    function gc($expire)
    {
        $mintime = time() - intval($expire);
		$sql = sprintf('DELETE FROM %s WHERE sess_updated < %u', $this->db->prefix('session'), $mintime);
        return $this->db->queryF($sql);
    }
}
?>
