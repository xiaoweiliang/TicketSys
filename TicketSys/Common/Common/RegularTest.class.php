<?php
/**
 * 该模块用来向正则验证接受的数据
 * Author 任雄伟
 * 时间 ：2016/6/22
 */
 namespace Common\Common;
class RegularTest
{
    //验证手机号码
    public static function check_mobilephone($phone)
    {
        if(empty($phone))
            return false;
        if(preg_match("/1[3458]{1}\d{9}$/",$phone))
            return true;
        else
            return false;
    }
    //验证座机号码
    public static function check_telephone($phone)
    {
        ;
    }
    //正则匹配身份证
    public static function check_idnum($idnum)         
    {
        if(preg_match("/^(?:\d{15}|\d{18})$/",$idnum))
            return true;
        else
            return false;
    }
}

?>