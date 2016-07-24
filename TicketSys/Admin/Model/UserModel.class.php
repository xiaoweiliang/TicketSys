<?php
/**
 * 用户管理类
 */
namespace Admin\Model;
use Think\Model;
use Common\Common\KeyTool;
use Common\Common\RegularTest;
use Common\Common\PublicTool;
use Common\Common\TimeManager;
use Common\Common\CacheManager;
use Common\Common\MyPage;
use Common\Common\SessionManager;
use Common\Common\UploadFile;
class UserModel extends Model
{
    private $_Model="UserModel";
    /**
     * 获取model
     */
    public function GetModel()
    {
        return $this->_Model;
    }
    /**
     * 获取总数据
     */
    public function GetCount()
    {
        $result=count($this->GetAllUserId());
        return $result;
    }
    /**
     * 获取所有的用户id
     */
    public function GetAllUserId()
    {
            $result=$this->table("tp_users")->field("id")->select();
            $AllUserList=array();
            if(!empty($result))
            {
                return $result;
            }
            else
                return PublicTool::_Empty();
    }
    /**
     * 获取所有人的信息
     */
    public function GetAllUserInfo()
    {
        $result=$this->GetAllUserId();
        $AllUserList=array();
        if(!empty($result))
        {
            $count=count($result);
            $offset=MyPage::GetSqlOffset($this->_Model);
            $endoffset=$offset+PAGE_COUNT;
            if($endoffset>$count)
                $endoffset=$count;
            for($i=$offset;$i<$endoffset;$i++)
            {
                $AllUserList[]=$this->GetUserInfo($result[$i]['id']);
            }       
        }
        return $AllUserList;
    }
    /**
     * 获取用户信息
    * 返回个人信息
     */
    public function GetUserInfo($user_id)
    {
    	
        if(!empty($user_id)&&$user_id>0)
        {
            $UserInfo=CacheManager::Get("UserInfo".$user_id,SESSION_PREFIX1);
            if(empty($UserInfo))
            {
                $result=$this->where("id=%d",$user_id)->field(array("nickname","user_money","user_headpic","user_sex","register_time"))->select();
                if(!empty($result))
                {
                    $result[0]['register_time']=TimeManager::FormatTime($result[0]['register_time']);
                    $result[0]['phone_no']=$this->GetUserPhone($user_id);
                    $result[0]['id']=$user_id;
                    CacheManager::Set("UserInfo".$user_id, $result[0],SESSION_PREFIX1);
                    return $result[0];
                }
            }
            else 
                return $UserInfo;
        }
    }
    /**
     * 获取用户电话号码
     */
    public function GetUserPhone($user_id)
    {
        $result=$this->table("tp_users")->where("id=%d",$user_id)->field("phone_no")->select();
       
        if(!empty($result))
        {
            return  $result[0]['phone_no'];
        }
        else
            return '';
    }
    /**
     * 获取用户登陆随机数
     */
    public function GetLoginRound($phone_no)
    {
 	
        $result=$this->table("tp_users")->where("phone_no='%s'",$phone_no)->field("round")->select();
        if(!empty($result))
            return $result[0]['round'];
        else
            return null;
    }
    /**
     * 获取用户id
     */
    public function GetUserId($phone_no,$key="")
    {     
        if(empty($key))
             $result=$this->table("tp_users")->where("phone_no='%s'",$phone_no)->field("id")->select();
        else 
            $result=$this->table("tp_users")->where("phone_no='%s' and user_key='%s'",$phone_no,$key)->field("id")->select();
         if(!empty($result))
             return $result[0]['id'];
         else
             return -1;
    }
    /**
     * 用户登陆
     */
    public function UserLogin($phone_no,$key)
    {
        if(RegularTest::check_mobilephone($phone_no))
        {
            $key=KeyTool::_before_base_decode($phone_no,$key);
             if(strlen($key)==KEY_LENGTH)
             {
                 $round=$this->GetLoginRound($phone_no);
                 if(!empty($round))
                 {
                   $key=KeyTool::Smd5($key.$round);
                    $result=$this->table("tp_users")->where("phone_no='%s' and user_key='%s'",$phone_no,$key)->field("id")->select();
                    if(!empty($result))
                    {
                        SessionManager::UpdateUserLogin($result[0]['id']);
                        $data['session_id']=SessionManager::GetSessionId();
                        $data['user_id']=KeyTool::base_encodes($result[0]['id']);
                        return $data;
                    }   
                    else
                        return -1;
                 }
                 else 
                     return -2;          //不存在账号 系统错误
             }
             else 
                 return -3;
        }
        else
            return -4;
    }
    /**
     * 用户注册 
     */
    public function UserRegister($phone_no,$key)
    {
 
        if(RegularTest::check_mobilephone($phone_no))
        {
            $key=KeyTool::_before_base_decode($phone_no,$key);
            
            if(strlen($key)==KEY_LENGTH)
            {
                $round=$this->GetLoginRound($phone_no);
              	
                if(empty($round))
                {
                    $login_round=PublicTool::MKRound();
                    $key=KeyTool::Smd5($key.$login_round);
                    $data['phone_no']=$phone_no;
                    $data['user_key']=$key;
                    $data['round']=$login_round;
                    $user=M("tp_users");                        //这里不能用$this->table    bug
                    $userid=$user->add($data);
                    
                    
                    if($userid<0)
                        return -1;                 //失败
                    else 
                    {
                    	$load=new UploadFile();
                    	$file_path=$load->UploadFile($_FILES['file'],$userid);
                    	if($file_path==false)
                    	{
                    		return  -5;//上传失败
                    	}
                        $detaile_info['id']=$userid;
                      //  $detaile_info['round']=PublicTool::MKRound();
                        $detaile_info['round']=$login_round;
                        $detaile_info['register_time']=TimeManager::GetTime();
                        $detaile_info['user_headpic']=$file_path;
                     //   $this->table("tp_userinfo")->add($detaile_info);这里不能用$this->table    bug
                        $userinfo=M(tp_userinfo);
                        $userinfo->add($detaile_info);
                        SessionManager::UpdateUserLogin($userid);
                        $date['session_id']=SessionManager::GetSessionId();
                        $date['user_id']=KeyTool::base_encodes($userid);
                        return $date;
                    }
                }
                else
                    return -2;          //已经存在
            }
            else
                return -3;
        }
        else
            return -4;
    }
    
    

