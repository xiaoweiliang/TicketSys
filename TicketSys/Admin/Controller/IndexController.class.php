<?php
/**
 * 系统入口
 */
namespace Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Common\Common\PublicTool;
use Admin\Model\AdminUserModel;
class IndexController extends Controller 
{
    private $AllController=array(
        'Auto'=>1,
        'City'=>1,
        'FeedBack'=>1,
        'Home'=>1,
        'Index'=>1,
        'Message'=>1,
        'Metro'=>1,
        'Order'=>1,
        'Site'=>1,
        'Spend'=>1,
        'User'=>1,
        'UserInfo'=>1
    );
    /**
     * 检测控制成存在
     * @param unknown $ctl
     * @return boolean
     */
    private function CheckComtroller($ctl)
    {
        if($this->AllController[$ctl]==1)
            return true;
        else
            return false;
    }
    /**
     * 写管理员操作日志
     */
    public function LogAdmin($content)
    {
        $detaile_info=array();
        $detaile_info[]=$_POST;
        $detaile_info[]=$_GET;
        $adminuser=new AdminUserModel("tp_adminuser");
        $adminuser->MemberAction($content, $detaile_info);
    }
    
     /**
      * 系统默认入口
      */
    public function index()
    { 
        if(!empty($_GET['m'])&&$this->CheckComtroller($_GET['m']))
        {
            $model="Admin\Controller\\".$_GET['m']."Controller";
            if(PublicTool::CheckFunction($model, $_GET['action']))
            {
                 $obj=new $model();
                 if(PublicTool::CheckFunction($model, 'GetInstation'))  
                         $this->LogAdmin($obj->GetInstation($_GET['action']));
                $obj->$_GET['action']();
            }
            else 
                $this->success('请求失败，数据不合法', 'javascript:history.back(-1);');
        }
        else
        {
            if(SessionManager::GetUserId()<0)
                $this->display();
            else
             $this->display("Home/index");
        }   
    }   
}