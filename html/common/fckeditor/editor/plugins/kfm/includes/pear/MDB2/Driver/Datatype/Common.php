<?php
require_once 'MDB2/LOB.php';
class MDB2_Driver_Datatype_Common extends MDB2_Module_Common
{
    var $valid_default_values = array(
        'text'      => '',
        'boolean'   => true,
        'integer'   => 0,
        'decimal'   => 0.0,
        'float'     => 0.0,
        'timestamp' => '1970-01-01 00:00:00',
        'time'      => '00:00:00',
        'date'      => '1970-01-01',
        'clob'      => '',
        'blob'      => '',
    );
    var $lobs = array();
    function getValidTypes()
    {
        $types = $this->valid_default_values;
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!empty($db->options['datatype_map'])) {
            foreach ($db->options['datatype_map'] as $type => $mapped_type) {
                if (array_key_exists($mapped_type, $types)) {
                    $types[$type] = $types[$mapped_type];
                } elseif (!empty($db->options['datatype_map_callback'][$type])) {
                    $parameter = array('type' => $type, 'mapped_type' => $mapped_type);
                    $default =  call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
                    $types[$type] = $default;
                }
            }
        }
        return $types;
    }
    function checkResultTypes($types)
    {
        $types = is_array($types) ? $types : array($types);
        foreach ($types as $key => $type) {
            if (!isset($this->valid_default_values[$type])) {
                $db =& $this->getDBInstance();
                if (PEAR::isError($db)) {
                    return $db;
                }
                if (empty($db->options['datatype_map'][$type])) {
                    return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                        $type.' for '.$key.' is not a supported column type', __FUNCTION__);
                }
            }
        }
        return $types;
    }
    function _baseConvertResult($value, $type, $rtrim = true)
    {
        switch ($type) {
        case 'text':
            if ($rtrim) {
                $value = rtrim($value);
            }
            return $value;
        case 'integer':
            return intval($value);
        case 'boolean':
            return !empty($value);
        case 'decimal':
            return $value;
        case 'float':
            return doubleval($value);
        case 'date':
            return $value;
        case 'time':
            return $value;
        case 'timestamp':
            return $value;
        case 'clob':
        case 'blob':
            $this->lobs[] = array(
                'buffer' => null,
                'position' => 0,
                'lob_index' => null,
                'endOfLOB' => false,
                'resource' => $value,
                'value' => null,
                'loaded' => false,
            );
            end($this->lobs);
            $lob_index = key($this->lobs);
            $this->lobs[$lob_index]['lob_index'] = $lob_index;
            return fopen('MDB2LOB:
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_INVALID, null, null,
            'attempt to convert result value to an unknown type :' . $type, __FUNCTION__);
    }
    function convertResult($value, $type, $rtrim = true)
    {
        if (is_null($value)) {
            return null;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = array('type' => $type, 'value' => $value, 'rtrim' => $rtrim);
                return call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
            }
        }
        return $this->_baseConvertResult($value, $type, $rtrim);
    }
    function convertResultRow($types, $row, $rtrim = true)
    {
        $types = $this->_sortResultFieldTypes(array_keys($row), $types);
        foreach ($row as $key => $value) {
            if (empty($types[$key])) {
                continue;
            }
            $value = $this->convertResult($row[$key], $types[$key], $rtrim);
            if (PEAR::isError($value)) {
                return $value;
            }
            $row[$key] = $value;
        }
        return $row;
    }
    function _sortResultFieldTypes($columns, $types)
    {
        $n_cols = count($columns);
        $n_types = count($types);
        if ($n_cols > $n_types) {
            for ($i= $n_cols - $n_types; $i >= 0; $i--) {
                $types[] = null;
            }
        }
        $sorted_types = array();
        foreach ($columns as $col) {
            $sorted_types[$col] = null;
        }
        foreach ($types as $name => $type) {
            if (array_key_exists($name, $sorted_types)) {
                $sorted_types[$name] = $type;
                unset($types[$name]);
            }
        }
        if (count($types)) {
            reset($types);
            foreach (array_keys($sorted_types) as $k) {
                if (is_null($sorted_types[$k])) {
                    $sorted_types[$k] = current($types);
                    next($types);
                }
            }
        }
        return $sorted_types;
    }
    function getDeclaration($type, $name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = array('type' => $type, 'name' => $name, 'field' => $field);
                return call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
            }
            $field['type'] = $type;
        }
        if (!method_exists($this, "_get{$type}Declaration")) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'type not defined: '.$type, __FUNCTION__);
        }
        return $this->{"_get{$type}Declaration"}($name, $field);
    }
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        switch ($field['type']) {
        case 'text':
            $length = !empty($field['length']) ? $field['length'] : $db->options['default_text_field_length'];
            $fixed = !empty($field['fixed']) ? $field['fixed'] : false;
            return $fixed ? ($length ? 'CHAR('.$length.')' : 'CHAR('.$db->options['default_text_field_length'].')')
                : ($length ? 'VARCHAR('.$length.')' : 'TEXT');
        case 'clob':
            return 'TEXT';
        case 'blob':
            return 'TEXT';
        case 'integer':
            return 'INT';
        case 'boolean':
            return 'INT';
        case 'date':
            return 'CHAR ('.strlen('YYYY-MM-DD').')';
        case 'time':
            return 'CHAR ('.strlen('HH:MM:SS').')';
        case 'timestamp':
            return 'CHAR ('.strlen('YYYY-MM-DD HH:MM:SS').')';
        case 'float':
            return 'TEXT';
        case 'decimal':
            return 'TEXT';
        }
        return '';
    }
    function _getDeclaration($name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $name = $db->quoteIdentifier($name, true);
        $declaration_options = $db->datatype->_getDeclarationOptions($field);
        if (PEAR::isError($declaration_options)) {
            return $declaration_options;
        }
        return $name.' '.$this->getTypeDeclaration($field).$declaration_options;
    }
    function _getDeclarationOptions($field)
    {
        $charset = empty($field['charset']) ? '' :
            ' '.$this->_getCharsetFieldDeclaration($field['charset']);
        $default = '';
        if (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $db =& $this->getDBInstance();
                if (PEAR::isError($db)) {
                    return $db;
                }
                if (empty($field['notnull'])) {
                    $field['default'] = null;
                } else {
                    $valid_default_values = $this->getValidTypes();
                    $field['default'] = $valid_default_values[$field['type']];
                }
                if ($field['default'] === ''
                    && ($db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL)
                ) {
                    $field['default'] = ' ';
                }
            }
            $default = ' DEFAULT '.$this->quote($field['default'], $field['type']);
        } elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }
        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $collation = empty($field['collation']) ? '' :
            ' '.$this->_getCollationFieldDeclaration($field['collation']);
        return $charset.$default.$notnull.$collation;
    }
    function _getCharsetFieldDeclaration($charset)
    {
        return '';
    }
    function _getCollationFieldDeclaration($collation)
    {
        return '';
    }
    function _getIntegerDeclaration($name, $field)
    {
        if (!empty($field['unsigned'])) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            $db->warnings[] = "unsigned integer field \"$name\" is being declared as signed integer";
        }
        return $this->_getDeclaration($name, $field);
    }
    function _getTextDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getCLOBDeclaration($name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $name = $db->quoteIdentifier($name, true);
        return $name.' '.$this->getTypeDeclaration($field).$notnull;
    }
    function _getBLOBDeclaration($name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $name = $db->quoteIdentifier($name, true);
        return $name.' '.$this->getTypeDeclaration($field).$notnull;
    }
    function _getBooleanDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getDateDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getTimestampDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getTimeDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getFloatDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function _getDecimalDeclaration($name, $field)
    {
        return $this->_getDeclaration($name, $field);
    }
    function compareDefinition($current, $previous)
    {
        $type = !empty($current['type']) ? $current['type'] : null;
        if (!method_exists($this, "_compare{$type}Definition")) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = array('current' => $current, 'previous' => $previous);
                $change =  call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
                return $change;
            }
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'type "'.$current['type'].'" is not yet supported', __FUNCTION__);
        }
        if (empty($previous['type']) || $previous['type'] != $type) {
            return $current;
        }
        $change = $this->{"_compare{$type}Definition"}($current, $previous);
        if ($previous['type'] != $type) {
            $change['type'] = true;
        }
        $previous_notnull = !empty($previous['notnull']) ? $previous['notnull'] : false;
        $notnull = !empty($current['notnull']) ? $current['notnull'] : false;
        if ($previous_notnull != $notnull) {
            $change['notnull'] = true;
        }
        $previous_default = array_key_exists('default', $previous) ? $previous['default'] :
            ($previous_notnull ? '' : null);
        $default = array_key_exists('default', $current) ? $current['default'] :
            ($notnull ? '' : null);
        if ($previous_default !== $default) {
            $change['default'] = true;
        }
        return $change;
    }
    function _compareIntegerDefinition($current, $previous)
    {
        $change = array();
        $previous_unsigned = !empty($previous['unsigned']) ? $previous['unsigned'] : false;
        $unsigned = !empty($current['unsigned']) ? $current['unsigned'] : false;
        if ($previous_unsigned != $unsigned) {
            $change['unsigned'] = true;
        }
        $previous_autoincrement = !empty($previous['autoincrement']) ? $previous['autoincrement'] : false;
        $autoincrement = !empty($current['autoincrement']) ? $current['autoincrement'] : false;
        if ($previous_autoincrement != $autoincrement) {
            $change['autoincrement'] = true;
        }
        return $change;
    }
    function _compareTextDefinition($current, $previous)
    {
        $change = array();
        $previous_length = !empty($previous['length']) ? $previous['length'] : 0;
        $length = !empty($current['length']) ? $current['length'] : 0;
        if ($previous_length != $length) {
            $change['length'] = true;
        }
        $previous_fixed = !empty($previous['fixed']) ? $previous['fixed'] : 0;
        $fixed = !empty($current['fixed']) ? $current['fixed'] : 0;
        if ($previous_fixed != $fixed) {
            $change['fixed'] = true;
        }
        return $change;
    }
    function _compareCLOBDefinition($current, $previous)
    {
        return $this->_compareTextDefinition($current, $previous);
    }
    function _compareBLOBDefinition($current, $previous)
    {
        return $this->_compareTextDefinition($current, $previous);
    }
    function _compareDateDefinition($current, $previous)
    {
        return array();
    }
    function _compareTimeDefinition($current, $previous)
    {
        return array();
    }
    function _compareTimestampDefinition($current, $previous)
    {
        return array();
    }
    function _compareBooleanDefinition($current, $previous)
    {
        return array();
    }
    function _compareFloatDefinition($current, $previous)
    {
        return array();
    }
    function _compareDecimalDefinition($current, $previous)
    {
        return array();
    }
    function quote($value, $type = null, $quote = true, $escape_wildcards = false)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (is_null($value)
            || ($value === '' && $db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL)
        ) {
            if (!$quote) {
                return null;
            }
            return 'NULL';
        }
        if (is_null($type)) {
            switch (gettype($value)) {
            case 'integer':
                $type = 'integer';
                break;
            case 'double':
                $type = 'decimal';
                break;
            case 'boolean':
                $type = 'boolean';
                break;
            case 'array':
                 $value = serialize($value);
            case 'object':
                 $type = 'text';
                break;
            default:
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
                    $type = 'timestamp';
                } elseif (preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $type = 'time';
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $type = 'date';
                } else {
                    $type = 'text';
                }
                break;
            }
        } elseif (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = array('type' => $type, 'value' => $value, 'quote' => $quote, 'escape_wildcards' => $escape_wildcards);
                return call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
            }
        }
        if (!method_exists($this, "_quote{$type}")) {
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'type not defined: '.$type, __FUNCTION__);
        }
        $value = $this->{"_quote{$type}"}($value, $quote, $escape_wildcards);
        if ($quote && $escape_wildcards && $db->string_quoting['escape_pattern']
            && $db->string_quoting['escape'] !== $db->string_quoting['escape_pattern']
        ) {
            $value.= $this->patternEscapeString();
        }
        return $value;
    }
    function _quoteInteger($value, $quote, $escape_wildcards)
    {
        return (int)$value;
    }
    function _quoteText($value, $quote, $escape_wildcards)
    {
        if (!$quote) {
            return $value;
        }
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $value = $db->escape($value, $escape_wildcards);
        if (PEAR::isError($value)) {
            return $value;
        }
        return "'".$value."'";
    }
    function _readFile($value)
    {
        $close = false;
        if (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
            $close = true;
            if ($match[1] == 'file:
                $value = $match[2];
            }
            $value = @fopen($value, 'r');
        }
        if (is_resource($value)) {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            $fp = $value;
            $value = '';
            while (!@feof($fp)) {
                $value.= @fread($fp, $db->options['lob_buffer_length']);
            }
            if ($close) {
                @fclose($fp);
            }
        }
        return $value;
    }
    function _quoteLOB($value, $quote, $escape_wildcards)
    {
        $value = $this->_readFile($value);
        if (PEAR::isError($value)) {
            return $value;
        }
        return $this->_quoteText($value, $quote, $escape_wildcards);
    }
    function _quoteCLOB($value, $quote, $escape_wildcards)
    {
        return $this->_quoteLOB($value, $quote, $escape_wildcards);
    }
    function _quoteBLOB($value, $quote, $escape_wildcards)
    {
        return $this->_quoteLOB($value, $quote, $escape_wildcards);
    }
    function _quoteBoolean($value, $quote, $escape_wildcards)
    {
        return ($value ? 1 : 0);
    }
    function _quoteDate($value, $quote, $escape_wildcards)
    {
        if ($value === 'CURRENT_DATE') {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if (isset($db->function) && is_a($db->function, 'MDB2_Driver_Function_Common')) {
                return $db->function->now('date');
            }
            return 'CURRENT_DATE';
        }
        return $this->_quoteText($value, $quote, $escape_wildcards);
    }
    function _quoteTimestamp($value, $quote, $escape_wildcards)
    {
        if ($value === 'CURRENT_TIMESTAMP') {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if (isset($db->function) && is_a($db->function, 'MDB2_Driver_Function_Common')) {
                return $db->function->now('timestamp');
            }
            return 'CURRENT_TIMESTAMP';
        }
        return $this->_quoteText($value, $quote, $escape_wildcards);
    }
    function _quoteTime($value, $quote, $escape_wildcards)
    {
        if ($value === 'CURRENT_TIME') {
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            if (isset($db->function) && is_a($db->function, 'MDB2_Driver_Function_Common')) {
                return $db->function->now('time');
            }
            return 'CURRENT_TIME';
        }
        return $this->_quoteText($value, $quote, $escape_wildcards);
    }
    function _quoteFloat($value, $quote, $escape_wildcards)
    {
        if (preg_match('/^(.*)e([-+])(\d+)$/i', $value, $matches)) {
            $decimal = $this->_quoteDecimal($matches[1], $quote, $escape_wildcards);
            $sign = $matches[2];
            $exponent = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            $value = $decimal.'E'.$sign.$exponent;
        } else {
            $value = $this->_quoteDecimal($value, $quote, $escape_wildcards);
        }
        return $value;
    }
    function _quoteDecimal($value, $quote, $escape_wildcards)
    {
        $value = (string)$value;
        $value = preg_replace('/[^\d\.,\-+eE]/', '', $value);
        if (preg_match('/[^.0-9]/', $value)) {
            if (strpos($value, ',')) {
                if (!strpos($value, '.')) {
                    $value = strrev(str_replace(',', '.', strrev($value)));
                } elseif (strpos($value, '.') && strpos($value, '.') < strpos($value, ',')) {
                    $value = str_replace('.', '', $value);
                    $value = strrev(str_replace(',', '.', strrev($value)));
                } else {
                    $value = str_replace(',', '', $value);
                }
            }
        }
        return $value;
    }
    function writeLOBToFile($lob, $file)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (preg_match('/^(\w+:\/\/)(.*)$/', $file, $match)) {
            if ($match[1] == 'file:
                $file = $match[2];
            }
        }
        $fp = @fopen($file, 'wb');
        while (!@feof($lob)) {
            $result = @fread($lob, $db->options['lob_buffer_length']);
            $read = strlen($result);
            if (@fwrite($fp, $result, $read) != $read) {
                @fclose($fp);
                return $db->raiseError(MDB2_ERROR, null, null,
                    'could not write to the output file', __FUNCTION__);
            }
        }
        @fclose($fp);
        return MDB2_OK;
    }
    function _retrieveLOB(&$lob)
    {
        if (is_null($lob['value'])) {
            $lob['value'] = $lob['resource'];
        }
        $lob['loaded'] = true;
        return MDB2_OK;
    }
    function _readLOB($lob, $length)
    {
        return substr($lob['value'], $lob['position'], $length);
    }
    function _endOfLOB($lob)
    {
        return $lob['endOfLOB'];
    }
    function destroyLOB($lob)
    {
        $lob_data = stream_get_meta_data($lob);
        $lob_index = $lob_data['wrapper_data']->lob_index;
        fclose($lob);
        if (isset($this->lobs[$lob_index])) {
            $this->_destroyLOB($this->lobs[$lob_index]);
            unset($this->lobs[$lob_index]);
        }
        return MDB2_OK;
    }
    function _destroyLOB(&$lob)
    {
        return MDB2_OK;
    }
    function implodeArray($array, $type = false)
    {
        if (!is_array($array) || empty($array)) {
            return 'NULL';
        }
        if ($type) {
            foreach ($array as $value) {
                $return[] = $this->quote($value, $type);
            }
        } else {
            $return = $array;
        }
        return implode(', ', $return);
    }
    function matchPattern($pattern, $operator = null, $field = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $match = '';
        if (!is_null($operator)) {
            $operator = strtoupper($operator);
            switch ($operator) {
            case 'ILIKE':
                if (is_null($field)) {
                    return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                        'case insensitive LIKE matching requires passing the field name', __FUNCTION__);
                }
                $db->loadModule('Function', null, true);
                $match = $db->function->lower($field).' LIKE ';
                break;
            case 'LIKE':
                $match = is_null($field) ? 'LIKE ' : $field.' LIKE ';
                break;
            default:
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'not a supported operator type:'. $operator, __FUNCTION__);
            }
        }
        $match.= "'";
        foreach ($pattern as $key => $value) {
            if ($key % 2) {
                $match.= $value;
            } else {
                if ($operator === 'ILIKE') {
                    $value = strtolower($value);
                }
                $escaped = $db->escape($value);
                if (PEAR::isError($escaped)) {
                    return $escaped;
                }
                $match.= $db->escapePattern($escaped);
            }
        }
        $match.= "'";
        $match.= $this->patternEscapeString();
        return $match;
    }
    function patternEscapeString()
    {
        return '';
    }
    function mapNativeDatatype($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $db_type = strtok($field['type'], '(), ');
        if (!empty($db->options['nativetype_map_callback'][$db_type])) {
            return call_user_func_array($db->options['nativetype_map_callback'][$db_type], array($db, $field));
        }
        return $this->_mapNativeDatatype($field);
    }
    function _mapNativeDatatype($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function mapPrepareDatatype($type)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        if (!empty($db->options['datatype_map'][$type])) {
            $type = $db->options['datatype_map'][$type];
            if (!empty($db->options['datatype_map_callback'][$type])) {
                $parameter = array('type' => $type);
                return call_user_func_array($db->options['datatype_map_callback'][$type], array(&$db, __FUNCTION__, $parameter));
            }
        }
        return $type;
    }
}
?>
