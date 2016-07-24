<?php
/**
 * 用户订单管理
 */
namespace  Admin\Controller;
use Think\Controller;
use Common\Common\SessionManager;
use Admin\Model\OrderModel;
use Common\Common\MyPage;
use Common\Common\Cookie;
use Common\Common\PublicTool;
class OrderController extends Controller
{
    private $Map=array(
        'GetAllUserOrder'=>'获取所有用户的订单',
        'GetUserOrder'=>'获取单个用户的订单',
        'DelUserOrder'=>'删除用户订单'
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
        $this->display("indx/index");
    }
    /**
     * 获取所有订单信息
     */
    public function GetAllUserOrder()
    {
        if(SessionManager::GetUserId()>0)
        {
            $OrderInfo=new OrderModel("tp_orderinfo");
            
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
            $this->assign("AllOrderList",$OrderInfo->GetAllUserOrder($_POST['type']));
            $this->assign('page',MyPage::GetPage($OrderInfo->GetModel()));
            Cookie::SetCookie("type", $_POST['type']);
            //Cookie::ClearCookie("userid");
            $this->assign("type",$_POST['type']);
            $this->assign("manager","用户订单管理");
            $this->display("Order/AllUserOrder");
        }
    }
    /**
     * 获取单个用户的订单
     */
    public function GetUserOrder()
    {
        if(SessionManager::GetUserId()>0)
        {
             $OrderInfo=new OrderModel("tp_orderinfo");  
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
            $this->assign("AllOrderList",$OrderInfo->GetUserOrder($_GET['userid'],$type));
            $this->assign('page',MyPage::GetPage($OrderInfo->GetModel()));
            $this->assign("type",$_POST['type']);
            Cookie::SetCookie("userid", $_GET['userid']);
            Cookie::SetCookie("type", $_POST['type']);
            $this->assign("userid",$_GET['userid']);
            $this->assign("manager","用户订单管理");
           $this->display("Order/AllUserOrder");
        }
    }
    /**
     * 删除单个用户的订单
     */
    public function DelUserOrder()
    {
        if(SessionManager::GetUserId()>0)
        {
            $userid=$_GET['userid'];
            $orderid=$_GET['orderid'];
            $OrderInfo=new OrderModel("tp_orderinfo");
            $OrderInfo->DelUserOrder($userid, $orderid);
            PublicTool::GoBack();
        }
    }
}