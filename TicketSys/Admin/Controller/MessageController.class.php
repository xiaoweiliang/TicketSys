<?php
/**
 * 用户消息 
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Common\Common\MyPage;
use Admin\Model\MsgModel;
use Common\Common\Cookie;
use Common\Common\PublicTool;
class MessageController extends Controller
{
    private $Map=array(
        'GetAllUserMsg'=>'获取所有用户的消息',
        'GetUserMsg'=>'获取单个用户的消息',
        'AddMsg'=>'添加用户消息',
        'DelUserMsg'=>'删除用户消息'
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
     * 获取所有用户的消息
     */
    public function GetAllUserMsg()
    {
        if(SessionManager::GetUserId()>0)
        {
            $Msg=new MsgModel("tp_message");
            $this->assign('page',MyPage::GetPage($Msg->GetModel()));
            $this->assign("AllMsgList",$Msg->GetAllUserMsg());
            Cookie::ClearCookie("userid");
            Cookie::ClearCookie("type");
            $this->assign("manager","用户消息管理");
            $this->display("Msg/AllUserMsg");
        }
    }
    /**
     * 获取单个用户的信息
     */
    public function GetUserMsg()
    {
        if(SessionManager::GetUserId()>0)
        {
            $Msg=new MsgModel("tp_message");
            
            if(empty($_GET['userid']))
            {
                $_GET['userid']=Cookie::GetCookie("userid");
            }
            $this->assign("AllMsgList",$Msg->GetUserMsg($_GET['userid']));
            $this->assign('page',MyPage::GetPage($Msg->GetModel()));
            Cookie::SetCookie("userid", $_GET['userid']);
            $this->assign("userid",$_GET['userid']);
            Cookie::ClearCookie("type");
            $this->assign("manager","用户消息管理");
            $this->display("Msg/AllUserMsg");
        }
    }
    /**
     * 添加用户消息记录
     */
    public function AddMsg()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_POST['user_id'];
            $msgcontent=$_POST['content'];
            $Msg=new MsgModel("tp_message");
 
            $Msg->AddUserMsg($userid, $msgcontent);
            PublicTool::GoBack();
        }
    }
    /**
     * 删除用户消息
     */
    public function DelUserMsg()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_GET['userid'];
            $msgid=$_GET['msgid'];
            $Msg=new MsgModel("tp_message");
            $Msg->DelUserMsg($userid, $msgid);
            PublicTool::GoBack();
        }
    }
}