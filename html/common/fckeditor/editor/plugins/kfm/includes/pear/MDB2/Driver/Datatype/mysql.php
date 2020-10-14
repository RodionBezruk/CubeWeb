<?php
require_once 'MDB2/Driver/Datatype/Common.php';
class MDB2_Driver_Datatype_mysql extends MDB2_Driver_Datatype_Common
{
    function _getCharsetFieldDeclaration($charset)
    {
        return 'CHARACTER SET '.$charset;
    }
    function _getCollationFieldDeclaration($collation)
    {
        return 'COLLATE '.$collation;
    }
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        switch ($field['type']) {
        case 'text':
            if (empty($field['length']) && array_key_exists('default', $field)) {
                $field['length'] = $db->varchar_max_length;
            }
            $length = !empty($field['length']) ? $field['length'] : false;
            $fixed = !empty($field['fixed']) ? $field['fixed'] : false;
            return $fixed ? ($length ? 'CHAR('.$length.')' : 'CHAR(255)')
                : ($length ? 'VARCHAR('.$length.')' : 'TEXT');
        case 'clob':
            if (!empty($field['length'])) {
                $length = $field['length'];
                if ($length <= 255) {
                    return 'TINYTEXT';
                } elseif ($length <= 65532) {
                    return 'TEXT';
                } elseif ($length <= 16777215) {
                    return 'MEDIUMTEXT';
                }
            }
            return 'LONGTEXT';
        case 'blob':
            if (!empty($field['length'])) {
                $length = $field['length'];
                if ($length <= 255) {
                    return 'TINYBLOB';
                } elseif ($length <= 65532) {
                    return 'BLOB';
                } elseif ($length <= 16777215) {
                    return 'MEDIUMBLOB';
                }
            }
            return 'LONGBLOB';
        case 'integer':
            if (!empty($field['length'])) {
                $length = $field['length'];
                if ($length <= 1) {
                    return 'TINYINT';
                } elseif ($length == 2) {
                    return 'SMALLINT';
                } elseif ($length == 3) {
                    return 'MEDIUMINT';
                } elseif ($length == 4) {
                    return 'INT';
                } elseif ($length > 4) {
                    return 'BIGINT';
                }
            }
            return 'INT';
        case 'boolean':
            return 'TINYINT(1)';
        case 'date':
            return 'DATE';
        case 'time':
            return 'TIME';
        case 'timestamp':
            return 'DATETIME';
        case 'float':
            return 'DOUBLE';
        case 'decimal':
            $length = !empty($field['length']) ? $field['length'] : 18;
            $scale = !empty($field['scale']) ? $field['scale'] : $db->options['decimal_places'];
            return 'DECIMAL('.$length.','.$scale.')';
        }
        return '';
    }
    function _getIntegerDeclaration($name, $field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $default = $autoinc = '';
        if (!empty($field['autoincrement'])) {
            $autoinc = ' AUTO_INCREMENT PRIMARY KEY';
        } elseif (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $field['default'] = empty($field['notnull']) ? null : 0;
            }
            $default = ' DEFAULT '.$this->quote($field['default'], 'integer');
        } elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }
        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $unsigned = empty($field['unsigned']) ? '' : ' UNSIGNED';
        $name = $db->quoteIdentifier($name, true);
        return $name.' '.$this->getTypeDeclaration($field).$unsigned.$default.$notnull.$autoinc;
    }
    function matchPattern($pattern, $operator = null, $field = null)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $match = '';
        if (!is_null($operator)) {
            $field = is_null($field) ? '' : $field.' ';
            $operator = strtoupper($operator);
            switch ($operator) {
            case 'ILIKE':
                $match = $field.'LIKE ';
                break;
            case 'LIKE':
                $match = $field.'LIKE BINARY ';
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
                $match.= $db->escapePattern($db->escape($value));
            }
        }
        $match.= "'";
        $match.= $this->patternEscapeString();
        return $match;
    }
    function _mapNativeDatatype($field)
    {
        $db_type = strtolower($field['type']);
        $db_type = strtok($db_type, '(), ');
        if ($db_type == 'national') {
            $db_type = strtok('(), ');
        }
        if (!empty($field['length'])) {
            $length = strtok($field['length'], ', ');
            $decimal = strtok(', ');
        } else {
            $length = strtok('(), ');
            $decimal = strtok('(), ');
        }
        $type = array();
        $unsigned = $fixed = null;
        switch ($db_type) {
        case 'tinyint':
            $type[] = 'integer';
            $type[] = 'boolean';
            if (preg_match('/^(is|has)/', $field['name'])) {
                $type = array_reverse($type);
            }
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 1;
            break;
        case 'smallint':
            $type[] = 'integer';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 2;
            break;
        case 'mediumint':
            $type[] = 'integer';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 3;
            break;
        case 'int':
        case 'integer':
            $type[] = 'integer';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 4;
            break;
        case 'bigint':
            $type[] = 'integer';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 8;
            break;
        case 'tinytext':
        case 'mediumtext':
        case 'longtext':
        case 'text':
        case 'text':
        case 'varchar':
            $fixed = false;
        case 'string':
        case 'char':
            $type[] = 'text';
            if ($length == '1') {
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', $field['name'])) {
                    $type = array_reverse($type);
                }
            } elseif (strstr($db_type, 'text')) {
                $type[] = 'clob';
                if ($decimal == 'binary') {
                    $type[] = 'blob';
                }
            }
            if ($fixed !== false) {
                $fixed = true;
            }
            break;
        case 'enum':
            $type[] = 'text';
            preg_match_all('/\'.+\'/U', $field['type'], $matches);
            $length = 0;
            $fixed = false;
            if (is_array($matches)) {
                foreach ($matches[0] as $value) {
                    $length = max($length, strlen($value)-2);
                }
                if ($length == '1' && count($matches[0]) == 2) {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                }
            }
            $type[] = 'integer';
        case 'set':
            $fixed = false;
            $type[] = 'text';
            $type[] = 'integer';
            break;
        case 'date':
            $type[] = 'date';
            $length = null;
            break;
        case 'datetime':
        case 'timestamp':
            $type[] = 'timestamp';
            $length = null;
            break;
        case 'time':
            $type[] = 'time';
            $length = null;
            break;
        case 'float':
        case 'double':
        case 'real':
            $type[] = 'float';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            break;
        case 'unknown':
        case 'decimal':
        case 'numeric':
            $type[] = 'decimal';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            if ($decimal !== false) {
                $length = $length.','.$decimal;
            }
            break;
        case 'tinyblob':
        case 'mediumblob':
        case 'longblob':
        case 'blob':
            $type[] = 'blob';
            $length = null;
            break;
        case 'binary':
        case 'varbinary':
            $type[] = 'blob';
            break;
        case 'year':
            $type[] = 'integer';
            $type[] = 'date';
            $length = null;
            break;
        default:
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'unknown database attribute type: '.$db_type, __FUNCTION__);
        }
        if ((int)$length <= 0) {
            $length = null;
        }
        return array($type, $length, $unsigned, $fixed);
    }
}
?>
