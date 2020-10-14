<?php
define('MDB2_TABLEINFO_ORDER',      1);
define('MDB2_TABLEINFO_ORDERTABLE', 2);
define('MDB2_TABLEINFO_FULL',       3);
class MDB2_Driver_Reverse_Common extends MDB2_Module_Common
{
    function getTableFieldDefinition($table, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function getTableIndexDefinition($table, $index)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function getTableConstraintDefinition($table, $index)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function getSequenceDefinition($sequence)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $start = $db->currId($sequence);
        if (PEAR::isError($start)) {
            return $start;
        }
        if ($db->supports('current_id')) {
            $start++;
        } else {
            $db->warnings[] = 'database does not support getting current
                sequence value, the sequence value was incremented';
        }
        $definition = array();
        if ($start != 1) {
            $definition = array('start' => $start);
        }
        return $definition;
    }
    function getTriggerDefinition($trigger)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function tableInfo($result, $mode = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!is_string($result)) {
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'method not implemented', __FUNCTION__);
        }
        $db->loadModule('Manager', null, true);
        $fields = $db->manager->listTableFields($result);
        if (PEAR::isError($fields)) {
            return $fields;
        }
        $flags = array();
        $idxname_format = $db->getOption('idxname_format');
        $db->setOption('idxname_format', '%s');
        $indexes = $db->manager->listTableIndexes($result);
        if (PEAR::isError($indexes)) {
            $db->setOption('idxname_format', $idxname_format);
            return $indexes;
        }
        foreach ($indexes as $index) {
            $definition = $this->getTableIndexDefinition($result, $index);
            if (PEAR::isError($definition)) {
                $db->setOption('idxname_format', $idxname_format);
                return $definition;
            }
            if (count($definition['fields']) > 1) {
                foreach ($definition['fields'] as $field => $sort) {
                    $flags[$field] = 'multiple_key';
                }
            }
        }
        $constraints = $db->manager->listTableConstraints($result);
        if (PEAR::isError($constraints)) {
            return $constraints;
        }
        foreach ($constraints as $constraint) {
            $definition = $this->getTableConstraintDefinition($result, $constraint);
            if (PEAR::isError($definition)) {
                $db->setOption('idxname_format', $idxname_format);
                return $definition;
            }
            $flag = !empty($definition['primary'])
                ? 'primary_key' : (!empty($definition['unique'])
                    ? 'unique_key' : false);
            if ($flag) {
                foreach ($definition['fields'] as $field => $sort) {
                    if (empty($flags[$field]) || $flags[$field] != 'primary_key') {
                        $flags[$field] = $flag;
                    }
                }
            }
        }
        if ($mode) {
            $res['num_fields'] = count($fields);
        }
        foreach ($fields as $i => $field) {
            $definition = $this->getTableFieldDefinition($result, $field);
            if (PEAR::isError($definition)) {
                $db->setOption('idxname_format', $idxname_format);
                return $definition;
            }
            $res[$i] = $definition[0];
            $res[$i]['name'] = $field;
            $res[$i]['table'] = $result;
            $res[$i]['type'] = preg_replace('/^([a-z]+).*$/i', '\\1', trim($definition[0]['nativetype']));
            $res[$i]['flags'] = empty($flags[$field]) ? '' : $flags[$field];
            if (!empty($res[$i]['notnull'])) {
                $res[$i]['flags'].= ' not_null';
            }
            if (!empty($res[$i]['unsigned'])) {
                $res[$i]['flags'].= ' unsigned';
            }
            if (!empty($res[$i]['auto_increment'])) {
                $res[$i]['flags'].= ' autoincrement';
            }
            if (!empty($res[$i]['default'])) {
                $res[$i]['flags'].= ' default_'.rawurlencode($res[$i]['default']);
            }
            if ($mode & MDB2_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & MDB2_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }
        $db->setOption('idxname_format', $idxname_format);
        return $res;
    }
}
?>
