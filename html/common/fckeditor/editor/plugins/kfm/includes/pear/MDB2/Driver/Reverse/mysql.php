<?php
require_once 'MDB2/Driver/Reverse/Common.php';
class MDB2_Driver_Reverse_mysql extends MDB2_Driver_Reverse_Common
{
    function getTableFieldDefinition($table, $field_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $result = $db->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "SHOW COLUMNS FROM $table LIKE ".$db->quote($field_name);
        $columns = $db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($columns)) {
            return $columns;
        }
        foreach ($columns as $column) {
            $column = array_change_key_case($column, CASE_LOWER);
            $column['name'] = $column['field'];
            unset($column['field']);
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column['name'] = strtolower($column['name']);
                } else {
                    $column['name'] = strtoupper($column['name']);
                }
            } else {
                $column = array_change_key_case($column, $db->options['field_case']);
            }
            if ($field_name == $column['name']) {
                $mapped_datatype = $db->datatype->mapNativeDatatype($column);
                if (PEAR::IsError($mapped_datatype)) {
                    return $mapped_datatype;
                }
                list($types, $length, $unsigned, $fixed) = $mapped_datatype;
                $notnull = false;
                if (empty($column['null']) || $column['null'] !== 'YES') {
                    $notnull = true;
                }
                $default = false;
                if (array_key_exists('default', $column)) {
                    $default = $column['default'];
                    if (is_null($default) && $notnull) {
                        $default = '';
                    }
                }
                $autoincrement = false;
                if (!empty($column['extra']) && $column['extra'] == 'auto_increment') {
                    $autoincrement = true;
                }
                $definition[0] = array(
                    'notnull' => $notnull,
                    'nativetype' => preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $column['type'])
                );
                if (!is_null($length)) {
                    $definition[0]['length'] = $length;
                }
                if (!is_null($unsigned)) {
                    $definition[0]['unsigned'] = $unsigned;
                }
                if (!is_null($fixed)) {
                    $definition[0]['fixed'] = $fixed;
                }
                if ($default !== false) {
                    $definition[0]['default'] = $default;
                }
                if ($autoincrement !== false) {
                    $definition[0]['autoincrement'] = $autoincrement;
                }
                foreach ($types as $key => $type) {
                    $definition[$key] = $definition[0];
                    if ($type == 'clob' || $type == 'blob') {
                        unset($definition[$key]['default']);
                    }
                    $definition[$key]['type'] = $type;
                    $definition[$key]['mdb2type'] = $type;
                }
                return $definition;
            }
        }
        return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
            'it was not specified an existing table column', __FUNCTION__);
    }
    function getTableIndexDefinition($table, $constraint_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table ";
        $constraint_name_mdb2 = $db->getIndexName($constraint_name);
        $result = $db->queryRow(sprintf($query, $db->quote($constraint_name_mdb2)));
        if (!PEAR::isError($result) && !is_null($result)) {
            $constraint_name = $constraint_name_mdb2;
        }
        $result = $db->query(sprintf($query, $db->quote($constraint_name)));
        if (PEAR::isError($result)) {
            return $result;
        }
        $colpos = 1;
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $key_name = strtolower($key_name);
                } else {
                    $key_name = strtoupper($key_name);
                }
            }
            if ($constraint_name == $key_name) {
                if (!$row['non_unique']) {
                    return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                        $constraint_name . ' is not an existing table constraint', __FUNCTION__);
                }
                $column_name = $row['column_name'];
                if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                    if ($db->options['field_case'] == CASE_LOWER) {
                        $column_name = strtolower($column_name);
                    } else {
                        $column_name = strtoupper($column_name);
                    }
                }
                $definition['fields'][$column_name] = array(
                    'position' => $colpos++
                );
                if (!empty($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                        ? 'ascending' : 'descending');
                }
            }
        }
        $result->free();
        if (empty($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }
        return $definition;
    }
    function getTableConstraintDefinition($table, $index_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table ";
        if (strtolower($index_name) != 'primary') {
            $index_name_mdb2 = $db->getIndexName($index_name);
            $result = $db->queryRow(sprintf($query, $db->quote($index_name_mdb2)));
            if (!PEAR::isError($result) && !is_null($result)) {
                $index_name = $index_name_mdb2;
            }
        }
        $result = $db->query(sprintf($query, $db->quote($index_name)));
        if (PEAR::isError($result)) {
            return $result;
        }
        $colpos = 1;
        $definition = array();
        while (is_array($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $key_name = strtolower($key_name);
                } else {
                    $key_name = strtoupper($key_name);
                }
            }
            if ($index_name == $key_name) {
                if ($row['non_unique']) {
                    return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                        'it was not specified an existing table constraint', __FUNCTION__);
                }
                if ($row['key_name'] == 'PRIMARY') {
                    $definition['primary'] = true;
                } else {
                    $definition['unique'] = true;
                }
                $column_name = $row['column_name'];
                if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                    if ($db->options['field_case'] == CASE_LOWER) {
                        $column_name = strtolower($column_name);
                    } else {
                        $column_name = strtoupper($column_name);
                    }
                }
                $definition['fields'][$column_name] = array(
                    'position' => $colpos++
                );
                if (!empty($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                        ? 'ascending' : 'descending');
                }
            }
        }
        $result->free();
        if (empty($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table constraint', __FUNCTION__);
        }
        return $definition;
    }
    function getTriggerDefinition($trigger)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'SELECT trigger_name,
                         event_object_table AS table_name,
                         action_statement AS trigger_body,
                         action_timing AS trigger_type,
                         event_manipulation AS trigger_event
                    FROM information_schema.triggers
                   WHERE trigger_name = '. $db->quote($trigger, 'text');
        $types = array(
            'trigger_name'    => 'text',
            'table_name'      => 'text',
            'trigger_body'    => 'text',
            'trigger_type'    => 'text',
            'trigger_event'   => 'text',
        );
        $def = $db->queryRow($query, $types, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($def)) {
            return $def;
        }
        $def['trigger_comment'] = '';
        $def['trigger_enabled'] = true;
        return $def;
    }
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
           return parent::tableInfo($result, $mode);
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $resource = MDB2::isResultCommon($result) ? $result->getResource() : $result;
        if (!is_resource($resource)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'Could not generate result resource', __FUNCTION__);
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $case_func = 'strtolower';
            } else {
                $case_func = 'strtoupper';
            }
        } else {
            $case_func = 'strval';
        }
        $count = @mysql_num_fields($resource);
        $res   = array();
        if ($mode) {
            $res['num_fields'] = $count;
        }
        $db->loadModule('Datatype', null, true);
        for ($i = 0; $i < $count; $i++) {
            $res[$i] = array(
                'table' => $case_func(@mysql_field_table($resource, $i)),
                'name'  => $case_func(@mysql_field_name($resource, $i)),
                'type'  => @mysql_field_type($resource, $i),
                'length'   => @mysql_field_len($resource, $i),
                'flags' => @mysql_field_flags($resource, $i),
            );
            if ($res[$i]['type'] == 'string') {
                $res[$i]['type'] = 'char';
            } elseif ($res[$i]['type'] == 'unknown') {
                $res[$i]['type'] = 'decimal';
            }
            $mdb2type_info = $db->datatype->mapNativeDatatype($res[$i]);
            if (PEAR::isError($mdb2type_info)) {
               return $mdb2type_info;
            }
            $res[$i]['mdb2type'] = $mdb2type_info[0][0];
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }
        return $res;
    }
}
?>
