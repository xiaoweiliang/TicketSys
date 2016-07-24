<?php
namespace Mobile\Controller;
use Think\Controller;
use Common\Common\KeyTool;
use Admin\Model\OrderModel;
use Admin\Model\UserModel;
/**
 * 支付详情
 */
class PayController extends Controller
{
	public function index()
	{
		
	}
	/** 
	 *支付前
	 */
	public  function before_payment()
	{
		$user=new OrderModel('tp_userorder');
		$date=$user->BeforePayMent($_POST['user_id'],$_POST['start_point'],$_POST['end_point']);
		return $date;
	}
	/**
	 * 确认支付
	 */
	public  function ok_payment()
	{
		$user=new OrderModel('tp_userorder');
		$date=$user->OkPayMent($_POST['user_id'], $_POST['order_sn'], $_POST['user_pay_key']);
		return $date;
	}
	/**
	 * 充值
	 */
	public function recharge()
	{
		$user=new UserModel("tp_userinfo");
		$date=$user->ModifyUserMoney($_POST['user_id'], $_POST['money'],1);
		
		return $date;
	}
}