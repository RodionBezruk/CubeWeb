<?php
class MDB2_Date
{
    function mdbNow()
    {
        return date('Y-m-d H:i:s');
    }
    function mdbToday()
    {
        return date('Y-m-d');
    }
    function mdbTime()
    {
        return date('H:i:s');
    }
    function date2Mdbstamp($hour = null, $minute = null, $second = null,
        $month = null, $day = null, $year = null)
    {
        return MDB2_Date::unix2Mdbstamp(mktime($hour, $minute, $second, $month, $day, $year, -1));
    }
    function unix2Mdbstamp($unix_timestamp)
    {
        return date('Y-m-d H:i:s', $unix_timestamp);
    }
    function mdbstamp2Unix($mdb_timestamp)
    {
        $arr = MDB2_Date::mdbstamp2Date($mdb_timestamp);
        return mktime($arr['hour'], $arr['minute'], $arr['second'], $arr['month'], $arr['day'], $arr['year'], -1);
    }
    function mdbstamp2Date($mdb_timestamp)
    {
        list($arr['year'], $arr['month'], $arr['day'], $arr['hour'], $arr['minute'], $arr['second']) =
            sscanf($mdb_timestamp, "%04u-%02u-%02u %02u:%02u:%02u");
        return $arr;
    }
}
?>
