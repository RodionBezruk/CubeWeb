<?php
if (!defined('XOOPS_ROOT_PATH')) exit();
include_once XOOPS_ROOT_PATH."/class/database/database.php";
class XoopsMySQLDatabase extends XoopsDatabase
{
	var $conn;
	var $mPrepareQuery=null;
	function connect($selectdb = true)
	{
		if (XOOPS_DB_PCONNECT == 1) {
			$this->conn = @mysql_pconnect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS);
		} else {
			$this->conn = @mysql_connect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS);
		}
		if (!$this->conn) {
			$this->logger->addQuery('', $this->error(), $this->errno());
			return false;
		}
		if($selectdb != false){
			if (!mysql_select_db(XOOPS_DB_NAME)) {
				$this->logger->addQuery('', $this->error(), $this->errno());
				return false;
			}
		}
		return true;
	}
	function genId($sequence)
	{
		return 0; 
	}
	function fetchRow($result)
	{
		return @mysql_fetch_row($result);
	}
	function fetchArray($result)
    {
        return @mysql_fetch_assoc( $result );
    }
    function fetchBoth($result)
    {
        return @mysql_fetch_array( $result, MYSQL_BOTH );
    }
	function getInsertId()
	{
		return mysql_insert_id($this->conn);
	}
	function getRowsNum($result)
	{
		return @mysql_num_rows($result);
	}
	function getAffectedRows()
	{
		return mysql_affected_rows($this->conn);
	}
	function close()
	{
		mysql_close($this->conn);
	}
	function freeRecordSet($result)
	{
		return mysql_free_result($result);
	}
	function error()
	{
		return @mysql_error();
	}
	function errno()
	{
		return @mysql_errno();
	}
    function quoteString($str)
    {
         $str = "'".mysql_real_escape_string($str)."'";
         return $str;
    }
    function &queryF($sql, $limit=0, $start=0)
	{
		if ( !empty($limit) ) {
			if (empty($start)) {
                $sql .= ' LIMIT ' . intval($limit);
			}
            else
            {
                $sql = $sql. ' LIMIT '.(int)$start.', '.(int)$limit;
            }
		}
		$result = mysql_query($sql, $this->conn);
		if ( $result ) {
			$this->logger->addQuery($sql);
			return $result;
        } else {
			$this->logger->addQuery($sql, $this->error(), $this->errno());
            $ret = false;
            return $ret;
        }
    }
	function &query($sql, $limit=0, $start=0)
	{
    }
	function queryFromFile($file){
        if (false !== ($fp = fopen($file, 'r'))) {
			include_once XOOPS_ROOT_PATH.'/class/database/sqlutility.php';
            $sql_queries = trim(fread($fp, filesize($file)));
            SqlUtility::splitMySqlFile($pieces, $sql_queries);
            foreach ($pieces as $query) {
                $prefixed_query = SqlUtility::prefixQuery(trim($query), $this->prefix());
                if ($prefixed_query != false) {
                    $this->query($prefixed_query[0]);
                }
            }
            return true;
        }
        return false;
    }
	function getFieldName($result, $offset)
	{
		return mysql_field_name($result, $offset);
	}
    function getFieldType($result, $offset)
	{
		return mysql_field_type($result, $offset);
	}
	function getFieldsNum($result)
	{
		return mysql_num_fields($result);
	}
	function prepare($query)
	{
		$count=0;
		while(($pos=strpos($query,"?"))!==false) {
			$pre=substr($query,0,$pos);
			$after="";
			if($pos+1<=strlen($query))
				$after=substr($query,$pos+1);
			$query=$pre."{".$count."}".$after;
			$count++;
		}
		$this->mPrepareQuery=$query;
	}
	function bind_param()
	{
		if(func_num_args()<2)
			return;
		$types=func_get_arg(0);
		$count=strlen($types);
		if(func_num_args()<$count)
			return;
		$searches=array();
		$replaces=array();
		for($i=0;$i<$count;$i++) {
			$searches[$i]="{".$i."}";
			switch(substr($types,$i,1)) {
				case "i":
					$replaces[$i]=intval(func_get_arg($i+1));
					break;
				case "s":
					$replaces[$i]=$this->quoteString(func_get_arg($i+1));
					break;
				case "d":
					$replaces[$i]=doubleval(func_get_arg($i+1));
					break;
				case "b":
					die();
			}
		}
		$this->mPrepareQuery=str_replace($searches,$replaces,$this->mPrepareQuery);
	}
	function &execute()
	{
		$result=&$this->query($this->mPrepareQuery);
		$this->mPrepareQuery=null;
		return $result;
	}
	function &executeF()
	{
		$result=&$this->queryF($this->mPrepareQuery);
		$this->mPrepareQuery=null;
		return $result;
	}
}
class XoopsMySQLDatabaseSafe extends XoopsMySQLDatabase
{
	function &query($sql, $limit=0, $start=0)
	{
		$result =& $this->queryF($sql, $limit, $start);
		return $result;
	}
}
class XoopsMySQLDatabaseProxy extends XoopsMySQLDatabase
{
	function &query($sql, $limit=0, $start=0)
	{
	    $sql = ltrim($sql);
		if (strtolower(substr($sql, 0, 6)) == 'select') {
			$ret = $this->queryF($sql, $limit, $start);
			return $ret;
		}
		$this->logger->addQuery($sql, 'Database update not allowed during processing of a GET request', 0);
		$ret = false;
		return $ret;
	}
}
?>
