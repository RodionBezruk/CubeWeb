<?php
require_once 'MDB2.php';
class MDB2_LOB
{
    var $db_index;
    var $lob_index;
    function stream_open($path, $mode, $options, &$opened_path)
    {
        if (!preg_match('/^rb?\+?$/', $mode)) {
            return false;
        }
        $url = parse_url($path);
        if (empty($url['host'])) {
            return false;
        }
        $this->db_index = (int)$url['host'];
        if (!isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            return false;
        }
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $this->lob_index = (int)$url['user'];
        if (!isset($db->datatype->lobs[$this->lob_index])) {
            return false;
        }
        return true;
    }
    function stream_read($count)
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
            $db->datatype->_retrieveLOB($db->datatype->lobs[$this->lob_index]);
            $data = $db->datatype->_readLOB($db->datatype->lobs[$this->lob_index], $count);
            $length = strlen($data);
            if ($length == 0) {
                $db->datatype->lobs[$this->lob_index]['endOfLOB'] = true;
            }
            $db->datatype->lobs[$this->lob_index]['position'] += $length;
            return $data;
        }
    }
    function stream_write($data)
    {
        return 0;
    }
    function stream_tell()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
            return $db->datatype->lobs[$this->lob_index]['position'];
        }
    }
    function stream_eof()
    {
        if (!isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            return true;
        }
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->datatype->_endOfLOB($db->datatype->lobs[$this->lob_index]);
        if (version_compare(phpversion(), "5.0", ">=")
            && version_compare(phpversion(), "5.1", "<")
        ) {
            return !$result;
        }
        return $result;
    }
    function stream_seek($offset, $whence)
    {
        return false;
    }
    function stream_stat()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
            return array(
              'db_index' => $this->db_index,
              'lob_index' => $this->lob_index,
            );
        }
    }
    function stream_close()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
            if (isset($db->datatype->lobs[$this->lob_index])) {
                $db->datatype->_destroyLOB($db->datatype->lobs[$this->lob_index]);
                unset($db->datatype->lobs[$this->lob_index]);
            }
        }
    }
}
if (!stream_wrapper_register("MDB2LOB", "MDB2_LOB")) {
    MDB2::raiseError();
    return false;
}
?>
