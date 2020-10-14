<?php
class XoopsLogger
{
    var $queries = array();
    var $blocks = array();
    var $extra = array();
    var $logstart = array();
    var $logend = array();
    function XoopsLogger()
    {
    }
    function &instance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new XoopsLogger();
        }
        return $instance;
    }
    function startTime($name = 'XOOPS')
    {
        $this->logstart[$name] = explode(' ', microtime());
    }
    function stopTime($name = 'XOOPS')
    {
        $this->logend[$name] = explode(' ', microtime());
    }
    function addQuery($sql, $error=null, $errno=null)
    {
        $this->queries[] = array('sql' => $sql, 'error' => $error, 'errno' => $errno);
    }
    function addBlock($name, $cached = false, $cachetime = 0)
    {
        $this->blocks[] = array('name' => $name, 'cached' => $cached, 'cachetime' => $cachetime);
    }
    function addExtra($name, $msg)
    {
        $this->extra[] = array('name' => $name, 'msg' => $msg);
    }
    function dumpQueries()
    {
        $ret = '<table class="outer" width="100%" cellspacing="1"><tr><th>Queries</th></tr>';
        $class = 'even';
        foreach ($this->queries as $q) {
            if (isset($q['error'])) {
                $ret .= '<tr class="'.$class.'"><td><span style="color:#ff0000;">'.htmlentities($q['sql']).'<br /><b>Error number:</b> '.$q['errno'].'<br /><b>Error message:</b> '.$q['error'].'</span></td></tr>';
            } else {
                $ret .= '<tr class="'.$class.'"><td>'.htmlentities($q['sql']).'</td></tr>';
            }
            $class = ($class == 'odd') ? 'even' : 'odd';
        }
        $ret .= '<tr class="foot"><td>Total: <span style="color:#ff0000;">'.count($this->queries).'</span> queries</td></tr></table><br />';
        return $ret;
    }
    function dumpBlocks()
    {
        $ret = '<table class="outer" width="100%" cellspacing="1"><tr><th colspan="2">Blocks</th></tr>';
        $class = 'even';
        foreach ($this->blocks as $b) {
            if ($b['cached']) {
                $ret .= '<tr><td class="'.$class.'"><b>'.htmlspecialchars($b['name']).':</b> Cached (regenerates every '.intval($b['cachetime']).' seconds)</td></tr>';
            } else {
                $ret .= '<tr><td class="'.$class.'"><b>'.htmlspecialchars($b['name']).':</b> No Cache</td></tr>';
            }
            $class = ($class == 'odd') ? 'even' : 'odd';
        }
        $ret .= '<tr class="foot"><td>Total: <span style="color:#ff0000;">'.count($this->blocks).'</span> blocks</td></tr></table><br />';
        return $ret;
    }
    function dumpTime($name = 'XOOPS')
    {
        if (!isset($this->logstart[$name])) {
            return 0;
        }
        if (!isset($this->logend[$name])) {
            $stop_time = explode(' ', microtime());
        } else {
            $stop_time = $this->logend[$name];
        }
        return ((float)$stop_time[1] + (float)$stop_time[0]) - ((float)$this->logstart[$name][1] + (float)$this->logstart[$name][0]);
    }
    function dumpExtra()
    {
        $ret = '<table class="outer" width="100%" cellspacing="1"><tr><th colspan="2">Extra</th></tr>';
        $class = 'even';
        foreach ($this->extra as $ex) {
            $ret .= '<tr><td class="'.$class.'"><b>'.htmlspecialchars($ex['name']).':</b> '.htmlspecialchars($ex['msg']).'</td></tr>';
            $class = ($class == 'odd') ? 'even' : 'odd';
        }
        $ret .= '</table><br />';
        return $ret;
    }
    function dumpAll()
    {
        $ret = $this->dumpQueries();
        $ret .= $this->dumpBlocks();
        if (count($this->logstart) > 0) {
            $ret .= '<table class="outer" width="100%" cellspacing="1"><tr><th>Execution Time</th></tr>';
            $class = 'even';
            foreach ($this->logstart as $k => $v) {
                $ret .= '<tr><td class="'.$class.'"><b>'.htmlspecialchars($k).'</b> took <span style="color:#ff0000;">'.$this->dumpTime($k).'</span> seconds to load.</td></tr>';
                $class = ($class == 'odd') ? 'even' : 'odd';
            }
            $ret .= '</table><br />';
        }
        $ret .= $this->dumpExtra();
        return $ret;
    }
}
?>
