<?php
/**
 * 管理员操作
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\ToolBack;
use Admin\Model\AdminUserModel;
use Common\Common\SessionManager;
use Common\Common\MyPage;
use Common\Common\Cookie;
use Common\Common\FileOperateTool;
 class UserController extends Controller
 {
     private $Map=array(
         'cklogin'=>'管理员登陆',
         'loginout'=>'管理员退出',
         'GetAdminActionAccount'=>'查看管理员操作记录',
         'GetAdminAccount'=>'查看单个管理员的操作记录',
         'ClearCache'=>'清空缓存'
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
     * 登陆验证
     */
    public function cklogin()
    {
        $user_login=new AdminUserModel("tp_adminuser");
        ToolBack::echoback($user_login->ck_login($_GET));                //返回客户护短
    }
    /**
     * 管理员退出 
     */
    public  function loginout()
    {
        SessionManager::GoBackSession();
        $this->display("index/index");
    }
    /**
     * 查看管理员操作记录
     */
    public function GetAdminActionAccount()
    {
        if(SessionManager::GetUserId()>0)
        {
            $User=new AdminUserModel("tp_adminuser");
            
            $this->assign('page',MyPage::GetPage($User->GetModel()));
            $this->assign("AllAccountList",$User->GetAllAdminAccount());
            Cookie::ClearCookie("userid");
            Cookie::ClearCookie("type");
            $this->assign("manager","管理员操作记录管理");
            $this->display('Admin/AllActionAccount');
        }
    }
    /**
     * 获取单个管理员的操作
     */
    public function GetAdminAccount()
    {
        if(SessionManager::GetUserId()>0)
        {
            $User=new AdminUserModel("tp_adminuser");
            if(empty($_POST['userid']))
            {
                $_POST['userid']=Cookie::GetCookie("userid");
            }
            $this->assign('page',MyPage::GetPage($User->GetModel()));
            $this->assign("AllAccountList",$User->GetAdminAccount($_POST['userid']));
            Cookie::SetCookie("userid", $_POST['userid']);
            Cookie::ClearCookie("type");
            $this->assign("manager","管理员操作记录管理");
            $this->display('Admin/AllActionAccount');
        }
    }
    /**
     * 清空缓存
     */
    public function ClearCache()
    {
        if(SessionManager::GetUserId()>0)
        {
            $action=$_POST['action'];
            if($action=="clear_cache")
            {
                FileOperateTool::ClearCacheFile();
                ToolBack::echoback(true);
            }
            else
                ToolBack::echoback(false);
        }
    }
 }
?>