    /**
     *
     *梁晓伟
     */
    
    
    /**
     * 修改用户基本信息
     */
    public function modify_userinfo($user_id,$nickname,$user_sex)
    {
    	if(!empty($user_id))
    	{
    		$user_id=KeyTool::base_decodes($user_id);
    		if(!empty($nickname))
    		{
    			if($this->modify_usernick($user_id, $nickname))
    				$date['nickname']=$nickname;
    		}
    		if(!empty($user_sex))
    		{
    			if($this->modify_usersex($user_id, $user_sex))
    				$date['user_sex']=$user_sex;
    		}
    		$load=new UploadFile();
    		$file_path=$load->UploadFile($_FILES['file']);
    		
    		if($file_path==false)
    		{
    			return  -3;//上传失败
    		}
    		$delete=$this->table('tp_userinfo')->where("id=%s",$user_id)->field("user_headpic")->select();
    		if(!empty($delete))
    		{
    			unlink(UPLOAD_IMAGE_ROOT_PATH.$delete[0]['user_headpic']);
    		}
    		$data['user_headpic']=$file_path;
    		$re=$this->table('tp_userinfo')->where("id=%s",$user_id)->setField("user_headpic",$file_path);
    		//修改缓冲
    		$result=$this->where("id=%d",$user_id)->field(array("nickname","user_money","user_headpic","user_sex","register_time"))->select();
    		if(!empty($result))
    		{
    			$result[0]['id']=$user_id;
    			$date['nickname']=$result[0]['nickname'];
    			$date['user_sex']=$result[0]['user_sex'];
    			$date['user_headpic']=$result[0]['user_headpic'];
    			//$date['phone_no']=$this->GetUserPhone($user_id);
    			CacheManager::Set("UserInfo".$user_id, $result[0],SESSION_PREFIX1);
    			//$date['is_ok']=1;
    		}
    		else
    			$date['is_ok']=-1;
    		 
    		 
    	}
    	else
    		$date['is_ok']=0;
    	return $date;
    }
    /**
     * 修改头像
     * $type为头像类型  默认为头像
     */
    public function modify_pic($user_id='',$type='')
    {
    	if(!empty($user_id))
    	{
    		if(empty($type))
    		{
    			//$user_id=KeyTool::base_decodes($user_id);
    			//上传头像
    			$load=new UploadFile();
    			 $file_path=$load->UploadFile($_FILES['file']);
    	
    			 if($file_path==false)
    			 {
    			 	return  -3;//上传失败
    			 }
    			 $delete=$this->table('tp_userinfo')->where("id=%s",$user_id)->field("user_headpic")->select();
    			 if(!empty($delete))
    			 {
    			 	unlink(UPLOAD_IMAGE_ROOT_PATH.$delete[0]['user_headpic']);
    			 }
    			$data['user_headpic']=$file_path;
    			$re=$this->table('tp_userinfo')->where("id=%s",$user_id)->save($data);
    			
    			
    			if(!empty($re))
    			{
    				$result=$this->where("id=%d",$user_id)->field(array("nickname","user_money","user_headpic","user_sex","register_time"))->select();
    				CacheManager::Set("UserInfo".$user_id, $result[0],SESSION_PREFIX1);//更新缓冲
    				return $data;
    			}
    			else 
    				return -2;//插入错误
    		}
    	}
    	else 
    		return -1;//空
    }
    /**
     * 修改用户昵称
     */
    public function modify_usernick($user_id,$nickname)
    {
    	$data['nickname']=$nickname;
    	$result=$this->table("tp_userinfo")->where("id='%s'",$user_id)->save($data);
    	if(!empty($result))
    	{
    		return true;
    	}
    	else
    		return false;
    }
    /**
     * 修改用户性别
     */
    public function modify_usersex($user_id,$user_sex)
    {
    	$data['user_sex']=$user_sex;
    	$result=$this->table("tp_userinfo")->where("id='%s'",$user_id)->save($data);
    	if(!empty($result))
    	{
    		return true;
    	}
    	else
    		return false;
    }
    /**
     * 修改登录密码
     */
    public function modify_userkey($user_id,$key,$newkey1)
    {
    	$user_id=KeyTool::base_decodes($user_id);
    	$phone_no=$this->GetUserPhone($user_id);
    	$key=KeyTool::_before_base_decode($phone_no, $key);
    	$newkey1=KeyTool::_before_base_decode($phone_no, $newkey1);
    	
    		if($this->is_loginpwd($user_id, $key))
    		{
    			$user_key=KeyTool::Smd5($newkey1.$this->GetLoginRoundid($user_id));
    			
    			//$re=$this->table('tp_users')->where("id='%s'",$user_id)->setField("user_key",$user_key);
    			$user=M('tp_users');
    			
    			$re=$user->where("id='%s'",$user_id)->setField("user_key",$user_key);
    			if(!empty($re))
    			{
    					
    				return 1;//ok
    			}
    			else
    				return -3;//更新错误
    		}
    		else
    			return -1;//旧密码错误
    
   }
    		
    
    /**
     * 修改支付密码
     */
    public function modify_paykey($user_id,$new_pwd1,$type='',$old_pwd)
    {
        
    	$user_id=KeyTool::base_decodes($user_id);
    	$phone_no=$this->GetUserPhone($user_id);
    	$old_pwd=KeyTool::_before_base_decode($phone_no, $old_pwd);
    	$new_pwd1=KeyTool::_before_base_decode($phone_no, $new_pwd1);
    	$date['user_pay_key']=KeyTool::Smd5($new_pwd1.$this->GetLoginRoundid($user_id));
    
    	$user=M('tp_userinfo');
    	
    	if(empty($type))
    	{
    		
    		if($this->is_loginpwd($user_id, $old_pwd))
    		{
    			$re=$user->where("id='%s'",$user_id)->setField("user_pay_key",$date['user_pay_key']);
    			if(!empty($re))	
    				return 1;//ok
    			else
    				return -3;//更新错误
    		}
    		else 
    			return -1;//旧密码错误
    			
    	}
    	else 
    	{
    		$user->user_pay_key=$date['user_pay_key'];
    		$re=$user->where("id=%s",$user_id)->save();
    	if(!empty($re))
    	    return 1;//ok
    	else
    		return -3;//更新错误
    	}
    		
    
    		
    }
    /**
     * 设置支付密码 ，当旧密码不存在
     */
    public  function  set_pay_key($user_id,$new_pwd1,$old_key)
    {
    	if($this->IsExistPayrKey($user_id))//不存在
    		return $this->modify_paykey($user_id,$new_pwd1,1);
    	else 
    		return $this->modify_paykey($user_id,$new_pwd1,'',$old_key);
    }
    /**
     *  匹配登录密码
     */
    public function is_loginpwd($user_id,$key)
    {
    	$re=$this->table("tp_users")->where("id='%s'",$user_id)->field('user_key')->select();
    	$round=$this->GetLoginRoundid($user_id);
    	$key=KeyTool::Smd5($key.$round);
    	
    	if($re[0]['user_key']==$key)
    	{
    		return true;
    	}
    	else
    		return false;
    }
    /**
     * 获取登录随机数，通过id
     */
    public function GetLoginRoundid($user_id)
    {
    	$resulte=$this->table("tp_userinfo")->where("id=%s",$user_id)->field("round")->select();
    	if(!empty($resulte))
    	{
    		return $resulte[0]["round"];
    	}
    	else
    		return null;
    }
    
