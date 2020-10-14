<?php
require_once 'PEAR.php';
define('MDB2_OK',                      true);
define('MDB2_ERROR',                     -1);
define('MDB2_ERROR_SYNTAX',              -2);
define('MDB2_ERROR_CONSTRAINT',          -3);
define('MDB2_ERROR_NOT_FOUND',           -4);
define('MDB2_ERROR_ALREADY_EXISTS',      -5);
define('MDB2_ERROR_UNSUPPORTED',         -6);
define('MDB2_ERROR_MISMATCH',            -7);
define('MDB2_ERROR_INVALID',             -8);
define('MDB2_ERROR_NOT_CAPABLE',         -9);
define('MDB2_ERROR_TRUNCATED',          -10);
define('MDB2_ERROR_INVALID_NUMBER',     -11);
define('MDB2_ERROR_INVALID_DATE',       -12);
define('MDB2_ERROR_DIVZERO',            -13);
define('MDB2_ERROR_NODBSELECTED',       -14);
define('MDB2_ERROR_CANNOT_CREATE',      -15);
define('MDB2_ERROR_CANNOT_DELETE',      -16);
define('MDB2_ERROR_CANNOT_DROP',        -17);
define('MDB2_ERROR_NOSUCHTABLE',        -18);
define('MDB2_ERROR_NOSUCHFIELD',        -19);
define('MDB2_ERROR_NEED_MORE_DATA',     -20);
define('MDB2_ERROR_NOT_LOCKED',         -21);
define('MDB2_ERROR_VALUE_COUNT_ON_ROW', -22);
define('MDB2_ERROR_INVALID_DSN',        -23);
define('MDB2_ERROR_CONNECT_FAILED',     -24);
define('MDB2_ERROR_EXTENSION_NOT_FOUND',-25);
define('MDB2_ERROR_NOSUCHDB',           -26);
define('MDB2_ERROR_ACCESS_VIOLATION',   -27);
define('MDB2_ERROR_CANNOT_REPLACE',     -28);
define('MDB2_ERROR_CONSTRAINT_NOT_NULL',-29);
define('MDB2_ERROR_DEADLOCK',           -30);
define('MDB2_ERROR_CANNOT_ALTER',       -31);
define('MDB2_ERROR_MANAGER',            -32);
define('MDB2_ERROR_MANAGER_PARSE',      -33);
define('MDB2_ERROR_LOADMODULE',         -34);
define('MDB2_ERROR_INSUFFICIENT_DATA',  -35);
define('MDB2_PREPARE_MANIP', false);
define('MDB2_PREPARE_RESULT', null);
define('MDB2_FETCHMODE_DEFAULT', 0);
define('MDB2_FETCHMODE_ORDERED', 1);
define('MDB2_FETCHMODE_ASSOC', 2);
define('MDB2_FETCHMODE_OBJECT', 3);
define('MDB2_FETCHMODE_FLIPPED', 4);
define('MDB2_PORTABILITY_NONE', 0);
define('MDB2_PORTABILITY_FIX_CASE', 1);
define('MDB2_PORTABILITY_RTRIM', 2);
define('MDB2_PORTABILITY_DELETE_COUNT', 4);
define('MDB2_PORTABILITY_NUMROWS', 8);
define('MDB2_PORTABILITY_ERRORS', 16);
define('MDB2_PORTABILITY_EMPTY_TO_NULL', 32);
define('MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES', 64);
define('MDB2_PORTABILITY_ALL', 127);
$GLOBALS['_MDB2_databases'] = array();
$GLOBALS['_MDB2_dsninfo_default'] = array(
    'phptype'  => false,
    'dbsyntax' => false,
    'username' => false,
    'password' => false,
    'protocol' => false,
    'hostspec' => false,
    'port'     => false,
    'socket'   => false,
    'database' => false,
    'mode'     => false,
);
class MDB2
{
    function setOptions(&$db, $options)
    {
        if (is_array($options)) {
            foreach ($options as $option => $value) {
                $test = $db->setOption($option, $value);
                if (PEAR::isError($test)) {
                    return $test;
                }
            }
        }
        return MDB2_OK;
    }
    function classExists($classname)
    {
        if (version_compare(phpversion(), "5.0", ">=")) {
            return class_exists($classname, false);
        }
        return class_exists($classname);
    }
    function loadClass($class_name, $debug)
    {
        if (!MDB2::classExists($class_name)) {
            $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            if ($debug) {
                $include = include_once($file_name);
            } else {
                $include = @include_once($file_name);
            }
            if (!$include) {
                if (!MDB2::fileExists($file_name)) {
                    $msg = "unable to find package '$class_name' file '$file_name'";
                } else {
                    $msg = "unable to load class '$class_name' from file '$file_name'";
                }
                $err =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null, $msg);
                return $err;
            }
        }
        return MDB2_OK;
    }
    function &factory($dsn, $options = false)
    {
        $dsninfo = MDB2::parseDSN($dsn);
        if (empty($dsninfo['phptype'])) {
            $err =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND,
                null, null, 'no RDBMS driver specified');
            return $err;
        }
        $class_name = 'MDB2_Driver_'.$dsninfo['phptype'];
        $debug = (!empty($options['debug']));
        $err = MDB2::loadClass($class_name, $debug);
        if (PEAR::isError($err)) {
            return $err;
        }
        $db =& new $class_name();
        $db->setDSN($dsninfo);
        $err = MDB2::setOptions($db, $options);
        if (PEAR::isError($err)) {
            return $err;
        }
        return $db;
    }
    function &connect($dsn, $options = false)
    {
        $db =& MDB2::factory($dsn, $options);
        if (PEAR::isError($db)) {
            return $db;
        }
        $err = $db->connect();
        if (PEAR::isError($err)) {
            $dsn = $db->getDSN('string', 'xxx');
            $db->disconnect();
            $err->addUserInfo($dsn);
            return $err;
        }
        return $db;
    }
    function &singleton($dsn = null, $options = false)
    {
        if ($dsn) {
            $dsninfo = MDB2::parseDSN($dsn);
            $dsninfo = array_merge($GLOBALS['_MDB2_dsninfo_default'], $dsninfo);
            $keys = array_keys($GLOBALS['_MDB2_databases']);
            for ($i=0, $j=count($keys); $i<$j; ++$i) {
                if (isset($GLOBALS['_MDB2_databases'][$keys[$i]])) {
                    $tmp_dsn = $GLOBALS['_MDB2_databases'][$keys[$i]]->getDSN('array');
                    if (count(array_diff_assoc($tmp_dsn, $dsninfo)) == 0) {
                        MDB2::setOptions($GLOBALS['_MDB2_databases'][$keys[$i]], $options);
                        return $GLOBALS['_MDB2_databases'][$keys[$i]];
                    }
                }
            }
        } elseif (is_array($GLOBALS['_MDB2_databases']) && reset($GLOBALS['_MDB2_databases'])) {
            $db =& $GLOBALS['_MDB2_databases'][key($GLOBALS['_MDB2_databases'])];
            return $db;
        }
        $db =& MDB2::factory($dsn, $options);
        return $db;
    }
    function loadFile($file)
    {
        $file_name = 'MDB2'.DIRECTORY_SEPARATOR.$file.'.php';
        if (!MDB2::fileExists($file_name)) {
            return MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'unable to find: '.$file_name);
        }
        if (!include_once($file_name)) {
            return MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'unable to load driver class: '.$file_name);
        }
        return $file_name;
    }
    function apiVersion()
    {
        return '2.4.1';
    }
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'MDB2_Error', true);
        return $err;
    }
    function isError($data, $code = null)
    {
        if (is_a($data, 'MDB2_Error')) {
            if (is_null($code)) {
                return true;
            } elseif (is_string($code)) {
                return $data->getMessage() === $code;
            } else {
                $code = (array)$code;
                return in_array($data->getCode(), $code);
            }
        }
        return false;
    }
    function isConnection($value)
    {
        return is_a($value, 'MDB2_Driver_Common');
    }
    function isResult($value)
    {
        return is_a($value, 'MDB2_Result');
    }
    function isResultCommon($value)
    {
        return is_a($value, 'MDB2_Result_Common');
    }
    function isStatement($value)
    {
        return is_a($value, 'MDB2_Statement');
    }
    function errorMessage($value = null)
    {
        static $errorMessages;
        if (is_array($value)) {
            $errorMessages = $value;
            return MDB2_OK;
        }
        if (!isset($errorMessages)) {
            $errorMessages = array(
                MDB2_OK                       => 'no error',
                MDB2_ERROR                    => 'unknown error',
                MDB2_ERROR_ALREADY_EXISTS     => 'already exists',
                MDB2_ERROR_CANNOT_CREATE      => 'can not create',
                MDB2_ERROR_CANNOT_ALTER       => 'can not alter',
                MDB2_ERROR_CANNOT_REPLACE     => 'can not replace',
                MDB2_ERROR_CANNOT_DELETE      => 'can not delete',
                MDB2_ERROR_CANNOT_DROP        => 'can not drop',
                MDB2_ERROR_CONSTRAINT         => 'constraint violation',
                MDB2_ERROR_CONSTRAINT_NOT_NULL=> 'null value violates not-null constraint',
                MDB2_ERROR_DIVZERO            => 'division by zero',
                MDB2_ERROR_INVALID            => 'invalid',
                MDB2_ERROR_INVALID_DATE       => 'invalid date or time',
                MDB2_ERROR_INVALID_NUMBER     => 'invalid number',
                MDB2_ERROR_MISMATCH           => 'mismatch',
                MDB2_ERROR_NODBSELECTED       => 'no database selected',
                MDB2_ERROR_NOSUCHFIELD        => 'no such field',
                MDB2_ERROR_NOSUCHTABLE        => 'no such table',
                MDB2_ERROR_NOT_CAPABLE        => 'MDB2 backend not capable',
                MDB2_ERROR_NOT_FOUND          => 'not found',
                MDB2_ERROR_NOT_LOCKED         => 'not locked',
                MDB2_ERROR_SYNTAX             => 'syntax error',
                MDB2_ERROR_UNSUPPORTED        => 'not supported',
                MDB2_ERROR_VALUE_COUNT_ON_ROW => 'value count on row',
                MDB2_ERROR_INVALID_DSN        => 'invalid DSN',
                MDB2_ERROR_CONNECT_FAILED     => 'connect failed',
                MDB2_ERROR_NEED_MORE_DATA     => 'insufficient data supplied',
                MDB2_ERROR_EXTENSION_NOT_FOUND=> 'extension not found',
                MDB2_ERROR_NOSUCHDB           => 'no such database',
                MDB2_ERROR_ACCESS_VIOLATION   => 'insufficient permissions',
                MDB2_ERROR_LOADMODULE         => 'error while including on demand module',
                MDB2_ERROR_TRUNCATED          => 'truncated',
                MDB2_ERROR_DEADLOCK           => 'deadlock detected',
            );
        }
        if (is_null($value)) {
            return $errorMessages;
        }
        if (PEAR::isError($value)) {
            $value = $value->getCode();
        }
        return isset($errorMessages[$value]) ?
           $errorMessages[$value] : $errorMessages[MDB2_ERROR];
    }
    function parseDSN($dsn)
    {
        $parsed = $GLOBALS['_MDB2_dsninfo_default'];
        if (is_array($dsn)) {
            $dsn = array_merge($parsed, $dsn);
            if (!$dsn['dbsyntax']) {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }
        if (($pos = strpos($dsn, ':
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }
        if (!count($dsn)) {
            return $parsed;
        }
        if (($at = strrpos($dsn,'@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }
        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];
        } else {
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (   strpos($dsn, '
                && strpos($dsn, '/', 2) !== false
                && $parsed['phptype'] == 'oci8'
            ) {
                $proto_opts = $dsn;
                $dsn = null;
            } elseif (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if (strpos($proto_opts, ':') !== false) {
            list($proto_opts, $parsed['port']) = explode(':', $proto_opts);
        }
        if ($parsed['protocol'] == 'tcp') {
            $parsed['hostspec'] = $proto_opts;
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }
        if ($dsn) {
            if (($pos = strpos($dsn, '?')) === false) {
                $parsed['database'] = $dsn;
            } else {
                $parsed['database'] = substr($dsn, 0, $pos);
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else { 
                    $opts = array($dsn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }
        return $parsed;
    }
    function fileExists($file)
    {
        if (!@ini_get('safe_mode')) {
             $dirs = explode(PATH_SEPARATOR, ini_get('include_path'));
             foreach ($dirs as $dir) {
                 if (is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
                     return true;
                 }
            }
        } else {
            $fp = @fopen($file, 'r', true);
            if (is_resource($fp)) {
                @fclose($fp);
                return true;
            }
        }
        return false;
    }
}
class MDB2_Error extends PEAR_Error
{
    function MDB2_Error($code = MDB2_ERROR, $mode = PEAR_ERROR_RETURN,
              $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_null($code)) {
            $code = MDB2_ERROR;
        }
        $this->PEAR_Error('MDB2 Error: '.MDB2::errorMessage($code), $code,
            $mode, $level, $debuginfo);
    }
}
class MDB2_Driver_Common extends PEAR
{
    var $db_index = 0;
    var $dsn = array();
    var $connected_dsn = array();
    var $connection = 0;
    var $opened_persistent;
    var $database_name = '';
    var $connected_database_name = '';
    var $connected_server_info = '';
    var $supported = array(
        'sequences' => false,
        'indexes' => false,
        'affected_rows' => false,
        'summary_functions' => false,
        'order_by_text' => false,
        'transactions' => false,
        'savepoints' => false,
        'current_id' => false,
        'limit_queries' => false,
        'LOBs' => false,
        'replace' => false,
        'sub_selects' => false,
        'auto_increment' => false,
        'primary_key' => false,
        'result_introspection' => false,
        'prepared_statements' => false,
        'identifier_quoting' => false,
        'pattern_escaping' => false,
        'new_link' => false,
    );
    var $options = array(
        'ssl' => false,
        'field_case' => CASE_LOWER,
        'disable_query' => false,
        'result_class' => 'MDB2_Result_%s',
        'buffered_result_class' => 'MDB2_BufferedResult_%s',
        'result_wrap_class' => false,
        'result_buffering' => true,
        'fetch_class' => 'stdClass',
        'persistent' => false,
        'debug' => 0,
        'debug_handler' => 'MDB2_defaultDebugOutput',
        'debug_expanded_output' => false,
        'default_text_field_length' => 4096,
        'lob_buffer_length' => 8192,
        'log_line_break' => "\n",
        'idxname_format' => '%s_idx',
        'seqname_format' => '%s_seq',
        'savepoint_format' => 'MDB2_SAVEPOINT_%s',
        'statement_format' => 'MDB2_STATEMENT_%1$s_%2$s',
        'seqcol_name' => 'sequence',
        'quote_identifier' => false,
        'use_transactions' => true,
        'decimal_places' => 2,
        'portability' => MDB2_PORTABILITY_ALL,
        'modules' => array(
            'ex' => 'Extended',
            'dt' => 'Datatype',
            'mg' => 'Manager',
            'rv' => 'Reverse',
            'na' => 'Native',
            'fc' => 'Function',
        ),
        'emulate_prepared' => false,
        'datatype_map' => array(),
        'datatype_map_callback' => array(),
        'nativetype_map_callback' => array(),
    );
    var $string_quoting = array('start' => "'", 'end' => "'", 'escape' => false, 'escape_pattern' => false);
    var $identifier_quoting = array('start' => '"', 'end' => '"', 'escape' => '"');
    var $sql_comments = array(
        array('start' => '--', 'end' => "\n", 'escape' => false),
        array('start' => '', 'escape' => false),
    );
    var $wildcards = array('%', '_');
    var $as_keyword = ' AS ';
    var $warnings = array();
    var $debug_output = '';
    var $in_transaction = false;
    var $nested_transaction_counter = null;
    var $has_transaction_error = false;
    var $offset = 0;
    var $limit = 0;
    var $phptype;
    var $dbsyntax;
    var $last_query;
    var $fetchmode = MDB2_FETCHMODE_ORDERED;
    var $modules = array();
    var $destructor_registered = true;
    function __construct()
    {
        end($GLOBALS['_MDB2_databases']);
        $db_index = key($GLOBALS['_MDB2_databases']) + 1;
        $GLOBALS['_MDB2_databases'][$db_index] = &$this;
        $this->db_index = $db_index;
    }
    function MDB2_Driver_Common()
    {
        $this->destructor_registered = false;
        $this->__construct();
    }
    function __destruct()
    {
        $this->disconnect(false);
    }
    function free()
    {
        unset($GLOBALS['_MDB2_databases'][$this->db_index]);
        unset($this->db_index);
        return MDB2_OK;
    }
    function __toString()
    {
        $info = get_class($this);
        $info.= ': (phptype = '.$this->phptype.', dbsyntax = '.$this->dbsyntax.')';
        if ($this->connection) {
            $info.= ' [connected]';
        }
        return $info;
    }
    function errorInfo($error = null)
    {
        return array($error, null, null);
    }
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null, $method = null)
    {
        $userinfo = "[Error message: $userinfo]\n";
        if (PEAR::isError($code)) {
            if (is_null($mode) && !empty($this->_default_error_mode)) {
                $mode    = $this->_default_error_mode;
                $options = $this->_default_error_options;
            }
            if (is_null($userinfo)) {
                $userinfo = $code->getUserinfo();
            }
            $code = $code->getCode();
        } elseif ($code == MDB2_ERROR_NOT_FOUND) {
        } elseif (isset($this->connection)) {
            if (!empty($this->last_query)) {
                $userinfo.= "[Last executed query: {$this->last_query}]\n";
            }
            $native_errno = $native_msg = null;
            list($code, $native_errno, $native_msg) = $this->errorInfo($code);
            if (!is_null($native_errno) && $native_errno !== '') {
                $userinfo.= "[Native code: $native_errno]\n";
            }
            if (!is_null($native_msg) && $native_msg !== '') {
                $userinfo.= "[Native message: ". strip_tags($native_msg) ."]\n";
            }
            if (!is_null($method)) {
                $userinfo = $method.': '.$userinfo;
            }
        }
        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'MDB2_Error', true);
        if ($err->getMode() !== PEAR_ERROR_RETURN
            && isset($this->nested_transaction_counter) && !$this->has_transaction_error) {
            $this->has_transaction_error =& $err;
        }
        return $err;
    }
    function resetWarnings()
    {
        $this->warnings = array();
    }
    function getWarnings()
    {
        return array_reverse($this->warnings);
    }
    function setFetchMode($fetchmode, $object_class = 'stdClass')
    {
        switch ($fetchmode) {
        case MDB2_FETCHMODE_OBJECT:
            $this->options['fetch_class'] = $object_class;
        case MDB2_FETCHMODE_ORDERED:
        case MDB2_FETCHMODE_ASSOC:
            $this->fetchmode = $fetchmode;
            break;
        default:
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'invalid fetchmode mode', __FUNCTION__);
        }
        return MDB2_OK;
    }
    function setOption($option, $value)
    {
        if (array_key_exists($option, $this->options)) {
            $this->options[$option] = $value;
            return MDB2_OK;
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown option $option", __FUNCTION__);
    }
    function getOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown option $option", __FUNCTION__);
    }
    function debug($message, $scope = '', $context = array())
    {
        if ($this->options['debug'] && $this->options['debug_handler']) {
            if (!$this->options['debug_expanded_output']) {
                if (!empty($context['when']) && $context['when'] !== 'pre') {
                    return null;
                }
                $context = empty($context['is_manip']) ? false : $context['is_manip'];
            }
            return call_user_func_array($this->options['debug_handler'], array(&$this, $scope, $message, $context));
        }
        return null;
    }
    function getDebugOutput()
    {
        return $this->debug_output;
    }
    function escape($text, $escape_wildcards = false)
    {
        if ($escape_wildcards) {
            $text = $this->escapePattern($text);
        }
        $text = str_replace($this->string_quoting['end'], $this->string_quoting['escape'] . $this->string_quoting['end'], $text);
        return $text;
    }
    function escapePattern($text)
    {
        if ($this->string_quoting['escape_pattern']) {
            $text = str_replace($this->string_quoting['escape_pattern'], $this->string_quoting['escape_pattern'] . $this->string_quoting['escape_pattern'], $text);
            foreach ($this->wildcards as $wildcard) {
                $text = str_replace($wildcard, $this->string_quoting['escape_pattern'] . $wildcard, $text);
            }
        }
        return $text;
    }
    function quoteIdentifier($str, $check_option = false)
    {
        if ($check_option && !$this->options['quote_identifier']) {
            return $str;
        }
        $str = str_replace($this->identifier_quoting['end'], $this->identifier_quoting['escape'] . $this->identifier_quoting['end'], $str);
        return $this->identifier_quoting['start'] . $str . $this->identifier_quoting['end'];
    }
    function getAsKeyword()
    {
        return $this->as_keyword;
    }
    function getConnection()
    {
        $result = $this->connect();
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->connection;
    }
    function _fixResultArrayValues(&$row, $mode)
    {
        switch ($mode) {
        case MDB2_PORTABILITY_EMPTY_TO_NULL:
            foreach ($row as $key => $value) {
                if ($value === '') {
                    $row[$key] = null;
                }
            }
            break;
        case MDB2_PORTABILITY_RTRIM:
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    $row[$key] = rtrim($value);
                }
            }
            break;
        case MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES:
            $tmp_row = array();
            foreach ($row as $key => $value) {
                $tmp_row[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $row = $tmp_row;
            break;
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_EMPTY_TO_NULL):
            foreach ($row as $key => $value) {
                if ($value === '') {
                    $row[$key] = null;
                } elseif (is_string($value)) {
                    $row[$key] = rtrim($value);
                }
            }
            break;
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_row = array();
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    $value = rtrim($value);
                }
                $tmp_row[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $row = $tmp_row;
            break;
        case (MDB2_PORTABILITY_EMPTY_TO_NULL + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_row = array();
            foreach ($row as $key => $value) {
                if ($value === '') {
                    $value = null;
                }
                $tmp_row[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $row = $tmp_row;
            break;
        case (MDB2_PORTABILITY_RTRIM + MDB2_PORTABILITY_EMPTY_TO_NULL + MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES):
            $tmp_row = array();
            foreach ($row as $key => $value) {
                if ($value === '') {
                    $value = null;
                } elseif (is_string($value)) {
                    $value = rtrim($value);
                }
                $tmp_row[preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $key)] = $value;
            }
            $row = $tmp_row;
            break;
        }
    }
    function &loadModule($module, $property = null, $phptype_specific = null)
    {
        if (!$property) {
            $property = strtolower($module);
        }
        if (!isset($this->{$property})) {
            $version = $phptype_specific;
            if ($phptype_specific !== false) {
                $version = true;
                $class_name = 'MDB2_Driver_'.$module.'_'.$this->phptype;
                $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            }
            if ($phptype_specific === false
                || (!MDB2::classExists($class_name) && !MDB2::fileExists($file_name))
            ) {
                $version = false;
                $class_name = 'MDB2_'.$module;
                $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
            }
            $err = MDB2::loadClass($class_name, $this->getOption('debug'));
            if (PEAR::isError($err)) {
                return $err;
            }
            if ($version) {
                if (method_exists($class_name, 'getClassName')) {
                    $class_name_new = call_user_func(array($class_name, 'getClassName'), $this->db_index);
                    if ($class_name != $class_name_new) {
                        $class_name = $class_name_new;
                        $err = MDB2::loadClass($class_name, $this->getOption('debug'));
                        if (PEAR::isError($err)) {
                            return $err;
                        }
                    }
                }
            }
            if (!MDB2::classExists($class_name)) {
                $err =& $this->raiseError(MDB2_ERROR_LOADMODULE, null, null,
                    "unable to load module '$module' into property '$property'", __FUNCTION__);
                return $err;
            }
            $this->{$property} =& new $class_name($this->db_index);
            $this->modules[$module] =& $this->{$property};
            if ($version) {
                $this->loaded_version_modules[] = $property;
            }
        }
        return $this->{$property};
    }
    function __call($method, $params)
    {
        $module = null;
        if (preg_match('/^([a-z]+)([A-Z])(.*)$/', $method, $match)
            && isset($this->options['modules'][$match[1]])
        ) {
            $module = $this->options['modules'][$match[1]];
            $method = strtolower($match[2]).$match[3];
            if (!isset($this->modules[$module]) || !is_object($this->modules[$module])) {
                $result =& $this->loadModule($module);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        } else {
            foreach ($this->modules as $key => $foo) {
                if (is_object($this->modules[$key])
                    && method_exists($this->modules[$key], $method)
                ) {
                    $module = $key;
                    break;
                }
            }
        }
        if (!is_null($module)) {
            return call_user_func_array(array(&$this->modules[$module], $method), $params);
        }
        trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $method), E_USER_ERROR);
    }
    function beginTransaction($savepoint = null)
    {
        $this->debug('Starting transaction', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'transactions are not supported', __FUNCTION__);
    }
    function commit($savepoint = null)
    {
        $this->debug('Committing transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'commiting transactions is not supported', __FUNCTION__);
    }
    function rollback($savepoint = null)
    {
        $this->debug('Rolling back transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'rolling back transactions is not supported', __FUNCTION__);
    }
    function inTransaction($ignore_nested = false)
    {
        if (!$ignore_nested && isset($this->nested_transaction_counter)) {
            return $this->nested_transaction_counter;
        }
        return $this->in_transaction;
    }
    function setTransactionIsolation($isolation, $options = array())
    {
        $this->debug('Setting transaction isolation level', __FUNCTION__, array('is_manip' => true));
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'isolation level setting is not supported', __FUNCTION__);
    }
    function beginNestedTransaction()
    {
        if ($this->in_transaction) {
            ++$this->nested_transaction_counter;
            $savepoint = sprintf($this->options['savepoint_format'], $this->nested_transaction_counter);
            if ($this->supports('savepoints') && $savepoint) {
                return $this->beginTransaction($savepoint);
            }
            return MDB2_OK;
        }
        $this->has_transaction_error = false;
        $result = $this->beginTransaction();
        $this->nested_transaction_counter = 1;
        return $result;
    }
    function completeNestedTransaction($force_rollback = false)
    {
        if ($this->nested_transaction_counter > 1) {
            $savepoint = sprintf($this->options['savepoint_format'], $this->nested_transaction_counter);
            if ($this->supports('savepoints') && $savepoint) {
                if ($force_rollback || $this->has_transaction_error) {
                    $result = $this->rollback($savepoint);
                    if (!PEAR::isError($result)) {
                        $result = false;
                        $this->has_transaction_error = false;
                    }
                } else {
                    $result = $this->commit($savepoint);
                }
            } else {
                $result = MDB2_OK;
            }
            --$this->nested_transaction_counter;
            return $result;
        }
        $this->nested_transaction_counter = null;
        $result = MDB2_OK;
        if ($this->in_transaction) {
            if ($force_rollback || $this->has_transaction_error) {
                $result = $this->rollback();
                if (!PEAR::isError($result)) {
                    $result = false;
                }
            } else {
                $result = $this->commit();
            }
        }
        $this->has_transaction_error = false;
        return $result;
    }
    function failNestedTransaction($error = null, $immediately = false)
    {
        if (is_null($error)) {
            $error = $this->has_transaction_error ? $this->has_transaction_error : true;
        } elseif (!$error) {
            $error = true;
        }
        $this->has_transaction_error = $error;
        if (!$immediately) {
            return MDB2_OK;
        }
        return $this->rollback();
    }
    function getNestedTransactionError()
    {
        return $this->has_transaction_error;
    }
    function connect()
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function setCharset($charset, $connection = null)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function disconnect($force = true)
    {
        $this->connection = 0;
        $this->connected_dsn = array();
        $this->connected_database_name = '';
        $this->opened_persistent = null;
        $this->connected_server_info = '';
        $this->in_transaction = null;
        $this->nested_transaction_counter = null;
        return MDB2_OK;
    }
    function setDatabase($name)
    {
        $previous_database_name = (isset($this->database_name)) ? $this->database_name : '';
        $this->database_name = $name;
        $this->disconnect(false);
        return $previous_database_name;
    }
    function getDatabase()
    {
        return $this->database_name;
    }
    function setDSN($dsn)
    {
        $dsn_default = $GLOBALS['_MDB2_dsninfo_default'];
        $dsn = MDB2::parseDSN($dsn);
        if (array_key_exists('database', $dsn)) {
            $this->database_name = $dsn['database'];
            unset($dsn['database']);
        }
        $this->dsn = array_merge($dsn_default, $dsn);
        return $this->disconnect(false);
    }
    function getDSN($type = 'string', $hidepw = false)
    {
        $dsn = array_merge($GLOBALS['_MDB2_dsninfo_default'], $this->dsn);
        $dsn['phptype'] = $this->phptype;
        $dsn['database'] = $this->database_name;
        if ($hidepw) {
            $dsn['password'] = $hidepw;
        }
        switch ($type) {
        case 'string':
           $dsn = $dsn['phptype'].
               ($dsn['dbsyntax'] ? ('('.$dsn['dbsyntax'].')') : '').
               ':
                $dsn['password'].'@'.$dsn['hostspec'].
                ($dsn['port'] ? (':'.$dsn['port']) : '').
                '/'.$dsn['database'];
            break;
        case 'array':
        default:
            break;
        }
        return $dsn;
    }
    function &standaloneQuery($query, $types = null, $is_manip = false)
    {
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $result =& $this->_doQuery($query, $is_manip, $connection, false);
        if (PEAR::isError($result)) {
            return $result;
        }
        if ($is_manip) {
            $affected_rows =  $this->_affectedRows($connection, $result);
            return $affected_rows;
        }
        $result =& $this->_wrapResult($result, $types, true, false, $limit, $offset);
        return $result;
    }
    function _modifyQuery($query, $is_manip, $limit, $offset)
    {
        return $query;
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
        $err =& $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
        return $err;
    }
    function _affectedRows($connection, $result = null)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function &exec($query)
    {
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, true, $limit, $offset);
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $result =& $this->_doQuery($query, true, $connection, $this->database_name);
        if (PEAR::isError($result)) {
            return $result;
        }
        $affectedRows = $this->_affectedRows($connection, $result);
        return $affectedRows;
    }
    function &query($query, $types = null, $result_class = true, $result_wrap_class = false)
    {
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, false, $limit, $offset);
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $result =& $this->_doQuery($query, false, $connection, $this->database_name);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result =& $this->_wrapResult($result, $types, $result_class, $result_wrap_class, $limit, $offset);
        return $result;
    }
    function &_wrapResult($result, $types = array(), $result_class = true,
        $result_wrap_class = false, $limit = null, $offset = null)
    {
        if ($types === true) {
            if ($this->supports('result_introspection')) {
                $this->loadModule('Reverse', null, true);
                $tableInfo = $this->reverse->tableInfo($result);
                if (PEAR::isError($tableInfo)) {
                    return $tableInfo;
                }
                $types = array();
                foreach ($tableInfo as $field) {
                    $types[] = $field['mdb2type'];
                }
            } else {
                $types = null;
            }
        }
        if ($result_class === true) {
            $result_class = $this->options['result_buffering']
                ? $this->options['buffered_result_class'] : $this->options['result_class'];
        }
        if ($result_class) {
            $class_name = sprintf($result_class, $this->phptype);
            if (!MDB2::classExists($class_name)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'result class does not exist '.$class_name, __FUNCTION__);
                return $err;
            }
            $result =& new $class_name($this, $result, $limit, $offset);
            if (!MDB2::isResultCommon($result)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'result class is not extended from MDB2_Result_Common', __FUNCTION__);
                return $err;
            }
            if (!empty($types)) {
                $err = $result->setResultTypes($types);
                if (PEAR::isError($err)) {
                    $result->free();
                    return $err;
                }
            }
        }
        if ($result_wrap_class === true) {
            $result_wrap_class = $this->options['result_wrap_class'];
        }
        if ($result_wrap_class) {
            if (!MDB2::classExists($result_wrap_class)) {
                $err =& $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'result wrap class does not exist '.$result_wrap_class, __FUNCTION__);
                return $err;
            }
            $result =& new $result_wrap_class($result, $this->fetchmode);
        }
        return $result;
    }
    function getServerVersion($native = false)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function setLimit($limit, $offset = null)
    {
        if (!$this->supports('limit_queries')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'limit is not supported by this driver', __FUNCTION__);
        }
        $limit = (int)$limit;
        if ($limit < 0) {
            return $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                'it was not specified a valid selected range row limit', __FUNCTION__);
        }
        $this->limit = $limit;
        if (!is_null($offset)) {
            $offset = (int)$offset;
            if ($offset < 0) {
                return $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                    'it was not specified a valid first selected range row', __FUNCTION__);
            }
            $this->offset = $offset;
        }
        return MDB2_OK;
    }
    function subSelect($query, $type = false)
    {
        if ($this->supports('sub_selects') === true) {
            return $query;
        }
        if (!$this->supports('sub_selects')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'method not implemented', __FUNCTION__);
        }
        $col = $this->queryCol($query, $type);
        if (PEAR::isError($col)) {
            return $col;
        }
        if (!is_array($col) || count($col) == 0) {
            return 'NULL';
        }
        if ($type) {
            $this->loadModule('Datatype', null, true);
            return $this->datatype->implodeArray($col, $type);
        }
        return implode(', ', $col);
    }
    function replace($table, $fields)
    {
        if (!$this->supports('replace')) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'replace query is not supported', __FUNCTION__);
        }
        $count = count($fields);
        $condition = $values = array();
        for ($colnum = 0, reset($fields); $colnum < $count; next($fields), $colnum++) {
            $name = key($fields);
            if (isset($fields[$name]['null']) && $fields[$name]['null']) {
                $value = 'NULL';
            } else {
                $type = isset($fields[$name]['type']) ? $fields[$name]['type'] : null;
                $value = $this->quote($fields[$name]['value'], $type);
            }
            $values[$name] = $value;
            if (isset($fields[$name]['key']) && $fields[$name]['key']) {
                if ($value === 'NULL') {
                    return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                        'key value '.$name.' may not be NULL', __FUNCTION__);
                }
                $condition[] = $name . '=' . $value;
            }
        }
        if (empty($condition)) {
            return $this->raiseError(MDB2_ERROR_CANNOT_REPLACE, null, null,
                'not specified which fields are keys', __FUNCTION__);
        }
        $result = null;
        $in_transaction = $this->in_transaction;
        if (!$in_transaction && PEAR::isError($result = $this->beginTransaction())) {
            return $result;
        }
        $connection = $this->getConnection();
        if (PEAR::isError($connection)) {
            return $connection;
        }
        $condition = ' WHERE '.implode(' AND ', $condition);
        $query = "DELETE FROM $table$condition";
        $result =& $this->_doQuery($query, true, $connection);
        if (!PEAR::isError($result)) {
            $affected_rows = $this->_affectedRows($connection, $result);
            $insert = implode(', ', array_keys($values));
            $values = implode(', ', $values);
            $query = "INSERT INTO $table ($insert) VALUES ($values)";
            $result =& $this->_doQuery($query, true, $connection);
            if (!PEAR::isError($result)) {
                $affected_rows += $this->_affectedRows($connection, $result);;
            }
        }
        if (!$in_transaction) {
            if (PEAR::isError($result)) {
                $this->rollback();
            } else {
                $result = $this->commit();
            }
        }
        if (PEAR::isError($result)) {
            return $result;
        }
        return $affected_rows;
    }
    function &prepare($query, $types = null, $result_types = null, $lobs = array())
    {
        $is_manip = ($result_types === MDB2_PREPARE_MANIP);
        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
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
        $ignores = $this->sql_comments;
        $ignores[] = $this->string_quoting;
        $ignores[] = $this->identifier_quoting;
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
                    if (!empty($types) && is_array($types)) {
                        if ($placeholder_type == ':') {
                            if (is_int(key($types))) {
                                $types_tmp = $types;
                                $types = array();
                                $count = -1;
                            }
                        } else {
                            $types = array_values($types);
                        }
                    }
                }
                if ($placeholder_type == ':') {
                    $parameter = preg_replace('/^.{'.($position+1).'}([a-z0-9_]+).*$/si', '\\1', $query);
                    if ($parameter === '') {
                        $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                            'named parameter with an empty name', __FUNCTION__);
                        return $err;
                    }
                    $positions[$p_position] = $parameter;
                    $query = substr_replace($query, '?', $position, strlen($parameter)+1);
                    if (isset($count) && isset($types_tmp[++$count])) {
                        $types[$parameter] = $types_tmp[$count];
                    }
                } else {
                    $positions[$p_position] = count($positions);
                }
                $position = $p_position + 1;
            } else {
                $position = $p_position;
            }
        }
        $class_name = 'MDB2_Statement_'.$this->phptype;
        $statement = null;
        $obj =& new $class_name($this, $statement, $positions, $query, $types, $result_types, $is_manip, $limit, $offset);
        $this->debug($query, __FUNCTION__, array('is_manip' => $is_manip, 'when' => 'post', 'result' => $obj));
        return $obj;
    }
    function _skipDelimitedStrings($query, $position, $p_position)
    {
        $ignores = $this->sql_comments;
        $ignores[] = $this->string_quoting;
        $ignores[] = $this->identifier_quoting;
        foreach ($ignores as $ignore) {
            if (!empty($ignore['start'])) {
                if (is_int($start_quote = strpos($query, $ignore['start'], $position)) && $start_quote < $p_position) {
                    $end_quote = $start_quote;
                    do {
                        if (!is_int($end_quote = strpos($query, $ignore['end'], $end_quote + 1))) {
                            if ($ignore['end'] === "\n") {
                                $end_quote = strlen($query) - 1;
                            } else {
                                $err =& $this->raiseError(MDB2_ERROR_SYNTAX, null, null,
                                    'query with an unterminated text string specified', __FUNCTION__);
                                return $err;
                            }
                        }
                    } while ($ignore['escape'] && $query[($end_quote - 1)] == $ignore['escape']);
                    $position = $end_quote + 1;
                    return $position;
                }
            }
        }
        return $position;
    }
    function quote($value, $type = null, $quote = true, $escape_wildcards = false)
    {
        $result = $this->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->quote($value, $type, $quote, $escape_wildcards);
    }
    function getDeclaration($type, $name, $field)
    {
        $result = $this->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->getDeclaration($type, $name, $field);
    }
    function compareDefinition($current, $previous)
    {
        $result = $this->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        return $this->datatype->compareDefinition($current, $previous);
    }
    function supports($feature)
    {
        if (array_key_exists($feature, $this->supported)) {
            return $this->supported[$feature];
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown support feature $feature", __FUNCTION__);
    }
    function getSequenceName($sqn)
    {
        return sprintf($this->options['seqname_format'],
            preg_replace('/[^a-z0-9_\$.]/i', '_', $sqn));
    }
    function getIndexName($idx)
    {
        return sprintf($this->options['idxname_format'],
            preg_replace('/[^a-z0-9_\$]/i', '_', $idx));
    }
    function nextID($seq_name, $ondemand = true)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function lastInsertID($table = null, $field = null)
    {
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function currID($seq_name)
    {
        $this->warnings[] = 'database does not support getting current
            sequence value, the sequence value was incremented';
        return $this->nextID($seq_name);
    }
    function queryOne($query, $type = null, $colnum = 0)
    {
        $result = $this->query($query, $type);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $one = $result->fetchOne($colnum);
        $result->free();
        return $one;
    }
    function queryRow($query, $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $result = $this->query($query, $types);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $row = $result->fetchRow($fetchmode);
        $result->free();
        return $row;
    }
    function queryCol($query, $type = null, $colnum = 0)
    {
        $result = $this->query($query, $type);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $col = $result->fetchCol($colnum);
        $result->free();
        return $col;
    }
    function queryAll($query, $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT,
        $rekey = false, $force_array = false, $group = false)
    {
        $result = $this->query($query, $types);
        if (!MDB2::isResultCommon($result)) {
            return $result;
        }
        $all = $result->fetchAll($fetchmode, $rekey, $force_array, $group);
        $result->free();
        return $all;
    }
}
class MDB2_Result
{
}
class MDB2_Result_Common extends MDB2_Result
{
    var $db;
    var $result;
    var $rownum = -1;
    var $types = array();
    var $values = array();
    var $offset;
    var $offset_count = 0;
    var $limit;
    var $column_names;
    function __construct(&$db, &$result, $limit = 0, $offset = 0)
    {
        $this->db =& $db;
        $this->result =& $result;
        $this->offset = $offset;
        $this->limit = max(0, $limit - 1);
    }
    function MDB2_Result_Common(&$db, &$result, $limit = 0, $offset = 0)
    {
        $this->__construct($db, $result, $limit, $offset);
    }
    function setResultTypes($types)
    {
        $load = $this->db->loadModule('Datatype', null, true);
        if (PEAR::isError($load)) {
            return $load;
        }
        $types = $this->db->datatype->checkResultTypes($types);
        if (PEAR::isError($types)) {
            return $types;
        }
        $this->types = $types;
        return MDB2_OK;
    }
    function seek($rownum = 0)
    {
        $target_rownum = $rownum - 1;
        if ($this->rownum > $target_rownum) {
            return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'seeking to previous rows not implemented', __FUNCTION__);
        }
        while ($this->rownum < $target_rownum) {
            $this->fetchRow();
        }
        return MDB2_OK;
    }
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        $err =& $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
        return $err;
    }
    function fetchOne($colnum = 0, $rownum = null)
    {
        $fetchmode = is_numeric($colnum) ? MDB2_FETCHMODE_ORDERED : MDB2_FETCHMODE_ASSOC;
        $row = $this->fetchRow($fetchmode, $rownum);
        if (!is_array($row) || PEAR::isError($row)) {
            return $row;
        }
        if (!array_key_exists($colnum, $row)) {
            return $this->db->raiseError(MDB2_ERROR_TRUNCATED, null, null,
                'column is not defined in the result set: '.$colnum, __FUNCTION__);
        }
        return $row[$colnum];
    }
    function fetchCol($colnum = 0)
    {
        $column = array();
        $fetchmode = is_numeric($colnum) ? MDB2_FETCHMODE_ORDERED : MDB2_FETCHMODE_ASSOC;
        $row = $this->fetchRow($fetchmode);
        if (is_array($row)) {
            if (!array_key_exists($colnum, $row)) {
                return $this->db->raiseError(MDB2_ERROR_TRUNCATED, null, null,
                    'column is not defined in the result set: '.$colnum, __FUNCTION__);
            }
            do {
                $column[] = $row[$colnum];
            } while (is_array($row = $this->fetchRow($fetchmode)));
        }
        if (PEAR::isError($row)) {
            return $row;
        }
        return $column;
    }
    function fetchAll($fetchmode = MDB2_FETCHMODE_DEFAULT, $rekey = false,
        $force_array = false, $group = false)
    {
        $all = array();
        $row = $this->fetchRow($fetchmode);
        if (PEAR::isError($row)) {
            return $row;
        } elseif (!$row) {
            return $all;
        }
        $shift_array = $rekey ? false : null;
        if (!is_null($shift_array)) {
            if (is_object($row)) {
                $colnum = count(get_object_vars($row));
            } else {
                $colnum = count($row);
            }
            if ($colnum < 2) {
                return $this->db->raiseError(MDB2_ERROR_TRUNCATED, null, null,
                    'rekey feature requires atleast 2 column', __FUNCTION__);
            }
            $shift_array = (!$force_array && $colnum == 2);
        }
        if ($rekey) {
            do {
                if (is_object($row)) {
                    $arr = get_object_vars($row);
                    $key = reset($arr);
                    unset($row->{$key});
                } else {
                    if ($fetchmode & MDB2_FETCHMODE_ASSOC) {
                        $key = reset($row);
                        unset($row[key($row)]);
                    } else {
                        $key = array_shift($row);
                    }
                    if ($shift_array) {
                        $row = array_shift($row);
                    }
                }
                if ($group) {
                    $all[$key][] = $row;
                } else {
                    $all[$key] = $row;
                }
            } while (($row = $this->fetchRow($fetchmode)));
        } elseif ($fetchmode & MDB2_FETCHMODE_FLIPPED) {
            do {
                foreach ($row as $key => $val) {
                    $all[$key][] = $val;
                }
            } while (($row = $this->fetchRow($fetchmode)));
        } else {
            do {
                $all[] = $row;
            } while (($row = $this->fetchRow($fetchmode)));
        }
        return $all;
    }
    function rowCount()
    {
        return $this->rownum + 1;
    }
    function numRows()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function nextResult()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function getColumnNames($flip = false)
    {
        if (!isset($this->column_names)) {
            $result = $this->_getColumnNames();
            if (PEAR::isError($result)) {
                return $result;
            }
            $this->column_names = $result;
        }
        if ($flip) {
            return array_flip($this->column_names);
        }
        return $this->column_names;
    }
    function _getColumnNames()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function numCols()
    {
        return $this->db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
    }
    function getResource()
    {
        return $this->result;
    }
    function bindColumn($column, &$value, $type = null)
    {
        if (!is_numeric($column)) {
            $column_names = $this->getColumnNames();
            if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($this->db->options['field_case'] == CASE_LOWER) {
                    $column = strtolower($column);
                } else {
                    $column = strtoupper($column);
                }
            }
            $column = $column_names[$column];
        }
        $this->values[$column] =& $value;
        if (!is_null($type)) {
            $this->types[$column] = $type;
        }
        return MDB2_OK;
    }
    function _assignBindColumns($row)
    {
        $row = array_values($row);
        foreach ($row as $column => $value) {
            if (array_key_exists($column, $this->values)) {
                $this->values[$column] = $value;
            }
        }
        return MDB2_OK;
    }
    function free()
    {
        $this->result = false;
        return MDB2_OK;
    }
}
class MDB2_Row
{
    function __construct(&$row)
    {
        foreach ($row as $key => $value) {
            $this->$key = &$row[$key];
        }
    }
    function MDB2_Row(&$row)
    {
        $this->__construct($row);
    }
}
class MDB2_Statement_Common
{
    var $db;
    var $statement;
    var $query;
    var $result_types;
    var $types;
    var $values = array();
    var $limit;
    var $offset;
    var $is_manip;
    function __construct(&$db, &$statement, $positions, $query, $types, $result_types, $is_manip = false, $limit = null, $offset = null)
    {
        $this->db =& $db;
        $this->statement =& $statement;
        $this->positions = $positions;
        $this->query = $query;
        $this->types = (array)$types;
        $this->result_types = (array)$result_types;
        $this->limit = $limit;
        $this->is_manip = $is_manip;
        $this->offset = $offset;
    }
    function MDB2_Statement_Common(&$db, &$statement, $positions, $query, $types, $result_types, $is_manip = false, $limit = null, $offset = null)
    {
        $this->__construct($db, $statement, $positions, $query, $types, $result_types, $is_manip, $limit, $offset);
    }
    function bindValue($parameter, $value, $type = null)
    {
        if (!is_numeric($parameter)) {
            $parameter = preg_replace('/^:(.*)$/', '\\1', $parameter);
        }
        if (!in_array($parameter, $this->positions)) {
            return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
        }
        $this->values[$parameter] = $value;
        if (!is_null($type)) {
            $this->types[$parameter] = $type;
        }
        return MDB2_OK;
    }
    function bindValueArray($values, $types = null)
    {
        $types = is_array($types) ? array_values($types) : array_fill(0, count($values), null);
        $parameters = array_keys($values);
        foreach ($parameters as $key => $parameter) {
            $err = $this->bindValue($parameter, $values[$parameter], $types[$key]);
            if (PEAR::isError($err)) {
                return $err;
            }
        }
        return MDB2_OK;
    }
    function bindParam($parameter, &$value, $type = null)
    {
        if (!is_numeric($parameter)) {
            $parameter = preg_replace('/^:(.*)$/', '\\1', $parameter);
        }
        if (!in_array($parameter, $this->positions)) {
            return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
        }
        $this->values[$parameter] =& $value;
        if (!is_null($type)) {
            $this->types[$parameter] = $type;
        }
        return MDB2_OK;
    }
    function bindParamArray(&$values, $types = null)
    {
        $types = is_array($types) ? array_values($types) : array_fill(0, count($values), null);
        $parameters = array_keys($values);
        foreach ($parameters as $key => $parameter) {
            $err = $this->bindParam($parameter, $values[$parameter], $types[$key]);
            if (PEAR::isError($err)) {
                return $err;
            }
        }
        return MDB2_OK;
    }
    function &execute($values = null, $result_class = true, $result_wrap_class = false)
    {
        if (is_null($this->positions)) {
            return $this->db->raiseError(MDB2_ERROR, null, null,
                'Prepared statement has already been freed', __FUNCTION__);
        }
        $values = (array)$values;
        if (!empty($values)) {
            $err = $this->bindValueArray($values);
            if (PEAR::isError($err)) {
                return $this->db->raiseError(MDB2_ERROR, null, null,
                                            'Binding Values failed with message: ' . $err->getMessage(), __FUNCTION__);
            }
        }
        $result =& $this->_execute($result_class, $result_wrap_class);
        return $result;
    }
    function &_execute($result_class = true, $result_wrap_class = false)
    {
        $this->last_query = $this->query;
        $query = '';
        $last_position = 0;
        foreach ($this->positions as $current_position => $parameter) {
            if (!array_key_exists($parameter, $this->values)) {
                return $this->db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                    'Unable to bind to missing placeholder: '.$parameter, __FUNCTION__);
            }
            $value = $this->values[$parameter];
            $query.= substr($this->query, $last_position, $current_position - $last_position);
            if (!isset($value)) {
                $value_quoted = 'NULL';
            } else {
                $type = !empty($this->types[$parameter]) ? $this->types[$parameter] : null;
                $value_quoted = $this->db->quote($value, $type);
                if (PEAR::isError($value_quoted)) {
                    return $value_quoted;
                }
            }
            $query.= $value_quoted;
            $last_position = $current_position + 1;
        }
        $query.= substr($this->query, $last_position);
        $this->db->offset = $this->offset;
        $this->db->limit = $this->limit;
        if ($this->is_manip) {
            $result = $this->db->exec($query);
        } else {
            $result =& $this->db->query($query, $this->result_types, $result_class, $result_wrap_class);
        }
        return $result;
    }
    function free()
    {
        if (is_null($this->positions)) {
            return $this->db->raiseError(MDB2_ERROR, null, null,
                'Prepared statement has already been freed', __FUNCTION__);
        }
        $this->statement = null;
        $this->positions = null;
        $this->query = null;
        $this->types = null;
        $this->result_types = null;
        $this->limit = null;
        $this->is_manip = null;
        $this->offset = null;
        $this->values = null;
        return MDB2_OK;
    }
}
class MDB2_Module_Common
{
    var $db_index;
    function __construct($db_index)
    {
        $this->db_index = $db_index;
    }
    function MDB2_Module_Common($db_index)
    {
        $this->__construct($db_index);
    }
    function &getDBInstance()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $result =& $GLOBALS['_MDB2_databases'][$this->db_index];
        } else {
            $result =& MDB2::raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'could not find MDB2 instance');
        }
        return $result;
    }
}
function MDB2_closeOpenTransactions()
{
    reset($GLOBALS['_MDB2_databases']);
    while (next($GLOBALS['_MDB2_databases'])) {
        $key = key($GLOBALS['_MDB2_databases']);
        if ($GLOBALS['_MDB2_databases'][$key]->opened_persistent
            && $GLOBALS['_MDB2_databases'][$key]->in_transaction
        ) {
            $GLOBALS['_MDB2_databases'][$key]->rollback();
        }
    }
}
function MDB2_defaultDebugOutput(&$db, $scope, $message, $context = array())
{
    $db->debug_output.= $scope.'('.$db->db_index.'): ';
    $db->debug_output.= $message.$db->getOption('log_line_break');
    return $message;
}
?>
