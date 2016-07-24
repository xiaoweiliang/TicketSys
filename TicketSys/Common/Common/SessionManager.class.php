<?php
/**
 * session数据处理以及管理
 */
 namespace Common\Common;
 use Admin\Controller\UserController;
  class SessionManager
 {
     /**
      * 获取sessionid
      */
     public static function GetSessionId()
     {
         return session_id();
     }
     /**
      * 设置sessionid
      */
     public static function SetSessionId($session_id)
     {
         if(!empty($session_id))
         {
             session_id($session_id);
             return true;
         }   
         else
             return false;
     }
     /**
      * 用户登录更新状态
      * type 1管理员   2 普通用户
      */
     public static function UpdateUserLogin($user_id,$username=null)
     {
         if(!empty($user_id))
         {
             session(SESSION_PREFIX."_id",$user_id);
             if(!empty($username))
                 session(SESSION_PREFIX."_name",$username);
         }
         else
         {
             $home=new UserController();
             $home->loginout();
         }
     }
     /**
      * 更新sessionid
      */
     public static function UpdateSession()
     {
     	$data=session();
     	session_regenerate_id();                       //更新sessionid
     	$session_id=session_id();
     	return $session_id;
     }
      
     /**
      * 检测管理员是否在线
      */
     public static function GetUserId($type=0)
     {
         $id=session(SESSION_PREFIX."_id");
         if(!empty($id)&&$id>0)
             return $id;
         else 
         {
             if(SESSION_PREFIX=="admin"&&$type==0)
             {
                 $home=new UserController();
                 $home->loginout();
                 exit();
             }
             return -1;
         }   
     }
     /**
      * 获取用户名
      */
     public static function GetAdminName()
     {
         $name=session(SESSION_PREFIX."_name");;
         if(!empty($name))
             return $name;
         else 
         {
             if(SESSION_PREFIX=="admin")
             {
                 $home=new UserController();
                 $home->loginout();
                 exit();
             }
             return null;
         }   
     }
     /**
      * 清空session
      */
     public static function GoBackSession()
     {
         session_destroy();
     }
 }
?>