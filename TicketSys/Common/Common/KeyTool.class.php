<?php
/*
 * 密码以及编码的工具类
 * Author 任雄伟
 * 时间 2016/6/22
 */
namespace  Common\Common;
use Think\Crypt\Driver\Base64;
class KeyTool extends Base64
{
    /**
     * md5加密
     */
    public static function MD5($value)
    {
        return md5($value);
    }
    //密码加密
    public static function Smd5($key)
    {
        if(!empty($key))
        {
            return KeyTool::MD5(sha1($key));
        }
        else
            return false;
    }
    //用户id编码
    public static function base_encodes($user_id)
    {
        if(isset($user_id)&&is_numeric($user_id))
        {
            $num=$user_id%4+2;
            for($i=0;$i<$num;$i++)
            {
                $user_id=KeyTool::base_encode($user_id);
            }
            return $user_id;
        }
        else 
            return false;
    }
    
    //解码用户id
    public static function base_decodes($user_id)
    {
        if(!empty($user_id))
        {
            while(!is_numeric($user_id))
            {
                $user_id=KeyTool::base_decode($user_id);
            }
            if(is_numeric($user_id))
                return $user_id;
            else
                return -1;
        }
        else
            return -1;
    }
    /**
     * 用户未登录前对密码进行加密
     */
    public static function _before_base_decode($phone_no,$key)
    {
    	$phone_no=substr($phone_no, 0,10);
        $num=$phone_no%4+2;
        while($num>0)
        {
            $key=KeyTool::base_decode($key);
            $num--;
        }
        if(!empty($key))
            return $key;
        else
            return '';
    }
    /**
     * 管理员添加用户的时候对用户的密码进行加密
     */
    public static function _before_base_encodes($phone_no,$key)
    {
        if(!empty($phone_no)&&!empty($key))
        {
            $num=$phone_no%4+2;
            while($num>0)
            {
                $key=   KeyTool::base_encode($key);
                $num--;
            }
            return $key;
        }
        return '';
    }
    //对数据编码
    public static function base_encode($data)
    {
        if(!empty($data))
            return base64_encode($data);
        else
            return '';
    }
    
    //对数据进行解码
    public static function base_decode($data)
    {
        if(!empty($data))
            return base64_decode($data);
        else 
            return '';
    }
}

?>