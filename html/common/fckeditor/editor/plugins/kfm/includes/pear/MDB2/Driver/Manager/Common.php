<?php
class MDB2_Driver_Manager_Common extends MDB2_Module_Common
{
    function getFieldDeclarationList($fields)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!is_array($fields) || empty($fields)) {
            return $db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'missing any fields', __FUNCTION__);
        }
        foreach ($fields as $field_name => $field) {
            $query = $db->getDeclaration($field['type'], $field_name, $field);
            if (PEAR::isError($query)) {
                return $query;
            }
            $query_fields[] = $query;
        }
        return implode(', ', $query_fields);
    }
    function _fixSequenceName($sqn, $check = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $seq_pattern = '/^'.preg_replace('/%s/', '([a-z0-9_]+)', $db->options['seqname_format']).'$/i';
        $seq_name = preg_replace($seq_pattern, '\\1', $sqn);
        if ($seq_name && !strcasecmp($sqn, $db->getSequenceName($seq_name))) {
            return $seq_name;
        }
        if ($check) {
            return false;
        }
        return $sqn;
    }
    function _fixIndexName($idx)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $idx_pattern = '/^'.preg_replace('/%s/', '([a-z0-9_]+)', $db->options['idxname_format']).'$/i';
        $idx_name = preg_replace($idx_pattern, '\\1', $idx);
        if ($idx_name && !strcasecmp($idx, $db->getIndexName($idx_name))) {
            return $idx_name;
        }
        return $idx;
    }
    function createDatabase($database)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function dropDatabase($database)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function _getCreateTableQuery($name, $fields, $options = array())
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!$name) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'no valid table name specified', __FUNCTION__);
        }
        if (empty($fields)) {
            return $db->raiseError(MDB2_ERROR_CANNOT_CREATE, null, null,
                'no fields specified for table "'.$name.'"', __FUNCTION__);
        }
        $query_fields = $this->getFieldDeclarationList($fields);
        if (PEAR::isError($query_fields)) {
            return $query_fields;
        }
        if (!empty($options['primary'])) {
            $query_fields.= ', PRIMARY KEY ('.implode(', ', array_keys($options['primary'])).')';
        }
        $name = $db->quoteIdentifier($name, true);
        $result = 'CREATE ';
        if (!empty($options['temporary'])) {
            $result .= $this->_getTemporaryTableQuery();
        }
        $result .= " TABLE $name ($query_fields)";
        return $result;
    }
    function _getTemporaryTableQuery()
    {
        return 'TEMPORARY';
    }
    function createTable($name, $fields, $options = array())
    {
        $query = $this->_getCreateTableQuery($name, $fields, $options);
        if (PEAR::isError($query)) {
            return $query;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->exec($query);
    }
    function dropTable($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $name = $db->quoteIdentifier($name, true);
        return $db->exec("DROP TABLE $name");
    }
    function alterTable($name, $changes, $check)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listDatabases()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implementedd', __FUNCTION__);
    }
    function listUsers()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listViews($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listTableViews($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listTableTriggers($table = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listFunctions()
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listTables($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listTableFields($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
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
        foreach (array_keys($definition['fields']) as $field) {
            $fields[] = $db->quoteIdentifier($field, true);
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
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        return $db->exec("DROP INDEX $name");
    }
    function listTableIndexes($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function createConstraint($table, $name, $definition)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $table = $db->quoteIdentifier($table, true);
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        $query = "ALTER TABLE $table ADD CONSTRAINT $name";
        if (!empty($definition['primary'])) {
            $query.= ' PRIMARY KEY';
        } elseif (!empty($definition['unique'])) {
            $query.= ' UNIQUE';
        }
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
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        return $db->exec("ALTER TABLE $table DROP CONSTRAINT $name");
    }
    function listTableConstraints($table)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function createSequence($seq_name, $start = 1)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function dropSequence($name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function listSequences($database = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
}
?>
