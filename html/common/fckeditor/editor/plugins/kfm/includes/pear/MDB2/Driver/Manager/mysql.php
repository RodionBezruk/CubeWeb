<?php
require_once 'MDB2/Driver/Manager/Common.php';
class MDB2_Driver_Manager_mysql extends MDB2_Driver_Manager_Common
{
    function createDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $name = $db->quoteIdentifier($name, true);
        $query = "CREATE DATABASE $name";
        $result = $db->exec($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        return MDB2_OK;
    }
    function dropDatabase($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $name = $db->quoteIdentifier($name, true);
        $query = "DROP DATABASE $name";
        $result = $db->exec($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        return MDB2_OK;
    }
    function createTable($name, $fields, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = $this->_getCreateTableQuery($name, $fields, $options);
        if (PEAR::isError($query)) {
            return $query;
        }
        $options_strings = array();
        if (!empty($options['comment'])) {
            $options_strings['comment'] = 'COMMENT = '.$db->quote($options['comment'], 'text');
        }
        if (!empty($options['charset'])) {
            $options_strings['charset'] = 'DEFAULT CHARACTER SET '.$options['charset'];
            if (!empty($options['collate'])) {
                $options_strings['charset'].= ' COLLATE '.$options['collate'];
            }
        }
        $type = false;
        if (!empty($options['type'])) {
            $type = $options['type'];
        } elseif ($db->options['default_table_type']) {
            $type = $db->options['default_table_type'];
        }
        if ($type) {
            $options_strings[] = "ENGINE = $type";
        }
        if (!empty($options_strings)) {
            $query .= ' '.implode(' ', $options_strings);
        }
        return $db->exec($query);
    }
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
            case 'add':
            case 'remove':
            case 'change':
            case 'rename':
            case 'name':
                break;
            default:
                return $db->raiseError(MDB2_ERROR_CANNOT_ALTER, null, null,
                    'change type "'.$change_name.'" not yet supported', __FUNCTION__);
            }
        }
        if ($check) {
            return MDB2_OK;
        }
        $query = '';
        if (!empty($changes['name'])) {
            $change_name = $db->quoteIdentifier($changes['name'], true);
            $query .= 'RENAME TO ' . $change_name;
        }
        if (!empty($changes['add']) && is_array($changes['add'])) {
            foreach ($changes['add'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $query.= 'ADD ' . $db->getDeclaration($field['type'], $field_name, $field);
            }
        }
        if (!empty($changes['remove']) && is_array($changes['remove'])) {
            foreach ($changes['remove'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                $field_name = $db->quoteIdentifier($field_name, true);
                $query.= 'DROP ' . $field_name;
            }
        }
        $rename = array();
        if (!empty($changes['rename']) && is_array($changes['rename'])) {
            foreach ($changes['rename'] as $field_name => $field) {
                $rename[$field['name']] = $field_name;
            }
        }
        if (!empty($changes['change']) && is_array($changes['change'])) {
            foreach ($changes['change'] as $field_name => $field) {
                if ($query) {
                    $query.= ', ';
                }
                if (isset($rename[$field_name])) {
                    $old_field_name = $rename[$field_name];
                    unset($rename[$field_name]);
                } else {
                    $old_field_name = $field_name;
                }
                $old_field_name = $db->quoteIdentifier($old_field_name, true);
                $query.= "CHANGE $old_field_name " . $db->getDeclaration($field['definition']['type'], $field_name, $field['definition']);
            }
        }
        if (!empty($rename) && is_array($rename)) {
            foreach ($rename as $rename_name => $renamed_field) {
                if ($query) {
                    $query.= ', ';
                }
                $field = $changes['rename'][$renamed_field];
                $renamed_field = $db->quoteIdentifier($renamed_field, true);
                $query.= 'CHANGE ' . $renamed_field . ' ' . $db->getDeclaration($field['definition']['type'], $field['name'], $field['definition']);
            }
        }
        if (!$query) {
            return MDB2_OK;
        }
        $name = $db->quoteIdentifier($name, true);
        return $db->exec("ALTER TABLE $name $query");
    }
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $result = $db->queryCol('SHOW DATABASES');
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->queryCol('SELECT DISTINCT USER FROM mysql.USER');
    }
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = "SELECT name FROM mysql.proc";
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function listTableTriggers($table = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'SHOW TRIGGERS';
        if (!is_null($table)) {
            $table = $db->quote($table, 'text');
            $query .= " LIKE $table";
        }
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function listTables($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = "SHOW  TABLES";
        if (!is_null($database)) {
            $query .= " FROM $database";
        }
        $query.= "";
        $table_names = $db->queryAll($query, null, MDB2_FETCHMODE_ORDERED);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        foreach ($table_names as $table) {
            if (!$this->_fixSequenceName($table[0], true)) {
                $result[] = $table[0];
            }
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function listViews($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = 'SHOW FULL TABLES';
        if (!is_null($database)) {
            $query.= " FROM $database";
        }
        $query.= " WHERE Table_type = 'VIEW'";
        $result = $db->queryCol($query);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $result = $db->queryCol("SHOW COLUMNS FROM $table");
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
    function createIndex($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        $query = "CREATE INDEX $name ON $table";
        $fields = array();
        foreach ($definition['fields'] as $field => $fieldinfo) {
            if (!empty($fieldinfo['length'])) {
                $fields[] = $db->quoteIdentifier($field, true) . '(' . $fieldinfo['length'] . ')';
            } else {
                $fields[] = $db->quoteIdentifier($field, true);
            }
        }
        $query .= ' ('. implode(', ', $fields) . ')';
        return $db->exec($query);
    }
    function dropIndex($table, $name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        return $db->exec("DROP INDEX $name ON $table");
    }
    function listTableIndexes($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
                $non_unique = strtolower($non_unique);
            } else {
                $key_name = strtoupper($key_name);
                $non_unique = strtoupper($non_unique);
            }
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table";
        $indexes = $db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }
        $result = array();
        foreach ($indexes as $index_data) {
            if ($index_data[$non_unique] && ($index = $this->_fixIndexName($index_data[$key_name]))) {
                $result[$index] = true;
            }
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }
    function createConstraint($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $type = '';
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        if (!empty($definition['primary'])) {
            $type = 'PRIMARY';
            $name = 'KEY';
        } elseif (!empty($definition['unique'])) {
            $type = 'UNIQUE';
        }
        if (empty($type)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'invalid definition, could not create constraint', __FUNCTION__);
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "ALTER TABLE $table ADD $type $name";
        $fields = array();
        foreach (array_keys($definition['fields']) as $field) {
            $fields[] = $db->quoteIdentifier($field, true);
        }
        $query .= ' ('. implode(', ', $fields) . ')';
        return $db->exec($query);
    }
    function dropConstraint($table, $name, $primary = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        if ($primary || strtolower($name) == 'primary') {
            $query = "ALTER TABLE $table DROP PRIMARY KEY";
        } else {
            $name = $db->quoteIdentifier($db->getIndexName($name), true);
            $query = "ALTER TABLE $table DROP INDEX $name";
        }
        return $db->exec($query);
    }
    function listTableConstraints($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
                $non_unique = strtolower($non_unique);
            } else {
                $key_name = strtoupper($key_name);
                $non_unique = strtoupper($non_unique);
            }
        }
        $table = $db->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table";
        $indexes = $db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }
        $result = array();
        foreach ($indexes as $index_data) {
            if (!$index_data[$non_unique]) {
                if ($index_data[$key_name] !== 'PRIMARY') {
                    $index = $this->_fixIndexName($index_data[$key_name]);
                } else {
                    $index = 'PRIMARY';
                }
                if (!empty($index)) {
                    $result[$index] = true;
                }
            }
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_change_key_case($result, $db->options['field_case']);
        }
        return array_keys($result);
    }
    function createSequence($seq_name, $start = 1, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $sequence_name = $db->quoteIdentifier($db->getSequenceName($seq_name), true);
        $seqcol_name = $db->quoteIdentifier($db->options['seqcol_name'], true);
        $options_strings = array();
        if (!empty($options['comment'])) {
            $options_strings['comment'] = 'COMMENT = '.$db->quote($options['comment'], 'text');
        }
        if (!empty($options['charset'])) {
            $options_strings['charset'] = 'DEFAULT CHARACTER SET '.$options['charset'];
            if (!empty($options['collate'])) {
                $options_strings['charset'].= ' COLLATE '.$options['collate'];
            }
        }
        $type = false;
        if (!empty($options['type'])) {
            $type = $options['type'];
        } elseif ($db->options['default_table_type']) {
            $type = $db->options['default_table_type'];
        }
        if ($type) {
            $options_strings[] = "ENGINE = $type";
        }
        $query = "CREATE TABLE $sequence_name ($seqcol_name INT NOT NULL AUTO_INCREMENT, PRIMARY KEY ($seqcol_name))";
        if (!empty($options_strings)) {
            $query .= ' '.implode(' ', $options_strings);
        }
        $res = $db->exec($query);
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($start == 1) {
            return MDB2_OK;
        }
        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (".($start-1).')';
        $res = $db->exec($query);
        if (!PEAR::isError($res)) {
            return MDB2_OK;
        }
        $result = $db->exec("DROP TABLE $sequence_name");
        if (PEAR::isError($result)) {
            return $db->raiseError($result, null, null,
                'could not drop inconsistent sequence table', __FUNCTION__);
        }
        return $db->raiseError($res, null, null,
            'could not create sequence table', __FUNCTION__);
    }
    function dropSequence($seq_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $sequence_name = $db->quoteIdentifier($db->getSequenceName($seq_name), true);
        return $db->exec("DROP TABLE $sequence_name");
    }
    function listSequences($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $query = "SHOW TABLES";
        if (!is_null($database)) {
            $query .= " FROM $database";
        }
        $table_names = $db->queryCol($query);
        if (PEAR::isError($table_names)) {
            return $table_names;
        }
        $result = array();
        foreach ($table_names as $table_name) {
            if ($sqn = $this->_fixSequenceName($table_name, true)) {
                $result[] = $sqn;
            }
        }
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $result = array_map(($db->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }
        return $result;
    }
}
?>
