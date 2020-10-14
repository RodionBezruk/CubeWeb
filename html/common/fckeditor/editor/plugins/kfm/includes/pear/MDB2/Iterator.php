<?php
class MDB2_Iterator implements Iterator
{
    protected $fetchmode;
    protected $result;
    protected $row;
    public function __construct($result, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $this->result = $result;
        $this->fetchmode = $fetchmode;
    }
    public function seek($rownum)
    {
        $this->row = null;
        if ($this->result) {
            $this->result->seek($rownum);
        }
    }
    public function next()
    {
        $this->row = null;
    }
    public function current()
    {
        if (is_null($this->row)) {
            $row = $this->result->fetchRow($this->fetchmode);
            if (PEAR::isError($row)) {
                $row = false;
            }
            $this->row = $row;
        }
        return $this->row;
    }
    public function valid()
    {
        return (bool)$this->current();
    }
    public function free()
    {
        if ($this->result) {
            return $this->result->free();
        }
        $this->result = false;
        $this->row = null;
        return false;
    }
    public function key()
    {
        if ($this->result) {
            return $this->result->rowCount();
        }
        return false;
    }
    public function rewind()
    {
    }
    public function __destruct()
    {
        $this->free();
    }
}
class MDB2_BufferedIterator extends MDB2_Iterator implements SeekableIterator
{
    public function valid()
    {
        if ($this->result) {
            return $this->result->valid();
        }
        return false;
    }
    public function count()
    {
        if ($this->result) {
            return $this->result->numRows();
        }
        return false;
    }
    public function rewind()
    {
        $this->seek(0);
    }
}
?>
