<?php
/**
 * 用户修改信息
 */
namespace Mobile\Controller;
use Think\Controller;
use Admin\Model\UserModel;
use Common\Common\JsonOperate;
use Common\Common\ToolBack;


class UserModifyController extends Controller
{
    /**
     * 系统默认入口
     */
    public function index()
    {
         
    }
    /**
     * 修改用户头像
     */
    public function ModifyHeadPic()
    {
    	$user=new UserModel('tp_userinfo');
    	$date=$user->modify_pic($_POST['user_id']);
    	
       return $date;
    }
    /**
     * 修改用户基本信息
     */
    public function ModifyBaseInfo()
    {
      $user=new UserModel('tp_userinfo');
      $date=$user->modify_userinfo($_POST['user_id'],$_POST['nickname'],$_POST['user_sex']);
    //  var_dump($date);
    //  exit();
      return $date;
    }
}