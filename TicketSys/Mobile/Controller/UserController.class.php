<?php
/**
 * 用户登陆注册
 */
namespace  Mobile\Controller;
use Think\Controller;
use Admin\Model\UserModel;
use Common\Common\ToolBack;
use Common\Common\JsonOperate;
use Common\Common\KeyTool;
use Common\Common\SessionManager;
class UserController extends Controller
{
    /**
     * 系统默认入口
     */
    public function index()
    {
      
    }
    /**
     * 用户注册
     */
    public function UserRegister()
    {
    	if($_POST['key1']==$_POST['key2'])
    	{
    		$user_login=new UserModel('tp_users');
    		$date=$user_login->UserRegister($_POST['phone_no'],$_POST['key1']);
    		return $date;
    	}
    	else 
    		return -5;
    		
       
    }
    /**
     * 用户登陆
     */
    public function UserLogin()
    {
         $user_login=new UserModel('tp_users');
    	 $date=  $user_login->UserLogin($_POST['phone_no'],$_POST['key']);
         return $date;
    }
    /**
     * 返回用户相应的信息
     */
    public  function UserInfo()
    {
    	$user_login=new UserModel('tp_userinfo');
    	$user_id=KeyTool::base_decodes($_POST['user_id']);
    	$date=$user_login->GetUserInfo($user_id);
    	$arr['user_headpic']=$date['user_headpic'];
    	$arr['user_money']=$date['user_money'];
    	$arr['nickname']=$date['nickname'];
    	$arr['phone_no']=$date['phone_no'];
    	return $arr;
    	
    }
    /**
     *用户退出登陆
     */
    public function UserLoginOut()
    {
        SessionManager::GoBackSession();
        return 1;
    }
}