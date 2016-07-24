<?php
/**
 * 系统周票 月票管理
 */
namespace  Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\AutoModel;
use Common\Common\MyPage;
use Common\Common\Cookie;
use Common\Common\PublicTool;
class AutoController extends Controller
{
    private $Map=array(
        'GetAllUserAuto'=>'获取所有用户的自动票',
        'GetUserAuto'=>'获取单个用户的自动票',
        'AddAuto'=>'添加用户自动票',
        'DelUserAuto'=>'删除用户自动s票'
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
     * 获取用户所有月票
     */
    public function GetAllUserAuto()
    {
        if(SessionManager::GetUserId()>0)
        {
            $auto=new AutoModel("tp_userauto");
            if($_GET['TP']!=1)
            {
                $type=Cookie::GetCookie("type");
                if(!isset($_POST['type'])&&isset($type))
                    $_POST['type']=$type;
                if(!isset($_POST['type']))
                    $_POST['type']=-1;
            }
            else
                $_POST['type']=-1;
            $this->assign('page',MyPage::GetPage($auto->GetModel()));
            $this->assign("AllAutoList",$auto->GetAllUserAuto($_POST['type']));
            Cookie::SetCookie("type", $_POST['type']);
            //Cookie::ClearCookie("userid");
            $this->assign("type",$_POST['type']);
            $this->assign("manager","用户自动票管理");
            $this->display("Auto/AllUserAuto");
        }
    }
    /**
     * 获取单个用户的自动票
     */
    public function GetUserAuto()
    {
        if(SessionManager::GetUserId()>0)
        {
            $auto=new AutoModel("tp_userauto");
           if(empty($_GET['userid']))
            {
                $_GET['userid']=Cookie::GetCookie("userid");
            }
            $type=Cookie::GetCookie("type");
            if(!isset($_POST['type'])&&isset($type))
                $_POST['type']=$type;
            if(!isset($_POST['type']))
                $_POST['type']=-1;
            $type=$_POST['type'];
            $this->assign('page',MyPage::GetPage($auto->GetModel()));
            $this->assign("AllAutoList",$auto->GetUserAuto($_GET['userid'],$_POST['type']));
           $this->assign("type",$_POST['type']);
            Cookie::SetCookie("userid", $_GET['userid']);
            Cookie::SetCookie("type", $_POST['type']);
            $this->assign("userid",$_GET['userid']);
            $this->assign("manager","用户自动票管理");
            $this->display("Auto/AllUserAuto");
        }
    }
    /**
     * 添加自动票
     */
    public function AddAuto()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_POST['user_id'];
            $days=$_POST['days'];
            $type=$_POST['type'];
            $auto=new AutoModel("tp_userauto");
            $auto->AddUserAuto($userid, $type,$days);
            PublicTool::GoBack();
        }
    }
    /**
     * 删除自动票
     */
    public function DelUserAuto()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_GET['userid'];
            $autoid=$_GET['autoid'];
             $auto=new AutoModel("tp_userauto");
            $auto->DelUserAuto($userid, $autoid);
            PublicTool::GoBack();
        }
    }
}