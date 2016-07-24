<?php
/**
 * 用户反馈
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\FeedBackModel;
use Common\Common\MyPage;
use Common\Common\Cookie;
use Common\Common\PublicTool;
class FeedBackController extends Controller
{
    private $Map=array(
        'GetAllUserFB'=>'获取所有用户的反馈',
        'GetUserFB'=>'获取单个用户的反馈',
        'DelUserFB'=>'删除用户反馈'
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
     * 获取所有用户反馈消息
     */
    public function GetAllUserFB()
    {
        if(SessionManager::GetUserId()>0)
        {
            $fb=new FeedBackModel("tp_feedback");
            $this->assign('page',MyPage::GetPage($fb->GetModel()));
            $this->assign("AllFBList",$fb->GetAllUserFB());
            Cookie::ClearCookie("userid");
            Cookie::ClearCookie("type");
            $this->assign("manager","用户反馈管理");
            $this->display("FB/AllUserFB");
        }
    }
    /**
     * 获取单个用户反馈信息
     */
    public function GetUserFB()
    {
        if(SessionManager::GetUserId()>0)
        {
            $fb=new FeedBackModel("tp_feedback");
          
            if(empty($_GET['userid']))
            {
                $_GET['userid']=Cookie::GetCookie("userid");
            }
            $this->assign("AllFBList",$fb->GetUserFB($_GET['userid']));
            $this->assign('page',MyPage::GetPage($fb->GetModel()));
            Cookie::SetCookie("userid", $_GET['userid']);
            Cookie::ClearCookie("type");
            $this->assign("manager","用户反馈管理");
            $this->display("FB/AllUserFB");
        }
    }
    /**
     * 删除用户反馈
     */
    public function DelUserFB()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_GET['userid'];
            $fbid=$_GET['fbid'];
           $fb=new FeedBackModel("tp_feedback");
            $fb->DelUserFB($userid, $fbid);
            PublicTool::GoBack();
        }
    }
}