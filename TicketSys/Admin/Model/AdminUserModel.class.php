<?php
/**
 * 管理员数据库操作
 */
 namespace Admin\Model;
 use Think\Model;
 use Common\Common\PublicTool;
 use Common\Common\KeyTool;
 use Common\Common\SessionManager;
 use Common\Common\JsonOperate;
 use Common\Common\TimeManager;
 use Common\Common\Cookie;
 use Common\Common\MyPage;
 class AdminUserModel extends Model
 {
     private $_Model="AdminUserModel";
     /**
      * 获取models
      */
     public function getModel()
     {
         return $this->_Model;
     }
     /**
      * 获取用户自动票总数
      */
     public function GetActionCount()
     {
         $userid=Cookie::GetTHUserId();
         if(!empty($userid))
             $result=$this->table("tp_adminopaccount")->where("admin_id=%d",$userid)->field("id")->count();
         else
             $result=$this->table("tp_adminopaccount")->field("id")->count();
          
         if(!empty($result))
         {
             return $result;
         }
         else
             return 0;
     }
     /**
      * 获取总数据
      */
     public function GetCount()
     {
         $result=$this->GetActionCount();
         return $result;
     }
     //获取用户id
     private function getuserid($username,$userkey)
     {
         $user_key=KeyTool::Smd5($userkey);
         $result=$this->where("name='%s' and user_key='%s'",$username,$user_key)->field("id")->select();
         if(!empty($result))
             return $result[0]['id'];
         else
             return -1;
     }
     //用户登录检测
     public function ck_login($data)
     {
         if(PublicTool::checkuserinfo($data)==true)
         {
             $result=$this->getuserid($data['name'],$data['key']);
             if($result>0)
             {
                 //保存管理员信息
                 SessionManager::UpdateUserLogin($result,$data['name']);
                 return true;
             }
             else
                 return false;
         }
         else
             return false;
     }
     /**
      * 添加管理员操作记录
      */
     public function MemberAction($content,$detaile)
     {
         $id=SessionManager::GetUserId(1);
         {
             if($id<0)
             {
                 $user_name=$detaile[0]['name'];
                 $key=$detaile[0]['key'];
                 $adminuser=new AdminUserModel("tp_adminuser");
                 $id=$adminuser->getuserid($user_name, $key);
             }
             $data['admin_id']=$id;
             $data['op_name']=$content;
             $data['detaile_info']=JsonOperate::JsonEncode($detaile);
             $data['op_time']=TimeManager::GetTime();
             $admin=M('tp_adminopaccount');
             $admin->add($data);
         }
     }
     /**
      * 获取管理员权限
      */
     public function GetAdminPrm()
     {
         $adminid=SessionManager::GetUserId();
         $result=$this->where("id=%d",$adminid)->field("is_all")->select();
         if(!empty($result))
             return $result[0]['is_all'];
         else
             return '';
     }
     /**
      * 获取管理员操作记录
      */
     public function GetAllAdminAccount($user_id=-1)
     {
         if($user_id==-1)
         {
             $result=$this->table("tp_adminopaccount")
             ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->order("op_time desc")
             ->field(array("id","admin_id","op_time","op_name","detaile_info"))->select();
         }
         else if(!empty($user_id))
         {
             $result=$this->table("tp_adminopaccount")
             ->where("admin_id=%d",$user_id)
             ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->order("op_time desc")
             ->field(array("id","admin_id","op_time","op_name","detaile_info"))->select();
         }
         if(!empty($result))
         {
             $count=count($result);
             for($i=0;$i<$count;$i++)
             {
                 $result[$i]['op_time']=TimeManager::FormatTime($result[$i]['op_time']);
             }
         }
         return $result;
     }
     /**
      * 获取单个管理员的操作记录
      */
     public function GetAdminAccount($adminid)
     {
         if(!empty($adminid)&&$adminid>0)
         {
             $result=$this->table("tp_adminopaccount")
             ->where("admin_id=%d",$adminid)
             ->limit(MyPage::GetSqlOffset($this->_Model),PAGE_COUNT)->order("op_time desc")
             ->field(array("id","admin_id","op_time","op_name","detaile_info"))->select();
             if(!empty($result))
             {
                 $count=count($result);
                 for($i=0;$i<$count;$i++)
                 {
                     $result[$i]['op_time']=TimeManager::FormatTime($result[$i]['op_time']);
                 }
             }
             return $result;
         }
         else
             return PublicTool::_Empty();
     }
 }
?>