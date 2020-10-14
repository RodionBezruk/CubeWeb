<?php
class MDB2_Driver_mysql extends MDB2_Driver_Common
{
    var $string_quoting = array('start' => "'", 'end' => "'", 'escape' => '\\', 'escape_pattern' => '\\');
    var $identifier_quoting = array('start' => '`', 'end' => '`', 'escape' => '`');
    var $sql_comments = array(
        array('start' => '-- ', 'end' => "\n", 'escape' => false),
        array('start' => '#', 'end' => "\n", 'escape' => false),
        array('start' => '', 'escape' => false),
    );
    var $start_transaction = false;
    var $varchar_max_length = 255;
    function __construct()
    {
        parent::__construct();
        $this->phptype = 'mysql';
        $this->dbsyntax = 'mysql';
        $this->supported['sequences'] = 'emulated';
        $this->supported['indexes'] = true;
        $this->supported['affected_rows'] = true;
        $this->supported['transactions'] = false;
        $this->supported['savepoints'] = false;
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['current_id'] = 'emulated';
        $this->supported['limit_queries'] = true;
        $this->supported['LOBs'] = true;
        $this->supported['replace'] = true;
        $this->supported['sub_selects'] = 'emulated';
        $this->supported['auto_increment'] = true;
        $this->supported['primary_key'] = true;
        $this->supported['result_introspection'] = true;
        $this->supported['prepared_statements'] = 'emulated';
        $this->supported['identifier_quoting'] = true;
        $this->supported['pattern_escaping'] = true;
        $this->supported['new_link'] = true;
        $this->options['default_table_type'] = '';
    }
    function errorInfo($error = null)
    {
        if ($this->connection) {
            $native_code = @mysql_errno($this->connection);
            $native_msg  = @mysql_error($this->connection);
        } else {
            $native_code = @mysql_errno();
            $native_msg  = @mysql_error();
        }
        if (is_null($error)) {
            static $ecode_map;
            if (empty($ecode_map)) {
                $ecode_map = array(
                    1004 => MDB2_ERROR_CANNOT_CREATE,
                    1005 => MDB2_ERROR_CANNOT_CREATE,
                    1006 => MDB2_ERROR_CANNOT_CREATE,
                    1007 => MDB2_ERROR_ALREADY_EXISTS,
                    1008 => MDB2_ERROR_CANNOT_DROP,
                    1022 => MDB2_ERROR_ALREADY_EXISTS,
                    1044 => MDB2_ERROR_ACCESS_VIOLATION,
                    1046 => MDB2_ERROR_NODBSELECTED,
                    1048 => MDB2_ERROR_CONSTRAINT,
                    1049 => MDB2_ERROR_NOSUCHDB,
                    1050 => MDB2_ERROR_ALREADY_EXISTS,
                    1051 => MDB2_ERROR_NOSUCHTABLE,
                    1054 => MDB2_ERROR_NOSUCHFIELD,
                    1061 => MDB2_ERROR_ALREADY_EXISTS,
                    1062 => MDB2_ERROR_ALREADY_EXISTS,
                    1064 => MDB2_ERROR_SYNTAX,
                    1091 => MDB2_ERROR_NOT_FOUND,
                    1100 => MDB2_ERROR_NOT_LOCKED,
                    1136 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                    1142 => MDB2_ERROR_ACCESS_VIOLATION,
                    1146 => MDB2_ERROR_NOSUCHTABLE,
                    1216 => MDB2_ERROR_CONSTRAINT,
                    1217 => MDB2_ERROR_CONSTRAINT,
                    1356 => MDB2_ERROR_DIVZERO,
                    1451 => MDB2_ERROR_CONSTRAINT,
                    1452 => MDB2_ERROR_CONSTRAINT,
                );
            }
            if ($this->options['portability'] & MDB2_PORTABILITY_ERRORS) {
                $ecode_map[1022] = MDB2_ERROR_CONSTRAINT;
                $ecode_map[1048] = MDB2_ERROR_CONSTRAINT_NOT_NULL;
                $ecode_map[1062] = MDB2_ERROR_CONSTRAINT;
            } else {
                $ecode_map[1022] = MDB2_ERROR_ALREADY_EXISTS;
                $ecode_map[1048] = MDB2_ERROR_CONSTRAINT;
                $ecode_map[1062] = MDB2_ERROR_ALREADY_EXISTS;
            }
            if (isset($ecode_map[$native_code])) {
                $error = $ecode_map[$native_code];
            }
        }
        return array($error, $native_code, $native_msg);
    }
    function escape($text, $escape_wildcards = false)
    {
        if ($escape_wildcards) {
            $text = $this->escapePattern($text);
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $text = @mysql_real_escape_string($text, $connection);
        return $text;
    }
    function beginTransaction($savepoint = null)
    {
        $this->debug('Starting transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        $this->_getServerCapabilities();
        if (!is_null($savepoint)) {
            if (!$this->supports('savepoints')) {
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'savepoints are not supported', __FUNCTION__);
            }
            if (!$this->in_transaction) {
                return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                    'savepoint cannot be released when changes are auto committed', __FUNCTION__);
            }
            $query = 'SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        } elseif ($this->in_transaction) {
            return MDB2_OK;  
        }
        if (!$this->destructor_registered && $this->opened_persistent) {
            $this->destructor_registered = true;
            register_shutdown_function('MDB2_closeOpenTransactions');
        }
        $query = $this->start_transaction ? 'START TRANSACTION' : 'SET AUTOCOMMIT = 1';
        $result =& $this->_doQuery($query, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $this->in_transaction = true;
        return MDB2_OK;
    }
    function commit($savepoint = null)
    {
        $this->debug('Committing transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'commit/release savepoint cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            if (!$this->supports('savepoints')) {
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'savepoints are not supported', __FUNCTION__);
            }
            $server_info = $this->getServerVersion();
            if (version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '5.0.3', '<')) {
                return MDB2_OK;
            }
            $query = 'RELEASE SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        }
        if (!$this->supports('transactions')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'transactions are not supported', __FUNCTION__);
        }
        $result =& $this->_doQuery('COMMIT', true);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$this->start_transaction) {
            $query = 'SET AUTOCOMMIT = 0';
            $result =& $this->_doQuery($query, true);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }
    function rollback($savepoint = null)
    {
        $this->debug('Rolling back transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'rollback cannot be done changes are auto committed', __FUNCTION__);
        }
        if (!is_null($savepoint)) {
            if (!$this->supports('savepoints')) {
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'savepoints are not supported', __FUNCTION__);
            }
            $query = 'ROLLBACK TO SAVEPOINT '.$savepoint;
            return $this->_doQuery($query, true);
        }
        $query = 'ROLLBACK';
        $result =& $this->_doQuery($query, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        if (!$this->start_transaction) {
            $query = 'SET AUTOCOMMIT = 0';
            $result =& $this->_doQuery($query, true);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }
    function setTransactionIsolation($isolation)
    {
        $this->debug('Setting transaction isolation level', __FUNCTION__, array('is_manip' => true));
        if (!$this->supports('transactions')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'transactions are not supported', __FUNCTION__);
        }
        switch ($isolation) {
        case 'READ UNCOMMITTED':
        case 'READ COMMITTED':
        case 'REPEATABLE READ':
        case 'SERIALIZABLE':
            break;
        default:
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'isolation level is not supported: '.$isolation, __FUNCTION__);
        }
        $query = "SET SESSION TRANSACTION ISOLATION LEVEL $isolation";
        return $this->_doQuery($query, true);
    }
    function connect()
    {
        if (is_resource($this->connection)) {
            if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
                && $this->opened_persistent == $this->options['persistent']
                && $this->connected_database_name == $this->database_name
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }
        if (!PEAR::loadExtension($this->phptype)) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'extension '.$this->phptype.' is not compiled into PHP', __FUNCTION__);
        }
        $params = array();
        if ($this->dsn['protocol'] && $this->dsn['protocol'] == 'unix') {
            $params[0] = ':' . $this->dsn['socket'];
        } else {
            $params[0] = $this->dsn['hostspec'] ? $this->dsn['hostspec']
                         : 'localhost';
            if ($this->dsn['port']) {
                $params[0].= ':' . $this->dsn['port'];
            }
        }
        $params[] = $this->dsn['username'] ? $this->dsn['username'] : null;
        $params[] = $this->dsn['password'] ? $this->dsn['password'] : null;
        if (!$this->options['persistent']) {
            if (isset($this->dsn['new_link'])
                && ($this->dsn['new_link'] == 'true' || $this->dsn['new_link'] === true)
            ) {
                $params[] = true;
            } else {
                $params[] = false;
            }
        }
        if (version_compare(phpversion(), '4.3.0', '>=')) {
            $params[] = isset($this->dsn['client_flags'])
                ? $this->dsn['client_flags'] : null;
        }
        $connect_function = $this->options['persistent'] ? 'mysql_pconnect' : 'mysql_connect';
        $connection = @call_user_func_array($connect_function, $params);
        if (!$connection) {
            if (($err = @mysql_error()) != '') {
                return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
                    $err, __FUNCTION__);
            } else {
                return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
                    'unable to establish a connection', __FUNCTION__);
            }
        }
        if (!empty($this->dsn['charset'])) {
            $result = $this->setCharset($this->dsn['charset'], $connection);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        $this->connection = $connection;
        $this->connected_dsn = $this->dsn;
        $this->connected_database_name = '';
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;
        if ($this->database_name) {
            if ($this->database_name != $this->connected_database_name) {
                if (!@mysql_select_db($this->database_name, $connection)) {
                    $err = $this->raiseError(null, null, null,
                        'Could not select the database: '.$this->database_name, __FUNCTION__);
                    return $err;
                }
                $this->connected_database_name = $this->database_name;
            }
        }
        $this->supported['transactions'] = $this->options['use_transactions'];
        if ($this->options['default_table_type']) {
            switch (strtoupper($this->options['default_table_type'])) {
            case 'BLACKHOLE':
            case 'MEMORY':
            case 'ARCHIVE':
            case 'CSV':
            case 'HEAP':
            case 'ISAM':
            case 'MERGE':
            case 'MRG_ISAM':
            case 'ISAM':
            case 'MRG_MYISAM':
            case 'MYISAM':
                $this->supported['transactions'] = false;
                $this->warnings[] = $this->options['default_table_type'] .
                    ' is not a supported default table type';
                break;
            }
        }
        $this->_getServerCapabilities();
        return MDB2_OK;
    }
    function setCharset($charset, $connection = null)
    {
        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        $query = "SET NAMES '".mysql_real_escape_string($charset, $connection)."'";
        return $this->_doQuery($query, true, $connection);
    }
    function disconnect($force = true)
    {
        if (is_resource($this->connection)) {
            if ($this->in_transaction) {
                $dsn = $this->dsn;
                $database_name = $this->database_name;
                $persistent = $this->options['persistent'];
                $this->dsn = $this->connected_dsn;
                $this->database_name = $this->connected_database_name;
                $this->options['persistent'] = $this->opened_persistent;
                $this->rollback();
                $this->dsn = $dsn;
                $this->database_name = $database_name;
                $this->options['persistent'] = $persistent;
            }
            if (!$this->opened_persistent || $force) {
                @mysql_close($this->connection);
            }
        }
        return parent::disconnect($force);
    }
    function &_doQuery($query, $is_manip = false, $connection = null, $database_name = null)
    {
        $this->last_query = $query;
        $result = $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        if ($this->options['disable_query']) {
            $result = $is_manip ? 0 : null;
            return $result;
        }
        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        if (is_null($database_name)) {
            $database_name = $this->database_name;
        }
        if ($database_name) {
            if ($database_name != $this->connected_database_name) {
                if (!@mysql_select_db($database_name, $connection)) {
                    $err = $this->raiseError(null, null, null,
                        'Could not select the database: '.$database_name, __FUNCTION__);
                    return $err;
                }
                $this->connected_database_name = $database_name;
            }
        }
        $function = $this->options['result_buffering']
            ? 'mysql_query' : 'mysql_unbuffered_query';
        $result = @$function($query, $connection);
        if (!$result) {
            $err =& $this->raiseError(null, null, null,
                'Could not execute statement', __FUNCTION__);
            return $err;
        }
        $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }
    function _affectedRows($connection, $result = null)
    {
        if (is_null($connection)) {
            $connection = $this->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
        }
        return @mysql_affected_rows($connection);
    }
    function _modifyQuery($query, $is_manip, $limit, $offset)
    {
        if ($this->options['portability'] & MDB2_PORTABILITY_DELETE_COUNT) {
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $query)) {
                $query = preg_replace('/^\s*DELETE\s+FROM\s+(\S+)\s*$/',
                                      'DELETE FROM \1 WHERE 1=1', $query);
            }
        }
        if ($limit > 0
            && !preg_match('/LIMIT\s*\d(?:\s*(?:,|OFFSET)\s*\d+)?(?:[^\)]*)?$/i', $query)
        ) {
            $query = rtrim($query);
            if (substr($query, -1) == ';') {
                $query = substr($query, 0, -1);
            }
            $after = '';
            if (preg_match('/(\s+INTO\s+(?:OUT|DUMP)FILE\s.*)$/ims', $query, $matches)) {
                $after = $matches[0];
                $query = preg_replace('/(\s+INTO\s+(?:OUT|DUMP)FILE\s.*)$/ims', '', $query);
            } elseif (preg_match('/(\s+FOR\s+UPDATE\s*)$/i', $query, $matches)) {
               $after = $matches[0];
               $query = preg_replace('/(\s+FOR\s+UPDATE\s*)$/im', '', $query);
            } elseif (preg_match('/(\s+LOCK\s+IN\s+SHARE\s+MODE\s*)$/im', $query, $matches)) {
               $after = $matches[0];
               $query = preg_replace('/(\s+LOCK\s+IN\s+SHARE\s+MODE\s*)$/im', '', $query);
            }
            if ($is_manip) {
                return $query . " LIMIT $limit" . $after;
            } else {
                return $query . " LIMIT $offset, $limit" . $after;
            }
        }
        return $query;
    }
    function getServerVersion($native = false)
    {
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        if ($this->connected_server_info) {
            $server_info = $this->connected_server_info;
        } else {
            $server_info = @mysql_get_server_info($connection);
        }
        if (!$server_info) {
            return $this->raiseError(null, null, null,
                'Could not get server information', __FUNCTION__);
        }
        $this->connected_server_info = $server_info;
        if (!$native) {
            $tmp = explode('.', $server_info, 3);
            if (isset($tmp[2]) && strpos($tmp[2], '-')) {
                $tmp2 = explode('-', @$tmp[2], 2);
            } else {
                $tmp2[0] = isset($tmp[2]) ? $tmp[2] : null;
                $tmp2[1] = null;
            }
            $server_info = array(
                'major' => isset($tmp[0]) ? $tmp[0] : null,
                'minor' => isset($tmp[1]) ? $tmp[1] : null,
                'patch' => $tmp2[0],
                'extra' => $tmp2[1],
                'native' => $server_info,
            );
        }
        return $server_info;
    }
    function _getServerCapabilities()
    {
        static $already_checked = false;
        if (!$already_checked) {
            $already_checked = true;
            $this->supported['sub_selects'] = 'emulated';
            $this->supported['prepared_statements'] = 'emulated';
            $this->start_transaction = false;
            $this->varchar_max_length = 255;
            $server_info = $this->getServerVersion();
            if (is_array($server_info)) {
                if (!version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '4.1.0', '<')) {
                    $this->supported['sub_selects'] = true;
                    $this->supported['prepared_statements'] = true;
                }
                if (!version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '4.0.14', '<')
                    || !version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '4.1.1', '<')
                ) {
                    $this->supported['savepoints'] = true;
                }
                if (!version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '4.0.11', '<')) {
                    $this->start_transaction = true;
                }
                if (!version_compare($server_info['major'].'.'.$server_info['minor'].'.'.$server_info['patch'], '5.0.3', '<')) {
                    $this->varchar_max_length = 65532;
                }
            }
        }
    }
    function _skipUserDefinedVariable($query, $position)
    {
        $found = strpos(strrev(substr($query, 0, $position)), '@');
        if ($found === false) {
            return $position;
        }
        $pos = strlen($query) - strlen(substr($query, $position)) - $found - 1;
        $substring = substr($query, $pos, $position - $pos + 2);
        if (preg_match('/^@\w+:=$/', $substring)) {
            return $position + 1; 
        }
        return $position;
    }
    function &prepare($query, $types = null, $result_types = null, $lobs = array())
    {
        if ($this->options['emulate_prepared']
            || $this->supported['prepared_statements'] !== true
        ) {
            $obj =& parent::prepare($query, $types, $result_types, $lobs);
            return $obj;
        }
        $is_manip = ($result_types === MDB2_PREPARE_MANIP);
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);
        $result = $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (PEAR::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        $placeholder_type_guess = $placeholder_type = null;
        $question = '?';
        $colon = ':';
        $positions = array();
        $position = 0;
        while ($position < strlen($query)) {
            $q_position = strpos($query, $question, $position);
            $c_position = strpos($query, $colon, $position);
            if ($q_position && $c_position) {
                $p_position = min($q_position, $c_position);
            } elseif ($q_position) {
                $p_position = $q_position;
            } elseif ($c_position) {
                $p_position = $c_position;
            } else {
                break;
            }
            if (is_null($placeholder_type)) {
                $placeholder_type_guess = $query[$p_position];
            }
            $new_pos = $this->_skipDelimitedStrings($query, $position, $p_position);
            if (PEAR::isError($new_pos)) {
                return $new_pos;
            }
            if ($new_pos != $position) {
                $position = $new_pos;
                continue; 
            }
            if ($query[$position] == $placeholder_type_guess) {
                if (is_null($placeholder_type)) {
                    $placeholder_type = $query[$p_position];
                    $question = $colon = $placeholder_type;
                }
                if ($placeholder_type == ':') {
                    $new_pos = $this->_skipUserDefinedVariable($query, $position);
                    if ($new_pos != $position) {
                        $position = $new_pos;
                        continue; 
                    }
                    $parameter = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                    if ($parameter === '') {
                        $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                            'named parameter with an empty name', __FUNCTION__);
                        return $err;
                    }
                    $positions[$p_position] = $parameter;
                    $query = substr_replace($query, '?', $position, strlen($parameter)+1);
                } else {
                    $positions[$p_position] = count($positions);
                }
                $position = $p_position + 1;
            } else {
                $position = $p_position;
            }
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $statement_name = sprintf($this->options['statement_format'], $this->phptype, md5(time() + rand()));
        $query = "PREPARE $statement_name FROM ".$this->quote($query, 'text');
        $statement =& $this->_doQuery($query, true, $connection);
        if (PEAR::isError($statement)) {
            return $statement;
        }
        $class_name = 'MDB2_Statement_'.$this->phptype;
        $obj =& new $class_name($this, $statement_name, $positions, $query, $types, $result_types, $is_manip, $limit, $offset);
        $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'post', 'result' => $obj));
        return $obj;
    }
    function replace($table, $fields)
    {
        $count = count($fields);
        $query = $values = '';
        $keys = $colnum = 0;
        for (reset($fields); $colnum < $count; next($fields), $colnum++) {
            $name = key($fields);
            if ($colnum > 0) {
                $query .= ',';
                $values.= ',';
            }
            $query.= $name;
            if (isset($fields[$name]['null']) && $fields[$name]['null']) {
                $value = 'NULL';
            } else {
                $type = isset($fields[$name]['type']) ? $fields[$name]['type'] : null;
                $value = $this->quote($fields[$name]['value'], $type);
            }
            $values.= $value;
            if (isset($fields[$name]['key']) && $fields[$name]['key']) {
                if ($value === 'NULL') {
                    return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                        'key value '.$name.' may not be NULL', __FUNCTION__);
                }
                $keys++;
            }
        }
        if ($keys == 0) {
            return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                'not specified which fields are keys', __FUNCTION__);
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $query = "REPLACE INTO $table ($query) VALUES ($values)";
        $result =& $this->_doQuery($query, true, $connection);
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->_affectedRows($connection, $result);
    }
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq_name), true);
        $seqcol_name = $this->quoteIdentifier($this->options['seqcol_name'], true);
        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (NULL)";
        $this->expectError(MDB2_ERROR_NOSUCHTABLE);
        $result =& $this->_doQuery($query, true);
        $this->popExpect();
        if (PEAR::isError($result)) {
            if ($ondemand && $result->getCode() == MDB2_ERROR_NOSUCHTABLE) {
                $this->loadModule('Manager', null, true);
                $result = $this->manager->createSequence($seq_name);
                if (PEAR::isError($result)) {
                    return $this->raiseError($result, null, null,
                        'on demand sequence '.$seq_name.' could not be created', __FUNCTION__);
                } else {
                    return $this->nextID($seq_name, false);
                }
            }
            return $result;
        }
        $value = $this->lastInsertID();
        if (is_numeric($value)) {
            $query = "DELETE FROM $sequence_name WHERE $seqcol_name < $value";
            $result =& $this->_doQuery($query, true);
            if (PEAR::isError($result)) {
                $this->warnings[] = 'nextID: could not delete previous sequence table values from '.$seq_name;
            }
        }
        return $value;
    }
    function lastInsertID($table = null, $field = null)
    {
        return $this->queryOne('SELECT LAST_INSERT_ID()', 'integer');
    }
    function currID($seq_name)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq_name), true);
        $seqcol_name = $this->quoteIdentifier($this->options['seqcol_name'], true);
        $query = "SELECT MAX($seqcol_name) FROM $sequence_name";
        return $this->queryOne($query, 'integer');
    }
}
class MDB2_Result_mysql extends MDB2_Result_Common
{
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if (!is_null($rownum)) {
            $seek = $this->seek($rownum);
            if (PEAR::isError($seek)) {
                return $seek;
            }
        }
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
            $row = @mysql_fetch_assoc($this->result);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
           $row = @mysql_fetch_row($this->result);
        }
        if (!$row) {
            if ($this->result === false) {
                $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
                return $err;
            }
            $null = null;
            return $null;
        }
        $mode = $this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL;
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if (!empty($this->types)) {
            $row = $this->db->datatype->convertResultRow($this->types, $row, false);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = &new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }
    function _getColumnNames()
    {
        $columns = array();
        $numcols = $this->numCols();
        if (PEAR::isError($numcols)) {
            return $numcols;
        }
        for ($column = 0; $column < $numcols; $column++) {
            $column_name = @mysql_field_name($this->result, $column);
            $columns[$column_name] = $column;
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_change_key_case($columns, $this->db->options['field_case']);
        }
        return $columns;
    }
    function numCols()
    {
        $cols = @mysql_num_fields($this->result);
        if (is_null($cols)) {
            if ($this->result === false) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            } elseif (is_null($this->result)) {
                return count($this->types);
            }
            return $this->db->raiseError(null, null, null,
                'Could not get column count', __FUNCTION__);
        }
        return $cols;
    }
    function free()
    {
        if (is_resource($this->result) && $this->db->connection) {
            $free = @mysql_free_result($this->result);
            if ($free === false) {
                return $this->db->raiseError(null, null, null,
                    'Could not free result', __FUNCTION__);
            }
        }
        $this->result = false;
        return MDB2_OK;
    }
}
class MDB2_BufferedResult_mysql extends MDB2_Result_mysql
{
    function seek($rownum = 0)
    {
        if ($this->rownum != ($rownum - 1) && !@mysql_data_seek($this->result, $rownum)) {
            if ($this->result === false) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            } elseif (is_null($this->result)) {
                return MDB2_OK;
            }
            return $this->db->raiseError(MDB2_ERROR_INVALID, null, null,
                'tried to seek to an invalid row number ('.$rownum.')', __FUNCTION__);
        }
        $this->rownum = $rownum - 1;
        return MDB2_OK;
    }
    function valid()
    {
        $numrows = $this->numRows();
        if (PEAR::isError($numrows)) {
            return $numrows;
        }
        return $this->rownum < ($numrows - 1);
    }
    function numRows()
    {
        $rows = @mysql_num_rows($this->result);
        if (is_null($rows)) {
            if ($this->result === false) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            } elseif (is_null($this->result)) {
                return 0;
            }
            return $this->db->raiseError(null, null, null,
                'Could not get row count', __FUNCTION__);
        }
        return $rows;
    }
}
class MDB2_Statement_mysql extends MDB2_Statement_Common
{
    function &_execute($result_class = true, $result_wrap_class = false)
    {
        if (is_null($this->statement)) {
            $result =& parent::_execute($result_class, $result_wrap_class);
            return $result;
        }
        $this->db->last_query = $this->query;
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'pre', 'parameters' => $this->values));
        if ($this->db->getOption('disable_query')) {
            $result = $this->is_manip ? 0 : null;
            return $result;
        }
        $connection = $this->db->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $query = 'EXECUTE '.$this->statement;
        if (!empty($this->positions)) {
            $parameters = array();
            foreach ($this->positions as $parameter) {
                if (!array_key_exists($parameter, $this->values)) {
                    return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                        'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
                }
                $value = $this->values[$parameter];
                $type = array_key_exists($parameter, $this->types) ? $this->types[$parameter] : null;
                if (is_resource($value) || $type == 'clob' || $type == 'blob') {
                    if (!is_resource($value) && preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
                        if ($match[1] == 'file:
                            $value = $match[2];
                        }
                        $value = @fopen($value, 'r');
                        $close = true;
                    }
                    if (is_resource($value)) {
                        $data = '';
                        while (!@feof($value)) {
                            $data.= @fread($value, $this->db->options['lob_buffer_length']);
                        }
                        if ($close) {
                            @fclose($value);
                        }
                        $value = $data;
                    }
                }
                $quoted = $this->db->quote($value, $type);
                if (PEAR::isError($quoted)) {
                    return $quoted;
                }
                $param_query = 'SET @'.$parameter.' = '.$quoted;
                $result = $this->db->_doQuery($param_query, true, $connection);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
            $query.= ' USING @'.implode(', @', array_values($this->positions));
        }
        $result = $this->db->_doQuery($query, $this->is_manip, $connection);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($this->is_manip) {
            $affected_rows = $this->db->_affectedRows($connection, $result);
            return $affected_rows;
        }
        $result =& $this->db->_wrapResult($result, $this->result_types,
            $result_class, $result_wrap_class, $this->limit, $this->offset);
        $this->db->debug($this->query, 'execute', array('is_manip' => $this->is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }
    function free()
    {
        if (is_null($this->positions)) {
            return $this->db->raiseError(MDB2_ERROR, null, null,
                'Prepared statement has already been freed', __FUNCTION__);
        }
        $result = MDB2_OK;
        if (!is_null($this->statement)) {
            $connection = $this->db->getConnection();
            if (PEAR::isError($connection)) {
                return $connection;
            }
            $query = 'DEALLOCATE PREPARE '.$this->statement;
            $result = $this->db->_doQuery($query, true, $connection);
        }
        parent::free();
        return $result;
    }
}
?>