   /**
    * 判断支付密码时否存在
    */
    public  function IsExistPayrKey($user_id)
    {
    	$user=$this->table("tp_userinfo")->where("id=%s",$user_id)->field("user_pay_key")->select();
    	if(empty($user[0]['user_pay_key']))
    		return 1;
    	else
    		return -1;
    }
     /**
      * 判断支付密码是否正确
      */
    public  function IsPayKey($user_id,$user_pay_key)
    {
    	
    	$user=new UserModel('tp_users');
    	$user_pay_key=KeyTool::Smd5($user_pay_key.$user->GetLoginRoundid($user_id));
    	$userinfo=$this->table('tp_userinfo')->where("id=%s and user_pay_key='%s'",$user_id,$user_pay_key)->field("round")->select();
    	if(!empty($userinfo))
    	{
    		return true;
    	}
    	else 
    		return false;
    }
    /**
     * 获取账户余额
     */
    public  function  GetUserMoney($user_id)
    {
    	$UserInfo=CacheManager::Get("UserInfo".$user_id,SESSION_PREFIX1);
    	if(!empty($UserInfo))
    	{
    		$count=count($UserInfo);
    		for($i=0;$i<$count;$i++)
    		{
    		if($UserInfo[$i]['id']==$user_id)
    			return $UserInfo[$i]['user_money'];
    			
    		}
    		return false;
    	}
    	else
    		{
    		$userinfo=$this->table('tp_userinfo')->where("id=%s",$user_id)->field("user_money")->select();
    		if(!empty($userinfo))
    		{
    			return $userinfo[0]['user_money'];
    		}
    		else
    			return false;
    		}
    }
    /**
     * 检查余额是否够用
     */
    public  function IsUserMoney($user_id,$money)
    {
    	 $usermoney=$this->GetUserMoney($user_id);
    	 if($usermoney==false)
    	 	 return false;
    	 if($usermoney>=$money)
    	 	return $usermoney;
    	 else 
    	 	return false;
    }
    
