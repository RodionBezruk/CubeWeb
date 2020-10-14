<?php
define('MDB2_AUTOQUERY_INSERT', 1);
define('MDB2_AUTOQUERY_UPDATE', 2);
define('MDB2_AUTOQUERY_DELETE', 3);
define('MDB2_AUTOQUERY_SELECT', 4);
class MDB2_Extended extends MDB2_Module_Common
{
    function autoPrepare($table, $table_fields, $mode = MDB2_AUTOQUERY_INSERT,
        $where = false, $types = null, $result_types = MDB2_PREPARE_MANIP)
    {
        $query = $this->buildManipSQL($table, $table_fields, $mode, $where);
        if (PEAR::isError($query)) {
            return $query;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->prepare($query, $types, $result_types);
    }
    function &autoExecute($table, $fields_values, $mode = MDB2_AUTOQUERY_INSERT,
        $where = false, $types = null, $result_class = true, $result_types = MDB2_PREPARE_MANIP)
    {
        $fields_values = (array)$fields_values;
        if ($mode == MDB2_AUTOQUERY_SELECT) {
            if (is_array($result_types)) {
                $keys = array_keys($result_types);
            } elseif (!empty($fields_values)) {
                $keys = $fields_values;
            } else {
                $keys = array();
            }
        } else {
            $keys = array_keys($fields_values);
        }
        $params = array_values($fields_values);
        if (empty($params)) {
            $query = $this->buildManipSQL($table, $keys, $mode, $where);
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if ($mode == MDB2_AUTOQUERY_SELECT) {
                $result =& $db->query($query, $result_types, $result_class);
            } else {
                $result = $db->exec($query);
            }
        } else {
            $stmt = $this->autoPrepare($table, $keys, $mode, $where, $types, $result_types);
            if (PEAR::isError($stmt)) {
                return $stmt;
            }
            $result =& $stmt->execute($params, $result_class);
            $stmt->free();
        }
        return $result;
    }
    function buildManipSQL($table, $table_fields, $mode, $where = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if ($db->options['quote_identifier']) {
            $table = $db->quoteIdentifier($table);
        }
        if (!empty($table_fields) && $db->options['quote_identifier']) {
            foreach ($table_fields as $key => $field) {
                $table_fields[$key] = $db->quoteIdentifier($field);
            }
        }
        if ($where !== false && !is_null($where)) {
            if (is_array($where)) {
                $where = implode(' AND ', $where);
            }
            $where = ' WHERE '.$where;
        }
        switch ($mode) {
        case MDB2_AUTOQUERY_INSERT:
            if (empty($table_fields)) {
                return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'Insert requires table fields', __FUNCTION__);
            }
            $cols = implode(', ', $table_fields);
            $values = '?'.str_repeat(', ?', (count($table_fields) - 1));
            return 'INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.')';
            break;
        case MDB2_AUTOQUERY_UPDATE:
            if (empty($table_fields)) {
                return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'Update requires table fields', __FUNCTION__);
            }
            $set = implode(' = ?, ', $table_fields).' = ?';
            $sql = 'UPDATE '.$table.' SET '.$set.$where;
            return $sql;
            break;
        case MDB2_AUTOQUERY_DELETE:
            $sql = 'DELETE FROM '.$table.$where;
            return $sql;
            break;
        case MDB2_AUTOQUERY_SELECT:
            $cols = !empty($table_fields) ? implode(', ', $table_fields) : '*';
            $sql = 'SELECT '.$cols.' FROM '.$table.$where;
            return $sql;
            break;
        }
        return $db->raiseError(MDB2_ERROR_SYNTAX, null, null,
                'Non existant mode', __FUNCTION__);
    }
    function &limitQuery($query, $types, $limit, $offset = 0, $result_class = true,
        $result_wrap_class = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $result = $db->setLimit($limit, $offset);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result =& $db->query($query, $types, $result_class, $result_wrap_class);
        return $result;
    }
    function execParam($query, $params = array(), $param_types = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        if (empty($params)) {
            return $db->exec($query);
        }
        $stmt = $db->prepare($query, $param_types, MDB2_PREPARE_MANIP);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (PEAR::isError($result)) {
            return $result;
        }
        $stmt->free();
        return $result;
    }
    function getOne($query, $type = null, $params = array(),
        $param_types = null, $colnum = 0)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        settype($type, 'array');
        if (empty($params)) {
            return $db->queryOne($query, $type, $colnum);
        }
        $stmt = $db->prepare($query, $param_types, $type);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $one = $result->fetchOne($colnum);
        $stmt->free();
        $result->free();
        return $one;
    }
    function getRow($query, $types = null, $params = array(),
        $param_types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        if (empty($params)) {
            return $db->queryRow($query, $types, $fetchmode);
        }
        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $row = $result->fetchRow($fetchmode);
        $stmt->free();
        $result->free();
        return $row;
    }
    function getCol($query, $type = null, $params = array(),
        $param_types = null, $colnum = 0)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        settype($type, 'array');
        if (empty($params)) {
            return $db->queryCol($query, $type, $colnum);
        }
        $stmt = $db->prepare($query, $param_types, $type);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $col = $result->fetchCol($colnum);
        $stmt->free();
        $result->free();
        return $col;
    }
    function getAll($query, $types = null, $params = array(),
        $param_types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT,
        $rekey = false, $force_array = false, $group = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        if (empty($params)) {
            return $db->queryAll($query, $types, $fetchmode, $rekey, $force_array, $group);
        }
        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $all = $result->fetchAll($fetchmode, $rekey, $force_array, $group);
        $stmt->free();
        $result->free();
        return $all;
    }
    function getAssoc($query, $types = null, $params = array(), $param_types = null,
        $fetchmode = MDB2_FETCHMODE_DEFAULT, $force_array = false, $group = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        settype($params, 'array');
        if (empty($params)) {
            return $db->queryAll($query, $types, $fetchmode, true, $force_array, $group);
        }
        $stmt = $db->prepare($query, $param_types, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        $result = $stmt->execute($params);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $all = $result->fetchAll($fetchmode, true, $force_array, $group);
        $stmt->free();
        $result->free();
        return $all;
    }
    function executeMultiple(&$stmt, $params = null)
    {
        for ($i = 0, $j = count($params); $i < $j; $i++) {
            $result = $stmt->execute($params[$i]);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return MDB2_OK;
    }
    function getBeforeID($table, $field = null, $ondemand = true, $quote = true)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if ($db->supports('auto_increment') !== true) {
            $seq = $table.(empty($field) ? '' : '_'.$field);
            $id = $db->nextID($seq, $ondemand);
            if (!$quote || PEAR::isError($id)) {
                return $id;
            }
            return $db->quote($id, 'integer');
        } elseif (!$quote) {
            return null;
        }
        return 'NULL';
    }
    function getAfterID($id, $table, $field = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if ($db->supports('auto_increment') !== true) {
            return $id;
        }
        return $db->lastInsertID($table, $field);
    }
}
?>
