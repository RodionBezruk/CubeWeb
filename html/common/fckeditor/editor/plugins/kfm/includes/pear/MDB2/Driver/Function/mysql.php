<?php
require_once 'MDB2/Driver/Function/Common.php';
class MDB2_Driver_Function_mysql extends MDB2_Driver_Function_Common
{
    function &executeStoredProc($name, $params = null, $types = null, $result_class = true, $result_wrap_class = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'CALL '.$name;
        $query .= $params ? '('.implode(', ', $params).')' : '()';
        return $db->query($query, $types, $result_class, $result_wrap_class);
    }
    function concat($value1, $value2)
    {
        $args = func_get_args();
        return "CONCAT(".implode(', ', $args).")";
    }
    function guid()
    {
        return 'UUID()';
    }
}
?>