    /**
     * 修改账户余额    $money修改的金额，负数为消费
     */
    public function  ModifyUserMoney($user_id,$money,$type='')
    {
    	$user_id=KeyTool::base_decodes($user_id);
    	if(!empty($type))
    	{
    		$order_sn=PublicTool::MKOrderSn();
    		$time=TimeManager::GetTime();
    		$order=new OrderModel('tp_userorder');
    		$info=$order->RechangeOrder($user_id, $order_sn, $time, $money);
    		if(!$info)
    		{
    			return -5;//系统错误
    		}
    	}
    	
    	$UserInfo=CacheManager::Get("UserInfo".$user_id,SESSION_PREFIX1);
    	$userin=$this->table('tp_userinfo')->where("id=%s",$user_id)->field("user_money")->select();
    	if(!empty($userin))
    	{
    	  if(!empty($UserInfo))
    	  {
    	  	$re=$this->table("tp_userinfo")->where("id=%s",$user_id)->setField("user_money",$money+$userin[0]['user_money']);
    	  	if(empty($re))
    	  	{
    	  		return -1;
    	  	}
    	  	if($UserInfo['id']==$user_id)
    	  	{
    	  		$res=$this->table("tp_userinfo")->where("id=%s",$user_id)->field("user_money")->select();
    	     
    	  	    $UserInfo['user_money']=$res[0]['user_money'];
    	     	CacheManager::Set("UserInfo".$user_id, $UserInfo,SESSION_PREFIX1);
    	     	$d['user_money']=$UserInfo['user_money'];
         	  	return $d;
    	  	}      
    	  	return -2;
    	  	
    	  }
    	  else {
    	  	$re=$this->table("tp_userinfo")->where("id=%s",$user_id)->setField("user_money",$money+$userin[0]['user_money']);
    	  	if(!empty($re))
    	  	{
    	  		$d['user_money'] =$money+$userin[0]['user_money'];
    	  		
    	  		return $d;
    	  	}
    	  	else 
    	  		return -3;
    	  }
    	}
        return -4;   	    			
    }
}