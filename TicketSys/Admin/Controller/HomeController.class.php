<?php
/**
 * 系统主界面
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\AdminActionModel;
use Common\Common\SessionManager;
use Admin\Model\CityModel;
use Admin\Model\AdminUserModel;
class HomeController extends Controller
{
    /**
     * 系统默认入口
     */
    public function index()
    {
         if(SessionManager::GetUserId()>0)
         {
             $this->display();
         }
    }
    /**
     * 初始化界面左边蓝
     */
    public function HomeLeft()
    {
         if(SessionManager::GetUserId()>0)
         {
             $this->assign("user_name",SessionManager::GetAdminName());
             $admin=new AdminUserModel("tp_adminuser");
             $action=new AdminActionModel("tp_adminaction");
             $city=new CityModel("tp_city");
             $this->assign("top_action",$action->GetAdminTopAction());
             $this->assign("child_action",$action->GetAdminChildAction());
             $this->assign("ctl","ctl");
             $this->assign("primary",$admin->GetAdminPrm());
             $this->assign("city",$city->GetCityList());
             $this->assign("rules",require APP_PATH."Admin/Conf/config.php");
             $this->display("public/left");
         }
    }
    /**
     * 初始化右边内容
     */
    public function HomeRight()
    {
        if(SessionManager::GetUserId()>0)
        {
            $this->display("public/right");
        }
    }
}
?>