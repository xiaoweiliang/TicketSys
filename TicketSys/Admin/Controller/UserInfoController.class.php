<?php
/**
 * 用户信息管理
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\UserModel;
use Common\Common\MyPage;
use Common\Common\RegularTest;
use Common\Common\PublicTool;
use Common\Common\Cookie;
class UserInfoController extends Controller
{
    private $Map=array(
        'GetAllUser'=>'获取所有用户信息',
        'AddUser'=>'添加用户',
        'GetUserInfo'=>'获取用户信息'
    );
    /**
     * 方法说明表
    */
    public function GetInstation($function)
    {
        return $this->Map[$function];
    }
    /**
     * 系统默认入口
     */
    public function index()
    {
        $this->display("index/index");
    }
    /**
     * 获取所有用户的信息
     */
    public function GetAllUser()
    {
        if(SessionManager::GetUserId()>0)
        {
            $User=new UserModel("tp_userinfo");
            $this->assign('page',MyPage::GetPage($User->GetModel()));
            $this->assign("AllUserList",$User->GetAllUserInfo());
            Cookie::ClearCookie("userid");
            Cookie::ClearCookie("type");
            $this->assign("manager","用户管理");
            $this->display('UserInfo/AllUserInfo');
        }
    }
    /**
     * 添加用户
     */
    public function AddUser()
    {
        if(SessionManager::GetUserId()>0)
        {
            $User=new UserModel("tp_userinfo");
             $User->UserRegister($_POST['phone_no'], $_POST['key']);
            PublicTool::GoBack();
        }
    }
    /**
     * 获取单个用户的信息
     */
    public function GetUserInfo()
    {
        if(SessionManager::GetUserId()>0)
        {
            $phone_no=@$_POST['phone_no'];
            $user_id=@$_POST['user_id'];
            if(empty($user_id))
                $user_id=@$_GET['user_id'];
            $User=new UserModel("tp_userinfo");
            $getuserid=-1;
            if(!empty($phone_no))
            {
                if(!RegularTest::check_mobilephone($phone_no))
                        $phone_no='';
                else
                    $getuserid=$User->GetUserId($phone_no);
            }
            if(!is_numeric($user_id))
            {
                $user_id='';
            }   
           if(!empty($user_id))
           {
               if($getuserid>0)
               {
                   if($user_id!=$getuserid)
                   {
                       Cookie::ClearCookie("userid");
                       $this->assign("AllUserList",PublicTool::_Empty());
                   }               
                   else
                   {
                       Cookie::SetCookie("userid", $user_id);
                       $this->assign("AllUserList",array($User->GetUserInfo($user_id)));
                   }             
               }    
               else 
               {
                   Cookie::SetCookie("userid", $user_id);
                   $userinfo=$User->GetUserInfo($user_id);
                   if(!empty($userinfo))
                       $userinfo=array($userinfo);
                   $this->assign("AllUserList",$userinfo);
               }         
           }
           else if($getuserid>0)
           {
               Cookie::SetCookie("userid", $getuserid);
               $this->assign("AllUserList",array($User->GetUserInfo($getuserid)));
           }
           else
           {
               Cookie::ClearCookie("userid");
               $this->assign("AllUserList",PublicTool::_Empty());
           }
           
            $this->assign("manager","用户管理");
            $this->display('UserInfo/AllUserInfo');
        }
    }
}