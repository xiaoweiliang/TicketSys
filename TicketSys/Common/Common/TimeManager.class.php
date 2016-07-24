<?php
/**
 * 时间管理器
 * @author rxw
 */
 namespace Common\Common;
class TimeManager
{
    /**
     * 获取时间戳
     */
    public static function GetTime()
    {
        return time();
    }
    /**
     * 获取精确时间
     */
    public static function GetMirTime()
    {
        $time=microtime(true);
        $time=implode("", explode(".", $time));
        return $time;
    }
    /**
     * 时间格式化
     */
    public static function FormatTime($time)
    {
        if(!empty($time))
            return date("Y-m-d H:i:s",$time);
        else 
            return "1970-07-07 07:07:07";
    }
    /**
     * 时间格式化
     */
    public static function FormatTime1($time)
    {
    	if(!empty($time))
    		return date("Y-m-d",$time);
    	else
    		return "1970-07-07";
    }
}
?>