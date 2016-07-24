<?php
/**
 * 用户消费管理
 */
namespace  Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\SpendModel;
use Common\Common\Cookie;
use Common\Common\MyPage;
use Common\Common\PublicTool;
class SpendController extends Controller
{
    private $Map=array(
        'GetAllUserSpend'=>'获取所有用户的消费记录',
        'GetUserSpend'=>'获取单个用户的消费记录',
        'DelSpend'=>'删除用户消费记录'
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
     * 获取所有用户的消费信息
     */
    public function GetAllUserSpend()
    {
        if(SessionManager::GetUserId()>0)
        {
            $spend=new SpendModel("tp_usermoneyaccount");
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
            $this->assign('page',MyPage::GetPage($spend->GetModel()));
            $this->assign("AllSpendList",$spend->GetAllUserSpend($_POST['type']));
            Cookie::SetCookie("type", $_POST['type']);
            //Cookie::ClearCookie("userid");
            $this->assign("type",$_POST['type']);
            $this->assign("manager","用户消费管理");
            $this->display("Spend/AllUserSpend");
        }
    }
    /**
     * 获取单个用户的消费记录
     */
    public function GetUserSpend()
    {
        if(SessionManager::GetUserId()>0)
        {
            $spend=new SpendModel("tp_usermoneyaccount");
           
            $type=Cookie::GetCookie("type");
            if(!isset($_GET['userid']))
            {
                $_GET['userid']=Cookie::GetCookie("userid");
            }   
            if(!isset($_POST['type'])&&isset($type))
                $_POST['type']=$type;
            if(!isset($_POST['type']))
                    $_POST['type']=-1;  
            $this->assign("AllSpendList",$spend->GetUserSpend($_GET['userid'],$_POST['type']));
            $this->assign('page',MyPage::GetPage($spend->GetModel()));
            $this->assign("type",$_POST['type']);
            Cookie::SetCookie("userid", $_GET['userid']);
            $this->assign("userid",$_GET['userid']);
            Cookie::SetCookie("type", $_POST['type']);
            $this->assign("manager","用户消费管理");
            $this->display("Spend/AllUserSpend");
        }
    }
    /**
     * 删除消费记录
     */
    public function DelSpend()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_GET['userid'];
            $spendid=$_GET['spendid'];
            $spend=new SpendModel("tp_usermoneyaccount");
            $spend->DelUserSpend($userid, $spendid);
            PublicTool::GoBack();
        }
    }
}