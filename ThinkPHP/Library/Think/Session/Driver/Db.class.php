<?php 
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Session\Driver;
use  Common\Conf\Conf;
/**
 * CREATE TABLE `user_session` (
  `session_key` varchar(55) NOT NULL DEFAULT '',
  `session_data` varchar(400) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `use_type` int(11) DEFAULT NULL,
  `session_time` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`session_key`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
 * @author rxw
 *
 */
class Db {

    /**
     * Session有效时间
     */
   protected $lifeTime      = ''; 

    /**
     * session保存的数据库名
     */
   protected $sessionTable  = '';

    /**
     * 数据库句柄
     */
   protected $hander  = array(); 

    /**
     * 打开Session 
     * @access public 
     * @param string $savePath 
     * @param mixed $sessName  
     */
    public function open($savePath, $sessName) { 
       $this->lifeTime 		= 	C('SESSION_EXPIRE')?C('SESSION_EXPIRE'):ini_get('session.gc_maxlifetime');
       $this->sessionTable  =   "user_session";
       //分布式数据库
       $host = explode(',',C('DB_HOST'));
       $port = explode(',',C('DB_PORT'));
       $name = explode(',',C('DB_NAME'));
       $user = explode(',',C('DB_USER'));
       $pwd  = explode(',',C('DB_PWD'));
       if(1 == C('DB_DEPLOY_TYPE')){
           //读写分离
           if(C('DB_RW_SEPARATE')){
               $w = floor(mt_rand(0,C('DB_MASTER_NUM')-1));
               if(is_numeric(C('DB_SLAVE_NO'))){//指定服务器读
                   $r = C('DB_SLAVE_NO');
               }else{
                   $r = floor(mt_rand(C('DB_MASTER_NUM'),count($host)-1));
               }
               //主数据库链接
               $hander = mysql_connect(
                   $host[$w].(isset($port[$w])?':'.$port[$w]:':'.$port[0]),
                   isset($user[$w])?$user[$w]:$user[0],
                   isset($pwd[$w])?$pwd[$w]:$pwd[0]
                   );
               $dbSel = mysql_select_db(
                   isset($name[$w])?$name[$w]:$name[0]
                   ,$hander);
               if(!$hander || !$dbSel)
                   return false;
               $this->hander[0] = $hander;
               //从数据库链接
               $hander = mysql_connect(
                   $host[$r].(isset($port[$r])?':'.$port[$r]:':'.$port[0]),
                   isset($user[$r])?$user[$r]:$user[0],
                   isset($pwd[$r])?$pwd[$r]:$pwd[0]
                   );
               $dbSel = mysql_select_db(
                   isset($name[$r])?$name[$r]:$name[0]
                   ,$hander);
               if(!$hander || !$dbSel)
                   return false;
               $this->hander[1] = $hander;
               return true;
           }
       }
       //从数据库链接
       $r = floor(mt_rand(0,count($host)-1));
       $hander = mysql_connect(
           $host[$r].(isset($port[$r])?':'.$port[$r]:':'.$port[0]),
           isset($user[$r])?$user[$r]:$user[0],
           isset($pwd[$r])?$pwd[$r]:$pwd[0]
           );
       $dbSel = mysql_select_db(
           isset($name[$r])?$name[$r]:$name[0]
           ,$hander);
       if(!$hander || !$dbSel) 
           return false; 
       $this->hander = $hander; 
       return true; 
    } 

    /**
     * 关闭Session 
     * @access public 
     */
   public function close() {
       if(is_array($this->hander)){
           $this->gc($this->lifeTime);
           return (mysql_close($this->hander[0]) && mysql_close($this->hander[1]));
       }
       $this->gc($this->lifeTime); 
       return mysql_close($this->hander); 
   } 

    /**
     * 读取Session 
     * @access public 
     * @param string $sessID 
     */
   public function read($sessID) { 
       $hander 	= 	is_array($this->hander)?$this->hander[1]:$this->hander;
       
       if($result = mysql_query("SELECT * FROM user_session WHERE session_key='$sessID'",$hander))       //这样可以防止一个浏览器登录几个账户  如果后面加上and user_id='id' 的话就可以设置一个浏览器多个账户登录  但是下面的相关操作也要变匿
       {
           if($row = mysql_fetch_assoc($result))
           {
               return $row["session_data"];
           }
           else
           {
               return "";
           }
       }
       else
       {
           return "";
       }
   } 

    /**
     * 写入Session 
     * @access public 
     * @param string $sessID 
     * @param String $sessData  
     */
   public function write($sessID,$sessData) { 
       $hander 		= 	is_array($this->hander)?$this->hander[0]:$this->hander;
       $sql = "select session_data from user_session where session_key='$sessID'";
       $result = mysql_query($sql,$hander);
       $time=time();
       if(mysql_num_rows($result)===0)          //如果不存在写
       {
           if(!empty($sessData))
           {
               $globaluserid=Conf::$now_user_id;
               $globaltype=Conf::$now_user_type;
               $sql = "insert into user_session(session_key,session_data,user_id,session_time,use_type) values('$sessID','$sessData','$globaluserid','$time','$globaltype')";
               $result = mysql_query($sql,$hander);
               //重新赋值后删除原来在数据库中存放的
               $del="delete from user_session where user_id='$globaluserid' and session_time<'$time' and use_type='$globaltype'" ;            //当一个用户登陆后自动删除其他登陆该账号的session  use_type   区分手机还是pc
               $result = mysql_query($del,$hander);
           }
       }
       else          //存在的话更新
       {
           $sql = "update user_session set session_data='$sessData',session_time='$time' where session_key='$sessID' and use_type='$globaltype'";
           $result = mysql_query($sql,$hander);
       }
       return true;
   } 

    /**
     * 删除Session 
     * @access public 
     * @param string $sessID 
     */
   public function destroy($sessID) { 
       $hander 	= 	is_array($this->hander)?$this->hander[0]:$this->hander;
       $get_all_user="select * from user_session where session_key='".$sessID."'";        //这样可以防止一个浏览器登录几个账户
       if($result1=mysql_fetch_assoc(mysql_query($get_all_user,$hander)))
       {
           $globaltype=1;
           if($result = mysql_query("DELETE  FROM user_session WHERE user_id='$result1[user_id]' and use_type='$globaltype'",$hander))
           {
               return true;
           }
       }
       else
       {
           return false;
       }
   } 

    /**
     * Session 垃圾回收
     * @access public 
     * @param string $sessMaxLifeTime 
     */
   public function gc($sessMaxLifeTime) { 
       return true;
   } 

}