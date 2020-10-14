<?php
class MDB2_Driver_Function_Common extends MDB2_Module_Common
{
    function &executeStoredProc($name, $params = null, $types = null, $result_class = true, $result_wrap_class = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $error =& $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
        return $error;
    }
    function functionTable()
    {
        return '';
    }
    function now($type = 'timestamp')
    {
        switch ($type) {
        case 'time':
            return 'CURRENT_TIME';
        case 'date':
            return 'CURRENT_DATE';
        case 'timestamp':
        default:
            return 'CURRENT_TIMESTAMP';
        }
    }
    function substring($value, $position = 1, $length = null)
    {
        if (!is_null($length)) {
            return "SUBSTRING($value FROM $position FOR $length)";
        }
        return "SUBSTRING($value FROM $position)";
    }
    function concat($value1, $value2)
    {
        $args = func_get_args();
        return "(".implode(' || ', $args).")";
    }
    function random()
    {
        return 'RAND()';
    }
    function lower($expression)
    {
        return "LOWER($expression)";
    }
    function upper($expression)
    {
        return "UPPER($expression)";
    }
    function guid()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $error =& $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
        return $error;
    }
}
?>
