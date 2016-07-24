<?php
/**
 * 
 *密码修改
 *
 */
namespace Mobile\Controller;
use Think\Controller;
use Admin\Model\UserModel;
use Common\Common\PublicTool;
use Common\Common\KeyTool;

class UserModifyPWDController extends Controller
{
	public function index()
	{
		
	}
	/**
	 * 修改登录密码
	 */
	public function login_modify_pwd()
	{
		if($_POST['new_pwd1']==$_POST['new_pwd2'])
		{
			$user_login=new UserModel('tp_userinfo');
			$date=$user_login->modify_userkey($_POST['user_id'],$_POST['old_pwd'],$_POST['new_pwd1']);
			return $date;
		}
		else 
		{
			return -2;//两次密码不同
		}
		
	}
	/**
	 * 修改支付密码
	 */
	public function pay_modify_pwd()
	{
		if($_POST['new_pwd1']==$_POST['new_pwd2'])
		{
			$user_pay=new UserModel('tp_userinfo');
			$user_id=KeyTool::base_decodes($_POST['user_id']);
			$date=$user_pay->set_pay_key($user_id,$_POST['new_pwd1'],$_POST['old_pwd']);
			return $date;
		}
		else 
		{
			return -2;//两次密码不同
		}
	}
	/**
	 * 判断支付密码是否存在
	 */
	public function IsExistPayKey()
	{
		$user_pay=new UserModel('tp_userinfo');
		$user_id=KeyTool::base_decodes($_POST['user_id']);
		$date=$user_pay->IsExistPayrKey($user_id);
		return $date;
	}
	
}