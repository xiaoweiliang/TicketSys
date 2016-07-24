<?php
/**
 * cookie操作类
 */
namespace Common\Common;
class Cookie
{
    /**
     * 设置cookie
     */
    public static function SetDataCookie($tb_name,$value)
    {
        if(!empty($tb_name))
        {
            if(is_numeric($value)&&$value>0)
            {
                $id='';
                if(!empty($_REQUEST['cityid'])&&is_numeric($_REQUEST['cityid']))
                    $id=$_REQUEST['cityid'];
                    cookie($tb_name.COOKIE_PREFIX.$id,$value);
            }       
        }
    }
    /**
     * 获取cookie
     */
    public static function GetDataCookie($tb_name)
    {
         if(!empty($tb_name))
         {
             $id='';
             if(!empty($_REQUEST['cityid'])&&is_numeric($_REQUEST['cityid']))
                 $id=$_REQUEST['cityid'];
            $value=cookie($tb_name.COOKIE_PREFIX.$id);
            if(!empty($value))
            {
                return $value;
            }
            else
                return 0;
         }
         else 
             return 0;
    }
    /**
     * 清空cookie  保存的是线路的或者站点的数量
     */
    public static function ClearDataCookie($tb_name)
    {
        if(!empty($tb_name))
        {
            $id='';
            if(!empty($_REQUEST['cityid'])&&is_numeric($_REQUEST['cityid']))
                $id=$_REQUEST['cityid'];
             cookie($tb_name.COOKIE_PREFIX.$id,0);
        }    
    }
    /**
     * 设置普通kookie
     */
    public static function SetCookie($key,$value)
    {
        if(!empty($key)&&isset($value))
            cookie($key.SESSION_PREFIX,$value);
    }
    /**
     * 获取普通cookie
     */
    public static function GetCookie($key)
    {
        if(!empty($key))
        {
            $value=cookie($key.SESSION_PREFIX);
            if(!empty($value))
            {
                return $value;
            }
            else
                return '';
        }
    }
    /**
     * 清楚普通cookie
     */
    public static function ClearCookie($key)
    {
        if(!empty($key))
        {
            cookie($key.SESSION_PREFIX,-1);
        }
    }
    /**
     * 获取传输的type
     */
    public static function GetTFType()
    {
        $type=@$_GET['type'];
        if(!isset($type))
            $type=@$_POST['type'];
        if($type==-1)
            $type=null;
        return $type;
    }
    /**
     * 获取传输的userid
     */
    public static function GetTHUserId()
    {
        $userid=@$_GET['userid'];
        if(!isset($userid))
            $userid=@$_POST['userid'];
        return $userid;
    }
}